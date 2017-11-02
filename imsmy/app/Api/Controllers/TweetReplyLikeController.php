<?php

namespace App\Api\Controllers;


use App\Api\Transformer\TweetReplyLikesTransformer;
use App\Api\Transformer\UsersWithSubTransformer;
use App\Models\Blacklist;
use App\Models\TweetReply;
use App\Models\TweetReplyLike;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;
use Auth;

/**
 * 评论-点赞相关接口
 *
 * @Resource("TweetReplyLikes")
 */
class TweetReplyLikeController extends BaseController
{
    protected $tweetReplyLikesTransformer;

    protected $usersWithSubTransformer;

    public function __construct(TweetReplyLikesTransformer $tweetReplyLikesTransformer, UsersWithSubTransformer $usersWithSubTransformer)
    {
        $this->tweetReplyLikesTransformer = $tweetReplyLikesTransformer;
        $this->usersWithSubTransformer = $usersWithSubTransformer;
    }

    /**
     * 获取评论-点赞详情
     * @Post("tweets/{tweet_id}/likes")
     * @Versions({"v1"})
     * @Transaction({
     *     @Response(201,body={{"id":3,"user":{"id":10000,"nickname":"nickname","avatar":null,"hash_avatar":null},"created_at":1464668984}}),
     *     @Response(404,body={"error":"tweet_not_found"}),
     * })
     *
     */
//    public function index($id,Request $request)
//    {
//        try {
//            $limit = $request->get('limit');
//            $timestamp = $request->get('timestamp');
//
//            $limit = isset($limit) && is_numeric($limit) ? $limit : 20;
//            $timestamp = isset($timestamp) && is_numeric($timestamp) ? $timestamp : time();
//
//            $date = Carbon::createFromTimestamp($timestamp)->toDateTimeString();
//
//            $reply_likes = TweetReplyLike::with('belongsToUser')
//                                    ->where('id',$id)
//                                    ->where('created_at','<',$date)
//                                    ->take($limit)
//                                    ->orderBy('created_at','desc')
//                                    ->get();
//
//            return response()->json($this->tweetReplyLikesTransformer->transformCollection($reply_likes->all()));
//        } catch (ModelNotFoundException $e) {
//            return response()->json(['error' => 'reply_not_found'],404);
//        } catch (\Exception $e) {
//            return response()->json(['error' => $e->getMessage()], $e->getCode());
//        }
//    }

    /**
     * 评论-点赞
     * @Post("users/{id}/replies/{reply_id}/likes")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"id":"此次点赞ID"}),
     *     @Response(404,body={"error":"reply_not_found"}),
     * })
     *
     */
    public function create($id,$reply_id)
    {
        try{
            // 判断该用户是否已经点过赞，如果已经点过赞，则直接返回点赞数据的id
            $like = TweetReplyLike::where('tweet_reply_id',$reply_id)->where('user_id',$id)->first();

            if ($like === null) {

                // 判断该评论是否存在
                $reply = TweetReply::findOrFail($reply_id);

                // 判断是否在黑名单内
                if(Blacklist::ofBlackIds($id,$reply->user_id)->first()){

                    // 在自己的黑名单中
                    return response()->json(['error'=>'in_own_black_list'],431);
                }elseif(Blacklist::ofBlackIds($reply->user_id,$id)->first()){

                    // 在对方的黑名单中
                    return response()->json(['error'=>'in_his_black_list'],432);
                }

                // 存储点赞用户的id及评论的id
                $newLike = [
                    'user_id'          => $id,
                    'tweet_reply_id'   => $reply_id,
                ];

                // 新建提醒消息,暂时关闭
//                $newNotice = [
//                    'user_id'           => $id,
//                    'notice_user_id'    => $reply->user_id,
//                    'type'              => 7,
//                    'type_id'           => $reply_id
//                ];

                // 开启事务
                $like = DB::transaction(function() use($newLike) {

                    // 将数据存入tweet_reply_like 表
                    $like = TweetReplyLike::create($newLike);

                    // 将数据存入noticecation表
//                    Notification::create($newNotice);

                    // 从tweet_reply表查询新存入tweet_reply_like表中动态id的数据
                    $tweet_reply = TweetReply::find($newLike['tweet_reply_id']);

                    // 如果tweet表该动态数据不为空，则写入表内
                    if ($tweet_reply !== null) {

                        // 该动态点赞总数加1
                        $tweet_reply->like_count ++;

                        // 保存
                        $tweet_reply->save();
                    }
                    return $like;
                });
            }

            // 返回新生成的点赞数据id
            return response()->json([
                'id' => $like->id

                // 201(已创建)请求成功并且服务器创建了新的资源
            ],201);

        } catch (ModelNotFoundException $e){
            return response()->json(['error' => 'reply_not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 取消评论-点赞
     * @Delete("users/{id}/replys/{reply_id}/likes")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(403,body={"error":"forbidden"}),
     *     @Response(404,body={"error":"tweet_like_not_found"}),
     * })
     */
    public function destroy($id,$reply_id)
    {
        try{
            // 查询是否存在
            $like = TweetReplyLike::where('tweet_reply_id',$reply_id)->where('user_id',$id)->firstOrFail();

            // 验证用户信息
            if($like->user_id != Auth::guard('api')->user()->id){
                throw new \Exception("forbidden",403);
            }

            //删除提醒 暂时关闭
//            Notification::where('type',7)->where('type_id',$like->tweet_id)->delete();

            // 删除tweet_like表中的数据
            $like->delete();

            // 查询tweet表中该动态数据
            $reply = TweetReply::find($reply_id);

            // 如果不为空，点赞总数－1
            if ($reply !== null) {
                $reply->like_count --;
                $reply->save();
            }

            // 204(无内容)服务器成功处理了请求，但没有返回任何内容
            return response('',204);
        } catch (ModelNotFoundException $e){
            return response()->json(['error' => 'reply_like_not_found'], 404);
        } catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

}