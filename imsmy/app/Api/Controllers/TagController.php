<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/13
 * Time: 16:02
 */

namespace App\Api\Controllers;


use App\Api\Transformer\TagsTransformer;
use App\Models\Tag;
use App\Models\TagMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use DB;
/**
 * 标签相关接口
 *
 * @Resource("Tag",uri="users/{id}/tags")
 */
class TagController extends BaseController
{
    protected $tagsTransformer;

    public function __construct(TagsTransformer $tagsTransformer)
    {
        $this->tagsTransformer = $tagsTransformer;
    }

    /**
     * 获取标签
     * @Get("/")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={
     *                          {
     *                              "id":1,
     *                              "name":"朋友",
     *                              "users":{
     *                                  {
     *                                      "member_id":10005
     *                                  },
     *                                  {
     *                                      "member_id":10006
     *                                  }
     *                              },
     *                              "updated_at":1463130835
     *                          },
     *                          {
     *                              "id":3,
     *                              "name":"好朋友",
     *                              "users":{
     *                                  {
     *                                      "member_id":10005
     *                                  }
     *                              },
     *                              "updated_at":1463130835
     *                          }
     *                      }),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(500,body={"error":"unknown error"})
     * })
     */
    public function index($id)
    {
        try {
            $tags = Tag::with('hasManyTagMembers')->where('user_id',$id)->get();
            return response()->json($this->tagsTransformer->transformCollection($tags->all()));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 创建标签
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("name",required=true,description="标签名称，前后空格会截掉"),
     *     @Parameter("member",required=true,description="类型为数组字符串。若查不到此用户，该用户不会添加上，但此次操作可能成功，失败原因可能为有效用户个数为0个"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"},body={"name":"请看Parameter-name","member":"请看Parameter-member"}),
     *     @Response(201,body={
     *                          "id":15,
     *                          "name":"name",
     *                          "users":{
     *                              {
     *                                  "member_id":10006,
     *                                  "success":true
     *                              }
     *                          },
     *                          "updated_at":1463223996
     *                        }),
     *     @Response(400,body={"error":"bad_request"}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(409,body={"error":"'朋友'_has_existed"}),
     *     @Response(500,body={"error":"unknown error"})
     * })
     */
    public function store($id,Request $request)
    {
        try {
            $name = trim($request->get('name'));
            $members = json_decode($request->get('member'),true);
            if(null === $name || null === $members){
                throw new \Exception('bad_request',400);
            }
            $members = array_unique($members);
            if(Tag::where('user_id',$id)->where('name',$name)->count()){
                throw new \Exception('"'. $name .'"_has_existed',409);
            }
            DB::beginTransaction();
            $tag = Tag::create([
                'user_id'   => $id,
                'name'      => $name
            ]);
            $data = [];
            $result = [];
            $time = new Carbon;
            foreach ($members as $member) {
                if(User::find($member) !== null){
                    $data[] = [
                        'tag_id'     => $tag->id,
                        'member_id'  => $member,
                        'created_at' => $time,
                        'updated_at' => $time
                    ];
                    $result[] = ['member_id' => $member,'success' => true];
                }else{
                    $result[] = ['member_id' => $member,'success' => false];
                }
            }
            if([] === $data){
                throw new \Exception('bad_request',400);
            }
            DB::table('tag_member')->insert($data);
            DB::commit();
            return response()->json([
                'id'         => $tag->id,
                'name'       => $tag->name,
                'users'      => $result,
                'updated_at' => strtotime($tag->updated_at)
            ],201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 更新标签
     * @Put("/{tag_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("tag_id",required=true,description="要更新的标签ID即(为URL中的tag_id)"),
     *     @Parameter("name",description="标签名称，前后空格会截掉"),
     *     @Parameter("member",description="请提交现有用户，类型为数组字符串。若查不到此用户，该用户不会添加上，有效用户为0个时，result字段不会返回"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"},body={"name":"请看Parameter-name","member":"请看Parameter-member"}),
     *      @Response(201,body={
     *                          "id":15,
     *                          "name":"name",
     *                          "users":{
     *                              {
     *                                  "member_id":10006,
     *                                  "success":true
     *                              }
     *                          },
     *                          "updated_at":1463223996
     *                        }),
     *     @Response(400,body={"error":"bad_request"}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(409,body={"error":"'朋友'_has_existed"}),
     *     @Response(500,body={"error":"unknown error"})
     * })
     */
    public function update($id,$tag_id,Request $request)
    {
        try {
            $name = trim($request->get('name'));
            if(!isset($name)){
                throw new \Exception('bad_request',400);
            }
            $members = json_decode($request->get('member'),true);
            $tag = Tag::findOrFail($tag_id);
            if($tag_id != $tag->id){
                throw new \Exception('unauthorized',401);
            }
            DB::beginTransaction();
            if(null !== $name){
                if(Tag::where('user_id',$id)->where('name',$name)->count() > 1){
                    throw new \Exception('"'. $name .'"_has_existed',409);
                }
                $tag->name = $name;
                $tag->save();
            }
            TagMember::where('tag_id',$tag_id)->delete();
            $data = [];
            $result = [];
            $time = new Carbon;
            if(isset($members)){
                foreach ($members as $member) {
                    if(User::find($member) !== null){
                        $data[] = [
                            'tag_id'     => $tag->id,
                            'member_id'  => $member,
                            'created_at' => $time,
                            'updated_at' => $time
                        ];
                        $result[] = ['member_id' => $member,'success' => true];
                    }else{
                        $result[] = ['member_id' => $member,'success' => false];
                    }
                }
                DB::table('tag_member')->insert($data);
            }
            DB::commit();
            return response()->json([
                'id'         => $tag->id,
                'name'       => $tag->name,
                'users'      => $result,
                'updated_at' => strtotime($tag->updated_at)
            ],201);
        } catch(ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found'],404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 删除标签
     * @Delete("/{tag_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="添加者的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("tag_id",required=true,description="要更新的标签ID即(为URL中的tag_id)"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"tag_not_found"}),
     *     @Response(500,body={"error":"unknown error"})
     * })
     */
    public function destroy($id,$tag_id)
    {
        try {
            $tag = Tag::findOrFail($tag_id);
            if($id != $tag->user_id){
                throw new \Exception('unauthorized',401);
            }
            DB::beginTransaction();
            TagMember::where('tag_id',$tag_id)->delete();
            $tag->delete();
            DB::commit();
            return response('',204);
        } catch(ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}