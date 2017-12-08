<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/11
 * Time: 18:22
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
 * 群聊-成员相关接口
 *
 * @Resource("ChatGroupMember",uri="/users/{id}/chat-groups/{group_id}/members")
 */
class ChatGroupMemberController extends BaseController
{
    /**
     * 群成员添加
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("group_id",required=true,description="群聊的ID即(为URL中的group_id)"),
     * })
     * @Transaction({
     *     @Request(body={"member": {"10001","所要添加的用户ID的<数组>"}},headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"newmembers":{"10001","添加成功的用户数组"},"action":"add_member","groupdid":"195312488898625976"}),
     *     @Response(400,body={"error":"bad_request"}),
     *     @Response(400,body={"error":"exceeds_maxusers"}),
     *     @Response(401,body={"error":"unauthorized"}),
     * })
     */
    public function store($id,$group_id,Request $request)
    {
        try {
            $members = json_decode($request->get('member'),true);
            if(!isset($members)){
                throw new \Exception('bad_request',400);
            }
            if(!ChatGroupMember::where('group_id',$group_id)->where('user_id',$id)->count()){
                throw new \Exception('unauthorized',401);
            }
            if(sizeof($members) >
                (ChatGroup::find($group_id)->maxusers -
                    ChatGroupMember::where('group_id',$group_id)->count())){
                throw new \Exception('exceeds_maxusers',400);
            }
            DB::beginTransaction();
            $data = [];
            foreach ($members as $member) {
                $time = new Carbon();
                $data[] = [
                    'group_id'   => $group_id,
                    'user_id'    => $member,
                    'type'       => 'member',
                    'updated_at' => $time,
                    'created_at' => $time
                ];
            }
            DB::table('chat_group_member')->insert($data);
            $result = EaseMob::addGroupMember($group_id,$members);
            DB::commit();
            return response()->json($result['data'],201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 退出群聊
     * @Delete("/{member_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("group_id",required=true,description="群聊的ID即(为URL中的group_id)"),
     *     @Parameter("member_id",required=true,description="退出群聊的用户的ID即与本用户id相同(为URL中的member_id)"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(400,body={"error":"owner_does_not_remove"}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"member_not_found"}),
     *     @Response(500,body={"error":"operation_failed"}),
     * })
     */
    public function destroySelf($id,$group_id,$member_id)
    {
        try {
            if($id != $member_id){
                throw new \Exception('unauthorized',401);
            }
            DB::beginTransaction();
            $member = ChatGroupMember::where('user_id',$id)->where('group_id',$group_id)->firstOrFail();
            if('owner' == $member->type){
                if(1 != ChatGroupMember::where('group_id',$group_id)->count()){
                    throw new \Exception('owner_does_not_remove',400);
                }
                ChatGroupMember::where('group_id',$group_id)->delete();
                ChatGroup::findOrFail($group_id)->delete();
                $result = EaseMob::deleteGroup($group_id);
                if(!isset($result['data']['success'])
                    || $result['data']['success'] != true){
                    throw new \Exception('operation_failed',500);
                }
            }else{
                $result = EaseMob::deleteGroupMember($group_id,$member_id);
                if(!isset($result['data']['result']) || $result['data']['result'] != true){
                    throw new \Exception('operation_failed',500);
                }
                $member->delete();
            }
            DB::commit();
            return response('',204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'member_not_found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 删除群成员
     * @Delete("/")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("group_id",required=true,description="群聊的ID即(为URL中的group_id)"),
     * })
     * @Transaction({
     *     @Request(body={"member": {"10001","所要添加的用户ID的<数组>"}},headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(400,body={"error":"owner_does_not_remove"}),
     *     @Response(400,body={"error":"bad_request"}),
     *     @Response(401,body={"error":"unauthorized"}),
     * })
     */
    public function destroy($id,$group_id,Request $request)
    {
        try {
            $members = json_decode($request->get('member'),true);
            if(!isset($members)){
                throw new \Exception('bad_request',400);
            }
            $type = ChatGroupMember::where('user_id',$id)->where('group_id',$group_id)->firstOrFail(['type'])->type;
            if('owner' != $type){
                throw new \Exception('unauthorized',401);
            }
            DB::beginTransaction();
            $usernames = null;
            foreach ($members as $member) {
                if($member == $id){
                    throw new \Exception('owner_does_not_remove',400);
                }
                if(null === $usernames){
                    $usernames .= $member;
                }else{
                    $usernames .= ',' . $member;
                }
                ChatGroupMember::where('group_id',$group_id)->where('user_id',$member)->delete();
            }
            EaseMob::deleteGroupMember($group_id,$usernames);
            DB::commit();
            return response('',204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}