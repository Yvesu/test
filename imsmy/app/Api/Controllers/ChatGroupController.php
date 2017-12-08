<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/10
 * Time: 10:48
 */

namespace App\Api\Controllers;


use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use EaseMob;
use DB;
/**
 * 群聊相关接口
 *
 * @Resource("ChatGroup",uri="users/{id}/chat-groups")
 */
class ChatGroupController extends BaseController
{
    /**
     * 创建群聊
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("groupname",required=true,description="群聊名称"),
     *     @Parameter("desc",required=true,description="群聊描述"),
     *     @Parameter("maxusers",required=true,description="最大人数"),
     *     @Parameter("member",description="成员名单，以数组传递，不包括当前用户")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={"groupid":123456}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(500,body={"error":"unknown error"})
     * })
     */
    public function create($id,Request $request)
    {
        try {
            $options = [
                'groupname' => $request->get('groupname'),
                'desc'      => $request->get('desc'),
                'maxusers'  => $request->get('maxusers') > 50 ? 50 : (int)$request->get('maxusers'),
                'owner'     => $id
            ];
            $members = json_decode($request->get('member'),true);
            isset($members) ? $options['members'] = $members : null;
            DB::beginTransaction();
            $result = EaseMob::createGroup($options);
            if(!isset($result)){
                throw new \Exception('unknown error',500);
            }
            $options['id']   = $result['data']['groupid'];
            $options['name'] = $options['groupname'];
            ChatGroup::create($options);
            $time = new Carbon;
            $data = [];
            $data[] = [
                'group_id'   => $options['id'],
                'user_id'    => $options['owner'],
                'type'       => 'owner',
                'updated_at' => $time,
                'created_at' => $time
            ];
            if(isset($members)){
                foreach ($members as $member) {
                    $time = new Carbon;
                    $data[] = [
                        'group_id'   => $options['id'],
                        'user_id'    => $member,
                        'type'       => 'member',
                        'updated_at' => $time,
                        'created_at' => $time
                    ];
                }
            }
            DB::table('chat_group_member')->insert($data);
            DB::commit();
            return response()->json($result['data'],201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 删除群聊
     * @Post("/{group_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("group_id",required=true,description="要删除的群聊ID"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(500,body={"error":"operation failed"})
     * })
     */
    public function destroy($id,$group_id)
    {
        try {
            $owner = ChatGroupMember::where('type','owner')->where('group_id',$group_id)->firstOrFail(['user_id']);
            if($id != $owner->user_id){
                throw new \Exception('unauthorized',401);
            }
            DB::beginTransaction();
            ChatGroupMember::where('group_id',$group_id)->delete();
            ChatGroup::findOrFail($group_id)->delete();
            $result = EaseMob::deleteGroup($group_id);
            if(!isset($result['data']['success'])
                || $result['data']['success'] != true){
                throw new \Exception('operation failed',500);
            }
            DB::commit();
            return response('',204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 转让群组
     * @Put("/{group_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("group_id",required=true,description="要删除的群聊ID(为URL中的group_id)"),
     *     @Parameter("leave",type="boolean",description="转移群聊后，前群主是否退群",default="false")
     * })
     * @Transaction({
     *     @Request(body={"new_owner":"用户ID","leave":false},headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"success":true}),
     *     @Response(400,body={"error":"bad_request"}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(500,body={"error":"operation failed"}),
     *     @Response(404,body={"error":"chat_group_member_not_found"})
     * })
     */
    public function update($id,$group_id,Request $request)
    {
        try {
            $new_owner = $request->get('new_owner');
            $leave = $request->get('leave') === true ? true : false;
            if(!isset($new_owner)){
                //更新都用PUT请求，若没有new_owner认为更新名称及介绍
                $result = $this->updateInfo($request,$group_id);
                return response()->json($result,201);
            }
            $new_owner = ChatGroupMember::where('user_id',$new_owner)->where('group_id',$group_id)->firstOrFail();
            $old_owner = ChatGroupMember::where('user_id',$id)->where('group_id',$group_id)->firstOrFail();
            if('owner' != $old_owner->type){
                //不是群主，没有权限
                throw new \Exception('unauthorized',401);
            }
            DB::beginTransaction();
            $result = EaseMob::changeGroupOwner($group_id,$new_owner->user_id);
            if(!isset($result['data']['newowner']) || $result['data']['newowner'] !== true){
                //在测试中，有时环信服务器返回的请求为200但未操作成功
                throw new \Exception('operation_failed',500);
            }
            $data = [(string)$old_owner->user_id];
            if($leave){
                //转移并退群的，删除本地存储的记录
                $old_owner->delete();
            }else{
                //环信设计问题，转移，直接退群，因此要加回去。
                EaseMob::addGroupMember($group_id,$data);
                $old_owner->type = 'member';
                $old_owner->save();
            }
            $new_owner->type = 'owner';
            $new_owner->save();
            DB::commit();
            return response()->json(['success' => true],201);
        } catch(ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found'],404);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 更改群聊信息
     * @Put("/{group_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("group_id",required=true,description="要删除的群聊ID(为URL中的group_id)"),
     *     @Parameter("groupname",description="群聊名称,不要加/,加了也会被过滤掉"),
     *     @Parameter("description",description="群聊描述,不要加/,加了也会被过滤掉"),
     * })
     * @Transaction({
     *     @Request(body={"groupname":"name","description":"description"},headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"groupname":"请求没加时，也会返回，true为修改成功","description":"请求没加时，也会返回，true为修改成功"}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"chat_group_member_not_found"})
     * })
     */
    public function updateInfo($request,$group_id)
    {
        $parameters = $request->only(['groupname','description']);
        $parameters['groupname'] = str_replace('/','',$parameters['groupname']);
        $parameters['description'] = str_replace(' ','+',str_replace('/','',$parameters['description']));
        //可以更改，但默认不可以改最大人数
        $result = EaseMob::modifyGroupInfo($group_id,$parameters);
        $chat_group = ChatGroup::findOrFail($group_id);
        if($result['data']['groupname'] === true){
            $chat_group->name = $parameters['groupname'];
        }
        if($result['data']['description'] === true){
            $chat_group->desc = str_replace('+',' ',$parameters['description']);
        }
        $chat_group->save();
        return $result['data'];
    }
}