<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/4
 * Time: 17:51
 */

namespace App\Api\Controllers;


use App\Api\Transformer\FriendTransformer;
use App\Models\Friend;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use DB;
/**
 * 好友相关接口(并非添加好友，只是做份备份)
 *
 * @Resource("Friend",uri="users/{id}/friends")
 */
class FriendController extends BaseController
{
    private $friendTransformer;

    public function __construct(FriendTransformer $friendTransformer)
    {
        $this->friendTransformer = $friendTransformer;
    }

    /**
     * 获取所有好友信息
     * @Get("/")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id", description="添加者的ID即登录环信的用户名"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={{"id":1,"nickname":"nickname","phone":13888888888,"remark":null,"top": 1464173703,"avatar":null,"hash_avatar":null,"updated_at":1463455750}}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"friend_not_found"})
     * })
     */
    public function index($id)
    {
        try {
            $friends = Friend::with('belongsToUser.hasOneLocalAuth')->where('from',$id)->get();
            return response()->json($this->friendTransformer->transformCollection($friends->all()));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 获取某个好友
     * @Get("/{friend_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id", description="添加者的ID即登录环信的用户名"),
     *     @Parameter("friend_id", description="添加好友的ID即环信用户名"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"id":1,"nickname":"nickname","phone":13888888888,"remark":null,"top": 1464173703,"avatar":null,"hash_avatar":null,"updated_at":1463455750}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"friend_not_found"})
     * })
     */
    public function show($id,$friend_id)
    {
        try {
            $friend = Friend::with('belongsToUser.hasOneLocalAuth')->where('from',$id)->where('to',$friend_id)->firstOrFail();
            return response()->json($this->friendTransformer->transform($friend));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 添加好友
     * @Post("/{friend_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id", description="添加者的ID即登录环信的用户名"),
     *     @Parameter("friend_id", description="添加好友的ID即环信用户名"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={{"id":1,"from":10001,"to":10002,"remark":null,"top": null,"avatar":null,"hash_avatar":null,"updated_at":1463455750},
     *                          {"id":2,"from":10002,"to":10001,"remark":null,"top": null,"avatar":null,"hash_avatar":null,"updated_at":1463455750}}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"user_not_found"})
     * })
     */
    public function store($id,$friend_id)
    {
        try {
            DB::beginTransaction();
            if(Friend::where('from',$id)->where('to',$friend_id)->count()){
                throw new \Exception('relationship_has_existed',409);
            }
            $from = Friend::create([
                'from'   => $id,
                'to'     => $friend_id
            ]);
            $to = Friend::create([
                'from'   => $friend_id,
                'to'     => $id
            ]);
            DB::commit();
            return response()->json([
                $this->friendTransformer->transform($from),
                $this->friendTransformer->transform($to)
            ],201);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'user_not_found'],404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 更新好友备注
     * @Put("/{friend_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id", description="添加者的ID即登录环信的用户名"),
     *     @Parameter("friend_id", description="添加好友的ID即环信用户名"),
     *     @Parameter("remark",description="备注名称",default="null"),
     *     @Parameter("top",description="传时间戳")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"},body={"remark":"请看Parameters-remark","top":"请看Parameters-top"}),
     *     @Response(201,body={"id":1,"from":10001,"to":10002,"remark":"天才","top": 1464173703,"avatar":null,"hash_avatar":null,"updated_at":1463455750}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"friend_not_found"})
     * })
     */
    public function update($id,$friend_id,Request $request)
    {
        try {
            $remark = $request->get('remark');
            $top = $request->get('top');
            $friend = Friend::where('from',$id)->where('to',$friend_id)->firstOrFail();
            $friend->remark = $remark;
            $friend->top = $top !== null ? Carbon::createFromTimestampUTC($top) : null;
            $friend->save();
            return response()->json($this->friendTransformer->transform($friend),201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
    
    /**
     * 删除好友
     * @Delete("/{friend_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id", description="添加者的ID即登录环信的用户名"),
     *     @Parameter("friend_id", description="添加好友的ID即环信用户名"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"user_not_found"})
     * })
     */
    public function destroy($id,$friend_id)
    {
        try {
            DB::beginTransaction();
            $friend = Friend::where(function($q) use($id,$friend_id){
                                $q->where('from',$id)
                                  ->where('to',$friend_id);
                             })->orWhere(function($q) use($id,$friend_id){
                                $q->where('from',$friend_id)
                                  ->where('to',$id);
                             });
            $friend->delete();
            DB::commit();
            return response()->json('',204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }
}