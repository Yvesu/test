<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/21
 * Time: 18:24
 */

namespace App\Api\Controllers;

use App\Api\Transformer\TweetLikesTransformer;
use App\Api\Transformer\UsersWithSubTransformer;
use App\Models\Notification;
use App\Models\Tweet;
use App\Models\Activity;
use App\Models\TweetActivity;
use App\Models\User;
use App\Models\Topic;
use App\Models\TweetLike;
use App\Models\Blacklist;
use App\Models\Friend;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * 动态-点赞相关接口
 *
 * @Resource("TweetsLikes")
 */
class TweetLikeController extends BaseController
{
    // 页码
    protected $paginate = 20;

    protected $tweetLikesTransformer;

    protected $usersWithSubTransformer;

    public function __construct(TweetLikesTransformer $tweetLikesTransformer, UsersWithSubTransformer $usersWithSubTransformer)
    {
        $this->tweetLikesTransformer = $tweetLikesTransformer;
        $this->usersWithSubTransformer = $usersWithSubTransformer;
    }

    /**
     * 获取动态-点赞详情
     * @Post("tweets/{tweet_id}/likes")
     * @Versions({"v1"})
     * @Transaction({
     *     @Response(201,body={{"id":3,"user":{"id":10000,"nickname":"nickname","avatar":null,"hash_avatar":null},"created_at":1464668984}}),
     *     @Response(404,body={"error":"tweet_not_found"}),
     * })
     *
     */
    public function index($id,Request $request)
    {
        try {
            // 查询话题是否为屏蔽话题
            Tweet::able()->findOrFail($id);

            // 获取点赞信息
            $tweet_likes = TweetLike::with('belongsToManyUser')
                                    ->where('tweet_id',$id)
                                    ->ofSecond((int)$request -> get('last_id'))
                                    ->orderBy('id','desc')
                                    ->take($this->paginate)
                                    ->get();
            // 返回数据
            return response()->json([

                // 数据
                'data' => count($tweet_likes) ? $this->tweetLikesTransformer->transformCollection($tweet_likes->all()) : null,

                // 总数量
                'count' => count($tweet_likes),

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link' => count($tweet_likes)
                    ? $request->url() .
                    '?last_id='.$tweet_likes -> last() -> id
                    : null      // 如果数量为0，则不附带条件
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'tweet_not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 动态-点赞
     * @Post("users/{id}/tweets/{tweet_id}/likes")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"id":"此次点赞ID"}),
     *     @Response(404,body={"error":"tweet_not_found"}),
     * })
     *
     */
    public function create($id,$tweet_id)
    {
        try{
            // 判断该用户是否已经点过赞，如果已经点过赞，则直接返回点赞数据的id
            $like = TweetLike::where('tweet_id',$tweet_id)->where('user_id',$id)->first();

            if ($like === null) {

                // 判断该动态是否存在
                $tweet = Tweet::able()->findOrFail($tweet_id);

                // 判断是否在黑名单内
                if(Blacklist::ofBlackIds($id,$tweet->user_id)->first()){

                    // 在自己的黑名单中
                    return response()->json(['error'=>'in_own_black_list'],431);
                }elseif(Blacklist::ofBlackIds($tweet->user_id,$id)->first()){

                    // 在对方的黑名单中
                    return response()->json(['error'=>'in_his_black_list'],432);
                }

                // 判断是否只有好友可看
                if(0 !== $tweet->visible){

                    // 判断是否为自己可见
                    if(3 === $tweet->visible) return response()->json(['error'=>'forbid'],403);

                    // 判断是否为好友关系
                    $friend = Friend::ofIsFriend($id, $tweet->user_id);

                    // 判断是否为好友圈私密
                    if(2 === $tweet->visible) {

                        // 好友关系
                        if($friend) return response()->json(['error'=>'forbid'],403);
                    }

                    // 非好友关系
                    if(!$friend) return response()->json(['error'=>'forbid'],403);

                    // 好友关系下，是否有权限观看该动态 是否在指定好友可看范围内
                    if(4 === $tweet->visible){
                        if(!substr_count($tweet->visible_range,$id)) return response()->json(['error'=>'forbid'],403);
                    }

                    // 好友关系下，是否有权限观看该动态 是否不在非可看名单内
                    if(5 === $tweet->visible){
                        if(substr_count($tweet->visible_range,$id)) return response()->json(['error'=>'forbid'],403);
                    }
                }

                // 存储点赞用户的id及动态的id
                $newLike = [
                    'user_id' => $id,
                    'tweet_id'   => $tweet_id,
                ];

                // 新建提醒消息
                $newNotice = [
                    'user_id'           => $id,
                    'notice_user_id'    => $tweet->user_id,
                    'type'              => 1,
                    'type_id'           => $tweet_id
                ];

                // 开启事务
                $like = DB::transaction(function() use($id,$newLike,$newNotice,$tweet_id,$tweet) {

                    // 将数据存入tweet_like 表
                    $like = TweetLike::create($newLike);

                    $user_to = User::status()->findOrFail($tweet->user_id);

                    $now = getTime();

                    // 判断是否开启了点赞提醒
                    if(1 === $user_to -> new_message_like){

                        $time = new Carbon();

                        // 将数据存入noticecation表
                        Notification::create([
                            'user_id'           => $id,
                            'notice_user_id'    => $tweet->user_id,
                            'type'              => 1,
                            'type_id'           => $tweet_id,
                            'created_at'        => $time,
                            'updated_at'        => $time
                        ]);
                    }

                    // 该动态点赞总数加1
                    $tweet -> increment('like_count');

                    // 用户统计数据 点赞量 +1
                    User::findOrfail($id) -> increment('like_count');

                    // 参与话题点赞量 +1
                    Topic::whereHas('hasManyTweetTopic', function($q) use($tweet_id) {
                        $q -> where('tweet_id',$tweet_id);
                    }) -> increment('like_count');

                    // 判断该动态是否参与了赛事
                    $tweet_activity = TweetActivity::whereHas('belongsToActivity', function($q) use($now) {
                        $q -> where('expires', '>', $now);
                    }) -> first();

                    if($tweet_activity) {

                        // 动态,点赞总量+1
                        $tweet_activity -> increment('like_count', 1, ['time_update' => $now]);

                        // 赛事,点赞总量+1
                        Activity::findOrFail($tweet_activity->activity_id) -> increment('like_count', 1, ['time_update' => $now]);
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

            // 事务回滚
            DB::rollBack();

            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e){

            // 事务回滚
            DB::rollBack();
            return response()->json(['error' => 'not_found'], 404);
        }
    }
    // TODO 带缓存 需进一步完善
//    public function create($id, $tweet_id)
//    {
//        try{
//
//            // 获取缓存中该动态的所有点赞用户id
//            $user_likes = Cache::remember('user_like_'.$tweet_id, 120, function () use ($tweet_id) {
//
//                $tweet_likes = TweetLike::where('tweet_id', $tweet_id) -> pluck('user_id');
//
//                if($tweet_likes -> count()) return $tweet_likes -> all();
//
//                return [];
//            });
//
//            // 判断该用户是否已经点过赞，如果已经点过赞，则直接返回状态
//            if(in_array($id, $user_likes))
//                return response()->json(['status' => 'ok'], 201);
//
//            // 判断该动态是否存在
//            $tweet = Tweet::with('hasOneStatistics')->able()->findOrFail($tweet_id);
//
//            // 判断是否在黑名单内
//            if(Blacklist::ofBlackIds($id,$tweet->user_id)->first()){
//
//                // 在自己的黑名单中
//                return response()->json(['error'=>'in_own_black_list'],431);
//            }elseif(Blacklist::ofBlackIds($tweet->user_id,$id)->first()){
//
//                // 在对方的黑名单中
//                return response()->json(['error'=>'in_his_black_list'],432);
//            }
//
//            // 如果只有好友可看
//            if(0 !== $tweet->visible){
//
//                // 判断是否为自己可见
//                if(3 === $tweet->visible) return response()->json(['error'=>'forbid'],403);
//
//                // 判断是否为好友关系
//                $friend = Friend::ofIsFriend($id, $tweet->user_id);
//
//                // 判断是否为好友圈私密
//                if(2 === $tweet->visible) {
//
//                    // 好友关系
//                    if($friend) return response()->json(['error'=>'forbid'],403);
//                }
//
//                // 非好友关系
//                if(!$friend) return response()->json(['error'=>'forbid'],403);
//
//                // 好友关系下，是否有权限观看该动态 是否在指定好友可看范围内
//                if(4 === $tweet->visible){
//                    if(!substr_count($tweet->visible_range,$id)) return response()->json(['error'=>'forbid'],403);
//                }
//
//                // 好友关系下，是否有权限观看该动态 是否不在非可看名单内
//                if(5 === $tweet->visible){
//                    if(substr_count($tweet->visible_range,$id)) return response()->json(['error'=>'forbid'],403);
//                }
//            }
//
//            # 将用户id添加到缓存中
//            array_push($user_likes, $id);
//
//            Cache::put('user_like_'.$tweet_id, $user_likes, 120);
//
//            $time = new Carbon();
//            $now = getTime();
//
//            // 存储点赞用户的id及动态的id，提醒消息
//            $newLike = [
//                'user_id'           => $id,
//                'tweet_id'          => $tweet_id,
//                'type'              => 1,   // 1为添加，0为取消
//                'notice_user_id'    => $tweet -> user_id,
//                'time'              => $time,
//            ];
//
//            # 更改各类统计类的缓存数据
//            // 动态缓存数据 点赞量 +1
//            Tweet::findOrfail($id) -> increment('like_count');
//
//            // 用户统计数据 点赞量 +1
//            User::findOrfail($tweet -> user_id) -> increment('like_count');
//
//            // 获取 tweet_topic 缓存数据
//            $tweet_topic = Cache::remember('tweet_topic', 120, function() {
//
//                return TweetTopic::get(['topic_id', 'tweet_id']);
//            });
//
//            // 获取动态参与的所有话题 id
//            $tweet_topic_ids = $tweet_topic -> filter(function ($value) use ($tweet_id) {
//
//                return $value -> tweet_id == $tweet_id;
//            });
//
//            // 将所有动态参与过的所有话题 缓存数据进行点赞 +1
//            foreach($tweet_topic_ids as $value) {
//
//                // 调用全局函数，自增缓存数据
//                Topic::findOrfail($value->topic_id) -> increment('like_count');
//            }
//
//            # 赛事有效期内的动态点赞,通过定时任务只需要每隔段时间更新一次 tweet_activity 表就行了，不需要重复更新
//            // 获取所有参与赛事的动态缓存信息
//            $tweet_activity = Cache::remember('tweet_activity', 1, function() use ($now) {
//
//                return TweetActivity::whereHas('belongsToActivity', function($q) use($now) {
//                    $q -> where('expires', '>', $now);
//                }) -> get(['id', 'activity_id', 'tweet_id', 'user_id', 'like_count']);
//            });
//
//            // 获取动态参与的赛事 只有一个
//            $tweet_activity -> each(function ($value) use ($tweet_id, $now) {
//
//                if($value -> tweet_id == $tweet_id ) {
//
//                    // 获取该赛事缓存信息
//                    $activity = Cache::remember('activity_'.$value->activity_id, 120, function() use ($value) {
//                        return Activity::findOrfail($value->activity_id);
//                    });
//
//                    // 如果该赛事还在截止日期内，缓存中点赞数 +1
//                    if($activity -> expires >= $now){
//                        $value -> like_count ++;
//                    }
//
//                    return false;
//                }
//            });
//
//            # 存入动态点赞队列的缓存中
//            $like_quque = Cache::remember('tweet_like_quque', 120, function () {
//
//                return [];
//            });
//
//            array_push($like_quque, json_encode($newLike));
//
//            Cache::put('like_quque', $like_quque, 120);
//
//            return response()->json(['status' => 'ok'], 201);
//
//        } catch (ModelNotFoundException $e){
//
//            // 事务回滚
//            DB::rollBack();
//
//            return response()->json(['error' => 'not_found'], 404);
//        } catch (\Exception $e){
//
//            // 事务回滚
//            DB::rollBack();
//            return response()->json(['error' => 'not_found'], 404);
//        }
//    }

    /**
     * 取消动态-点赞
     * @Post("users/{id}/tweets/{tweet_id}/unlikes")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(403,body={"error":"forbidden"}),
     *     @Response(404,body={"error":"tweet_like_not_found"}),
     * })
     */
    public function destroy($id, $tweet_id)
    {
        try{
            // 查询是否存在
            $like = TweetLike::where('user_id',$id)->where('tweet_id',$tweet_id)->firstOrFail();

            $tweet = Tweet::able()->findOrFail($tweet_id);

            // 开启事务
            DB::beginTransaction();

            //删除提醒
            Notification::where('type',1)->where('type_id', $tweet_id)->delete();

            // 该动态作者的总点赞量 -1
            // 用户统计数据 点赞量 +1
            User::findOrfail($id) -> decrement('like_count');

            // 该动态点赞量数量 -1
            $tweet -> decrement('like_count');

            $now = getTime();

            // 参与话题点赞量 +1
            Topic::whereHas('hasManyTweetTopic', function($q) use($tweet_id) {
                $q -> where('tweet_id',$tweet_id);
            }) -> decrement('like_count');

            // 判断该动态是否参与了赛事
            $tweet_activity = TweetActivity::whereHas('belongsToActivity', function($q) use($now) {
                $q -> where('expires', '>', $now);
            }) -> first();

            if($tweet_activity) {

                // 动态,点赞总量+1
                $tweet_activity -> decrement('like_count', 1, ['time_update' => $now]);

                // 赛事,点赞总量+1
                Activity::findOrFail($tweet_activity->activity_id) -> decrement('like_count', 1, ['time_update' => $now]);
            }

            // 删除tweet_like表中的数据
            $like -> delete();

            // 提交事务
            DB::commit();

            // 204(无内容)服务器成功处理了请求，但没有返回任何内容
            return response('',204);

        } catch (ModelNotFoundException $e){

            // 事务回滚
            DB::rollBack();

            return response()->json(['error' => 'tweet_like_not_found'], 404);
        } catch (\Exception $e){

            // 事务回滚
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /** 历史遗留，暂时关闭
     * TODO 用户信息页面 点击点赞时的所需数据，尚未实现
     * @param $id
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
//    public function userLike($id, Request $request)
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
//            $user = Auth::guard('api')->user();
//
//            $tweets = Tweet::where('user_id', $id);
//            if ($id != $user->id) {
//                $tweets = $tweets->where('visible', 0);
//            }
//            $tweets = $tweets->get()->pluck('id')->all();
//            /*$select = TweetLike::groupBy('user_id')->get([DB::raw('MAX(created_at)')]);
//            return ($select);*/
//            $tweet_likes = TweetLike::whereIn('tweet_id', function ($q) {
//                    $q->select(DB::raw('MAX(created_at)'))->groupBy('user_id');
//                })->where('created_at', '<', $date)
//                ->orderBy('created_at', 'desc')
//                ->take($limit)
//                ->get();
//            $like_arr = $tweet_likes->pluck('user_id')->all();
//            $like_str = implode(',', $like_arr);
//
//            $users = User::whereIn('id', $like_arr)
//                ->orderByRaw(DB::raw("FIELD(id,$like_str)"))
//                ->get();
//
//
//            $users->load([
//                'hasManySubscriptions' => function ($q) use ($user) {
//                    $q->where('from', $user->id);
//                },
//                'hasManySubscriptionsFrom' => function ($q) use ($user) {
//                    $q->where('to', $user->id);
//                }
//            ]);
//
//            $count = $tweet_likes->count();
//            return [
//                'data'       => $count ? $this->usersWithSubTransformer->transformCollection($users->all()) : [],
//                'timestamp'  => $count ? (int)strtotime($tweet_likes->last()->created_at) : null,
//                'count'      => $count,
//                'link'       => $count
//                    ? $request->url() .
//                    '?channel=subscription&limit=' . $limit .
//                    '&timestamp=' . strtotime($tweet_likes->last()->created_at)
//                    : null
//            ];
//        } catch (ModelNotFoundException $e) {
//            return response()->json(['error' => 'tweet_not_found'],404);
//        } catch (\Exception $e) {
//            return response()->json(['error' => $e->getMessage()], $e->getCode());
//        }
//    }
}