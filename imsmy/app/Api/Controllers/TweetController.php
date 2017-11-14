<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ChannelTweetsTransformer;
use App\Api\Transformer\TweetsLikeTransformer;
use App\Api\Transformer\TopicsTransformer;
use App\Api\Transformer\AttentionTweetsTransformer;
use App\Api\Transformer\TweetsTransformer;
use App\Api\Transformer\TweetsAtTransformer;
use App\Api\Transformer\TweetHotRepliesTransformer;
use App\Api\Transformer\TweetsSearchTransformer;
use App\Api\Transformer\HotTweetsTransformer;
use App\Api\Transformer\TweetsDetailsTransformer;
use App\Api\Transformer\TweetsNearbyTransformer;
use App\Api\Transformer\TweetsTrophyLogTransformer;
use App\Api\Transformer\TweetsPersonalTransformer;
use App\Api\Transformer\ZxHomeImagesTransformer;
use App\Api\Transformer\ActivityTweetsTransformer;
use App\Api\Transformer\ActivityTweetDetailsTransformer;
use App\Api\Transformer\ActivityDiscoverTransformer;
use App\Api\Transformer\TemplateDiscoverTransformer;
use App\Api\Transformer\AdsDiscoverTransformer;
use App\Models\Activity;
use App\Models\Blacklist;
use App\Models\Channel;
use App\Models\Friend;
use App\Models\HotSearch;
use App\Models\Keywords;
use App\Models\Make\MakeTemplateFile;
use App\Models\TweetActivity;
use App\Models\TweetContent;
use App\Models\TweetHot;
use App\Models\Location;
use App\Models\AdvertisingRotation;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\Topic;
use App\Models\Tweet;
use App\Models\TweetAt;
use App\Models\TweetLike;
use App\Models\ChannelTweet;
use App\Models\TweetPhone;
use App\Models\TweetTopic;
use App\Models\TopicUser;
use App\Models\TweetReply;
use App\Models\TweetTrophyLog;
use App\Models\UserSearchLog;
use App\Models\User;
use App\Models\Word_filter;
use Carbon\Carbon;
use CloudStorage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\GoldTransactionService;
use Auth;
use Illuminate\Support\Facades\Cache;
use DB;
use Illuminate\Support\Collection;
use App\Services\TweetService;

/**
 * 动态相关接口 
 *
 * @Resource("Tweets")
 */
class TweetController extends BaseController
{
    // 默认所取条数
    protected $paginate = 20;

    protected $tweetsTransformer;

    protected $channelTweetsTransformer;

    protected $topicsTransformer;

    protected $hotTweetsTransformer;

    protected $tweetsSearchTransformer;

    // 动态详情 details方法
    protected $tweetsDetailsTransformer;

    // 动态评论详情 details方法
    protected $tweetHotRepliesTransformer;

    // 动态详情，推荐视频处理
    protected $tweetsAtTransformer;

    // 动态详情，颁奖嘉宾
    protected $tweetsTrophyLogTransformer;

    protected $tweetsPersonalTransformer;

    // 频道动态，广告位
    protected $channelAdsTransformer;

    // 附近动态，
    private $tweetsNearbyTransformer;

    //
    private $zxHomeImagesTransformer;

    // 首页关注页面动态
    protected $attentionTweetsTransformer;

    // 赛事（原活动）详情页动态
    protected $activityTweetsTransformer;

    // 赛事（原活动）详情页动态 -- 新20170818
    protected $activityTweetDetailsTransformer;

    // 点赞动态
    protected $tweetsLikeTransformer;

    // 发现页面赛事
    protected $activityDiscoverTransformer;

    // 发现页面模板
    protected $templateDiscoverTransformer;

    // 发现页面广告
    protected $adsDiscoverTransformer;

    public function __construct(
        TweetsTransformer $tweetsTransformer,
        ChannelTweetsTransformer $channelTweetsTransformer,
        TopicsTransformer $topicsTransformer,
        HotTweetsTransformer $hotTweetsTransformer,
        TweetsSearchTransformer $tweetsSearchTransformer,
        TweetsDetailsTransformer $tweetsDetailsTransformer,
        TweetHotRepliesTransformer $tweetHotRepliesTransformer,
        TweetsAtTransformer $tweetsAtTransformer,
        TweetsNearbyTransformer $tweetsNearbyTransformer,
        TweetsTrophyLogTransformer $tweetsTrophyLogTransformer,
        TweetsPersonalTransformer $tweetsPersonalTransformer,
        ZxHomeImagesTransformer $zxHomeImagesTransformer,
        AttentionTweetsTransformer $attentionTweetsTransformer,
        ActivityTweetsTransformer $activityTweetsTransformer,
        ActivityTweetDetailsTransformer $activityTweetDetailsTransformer,
        TweetsLikeTransformer $tweetsLikeTransformer,
        ActivityDiscoverTransformer $activityDiscoverTransformer,
        TemplateDiscoverTransformer $templateDiscoverTransformer,
        AdsDiscoverTransformer $adsDiscoverTransformer
    )
    {
        $this -> tweetsTransformer = $tweetsTransformer;
        $this -> channelTweetsTransformer = $channelTweetsTransformer;
        $this -> topicsTransformer = $topicsTransformer;
        $this -> hotTweetsTransformer = $hotTweetsTransformer;
        $this -> tweetsSearchTransformer = $tweetsSearchTransformer;
        $this -> tweetsDetailsTransformer = $tweetsDetailsTransformer;
        $this -> tweetHotRepliesTransformer = $tweetHotRepliesTransformer;
        $this -> tweetsAtTransformer = $tweetsAtTransformer;
        $this -> tweetsNearbyTransformer = $tweetsNearbyTransformer;
        $this -> tweetsTrophyLogTransformer = $tweetsTrophyLogTransformer;
        $this -> tweetsPersonalTransformer = $tweetsPersonalTransformer;
        $this -> zxHomeImagesTransformer = $zxHomeImagesTransformer;
        $this -> attentionTweetsTransformer = $attentionTweetsTransformer;
        $this -> activityTweetsTransformer = $activityTweetsTransformer;
        $this -> activityTweetDetailsTransformer = $activityTweetDetailsTransformer;
        $this -> tweetsLikeTransformer = $tweetsLikeTransformer;
        $this -> activityDiscoverTransformer = $activityDiscoverTransformer;
        $this -> templateDiscoverTransformer = $templateDiscoverTransformer;
        $this -> adsDiscoverTransformer = $adsDiscoverTransformer;
    }

    /**
     * 搜索某个用户的动态
     */
    public function index($id, Request $request)
    {
        try {

            // 获取要查询的关键词 及 所取页数
            if(!is_numeric($page = $request -> get('page',1)))
                return response()->json(['error'=>'bad_request'],403);

            $search = removeXSS($request->get('search'));

            // 通过预先加载，获取要查询用户的动态，包括用户的信息、该用户原创并所属频道为在用的的、及所属频道在用的

            // 获取登录用户信息
            $user = Auth::guard('api')->user();

            // 判断是否为好友关系
            if ($user && Friend::ofIsFriend($id, $user->id)->first()) {

                // 查询可以查看的好友的动态
                $tweets = Tweet::ofFriendTweets($user->id);

                // 自己
            } else if ($user && $user->id == $id) {

                $tweets = new Tweet();
            } else {

                // 非好友关系，只能看对方设置为公开的
                $tweets = Tweet::where('visible', 0);
            }

            // 按类型查找并获取数据
            $tweets = $tweets->with([
                'hasOneContent' => function ($query) {
                    $query->select(['tweet_id','content']);
                },
                'belongsToUser' => function ($query) {
                    $query->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
                },
                // 预先加载嵌套关系
                'hasOneOriginal'=> function ($query) {
                    $query->able()->select(['id','type','user_id','location','user_top','photo','screen_shot','video','created_at']);
                },
                'hasManyTweetReply'=>function($q){
                    $q -> with(['belongsToUser' => function ($query) {
                        $query -> select(['id','nickname']);
                    }])
                        -> status()
                        -> where('anonymity',0)
                        -> orderBy('like_count','DESC')
                        -> select(['id', 'user_id', 'tweet_id', 'content','created_at']);
                }])
                -> ofSearch($search)
                -> able()
                -> where('user_id', $id)
                -> orderBy('user_top', 'DESC')
                -> orderBy('id', 'DESC')
                -> forPage($page,$this->paginate)
                -> get(['id','type','user_id','location','user_top','photo','screen_shot','video','created_at']);

            // 将所关注用户的未读视频设置为0
            if($user && $user->id != $id){

                $subscription = Subscription::where('from',$user->id) -> where('to',$id) -> first();

                if($subscription){
                    $subscription -> unread = 0;
                    $subscription -> save();
                }
            }

            // 统计或获取数据的数量
            $count = $tweets -> count();

            $data = $count ? $this->tweetsPersonalTransformer->transformCollection($tweets->all()) : [];

            // 返回数据
            return [
                // 所获取的数据
                'data'       => $data,

                // 应取数据的条数
                'count'      => $this->paginate,
            ];
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 获取某个具体动态
     *
     * @Post("tweets/{id}/details?{limit,timestamp}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("limit", description="每次返回评论的最大条数",default=20),
     *      @Parameter("timestamp", description="每次起始时间点",default="当前时间"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN 可选"}),
     *     @Response(200,body={
     *                          "tweets_data": [				// 动态数据
                                                {
                                                "id": 91,
                                                "type": 0,
                                                "video": "http://7xtg0b.com1.z0.glb.clouddn.com/tweet/57/o_1b0klsp3jelmojd1g4r18ce1ei496.mp4",
                                                "photo": null,
                                                "already_like": 0,
                                                "like_count": 7,
                                                "reply_count": 4,
                                                "retweet_count": null,
                                                "content": "杜可风个哭点功夫的时光",
                                                "location": null,
                                                "shot_width_height": "500*281",
                                                "screen_shot": "http://7xtg0b.com1.z0.glb.clouddn.com/tweet/57/14781624603129079_500*281_.gif",
                                                "user": {				// 发表该动态的用户的信息
                                                "id": 1000234,
                                                "nickname": "追喜_1000234",
                                                "avatar": null,
                                                "signature": null
                                                },
                                                "created_at": 1478133660,	// 动态发表时间
                                                "tweet_grade": "61.4"		// 总平均评分
                                                }
                                                ],
                                                "recommend_tweets": [				// 推荐视频
                                                {
                                                "id": 2,
                                                "type": 0,
                                                "picture": "http://7xtg0b.com1.z0.glb.clouddn.com/tweet/23/147771284557d0d1042a7ec_1024.jpg"
                                                },
                                                {
                                                "id": 44,
                                                "type": 0,
                                                "picture": "http://7xtg0b.com1.z0.glb.clouddn.com/tweet/44/14779799965.jpg"
                                                },
                                                {
                                                "id": 45,
                                                "type": 0,
                                                "picture": "http://7xtg0b.com1.z0.glb.clouddn.com/tweet/45/14779895688.jpg"
                                                },
                                                {
                                                "id": 46,
                                                "type": 0,
                                                "picture": "http://7xtg0b.com1.z0.glb.clouddn.com/tweet/46/14779899229.jpg"
                                                },
                                                {
                                                "id": 47,
                                                "type": 0,
                                                "picture": "http://7xtg0b.com1.z0.glb.clouddn.com/tweet/47/1477994100IMG_4849.JPG"
                                                }
                                                ],
                                                "trophy_users": [				// 颁奖嘉宾
                                                {
                                                "user_id": 1000240,
                                                "avatar": null
                                                },
                                                {
                                                "user_id": 1000242,
                                                "avatar": "http://7xtg0b.com1.z0.glb.clouddn.com/topic/11/14793690643377046_300*300_.jpg"
                                                }
                                                ],
                                                "hot_replys": [						// 热门评论数据，不大于四条
                                                {
                                                "id": 15,
                                                "reply_id": 1000220,
                                                "content": "评论测试",
                                                "created_at": 1481080162,
                                                "user": {
                                                "id": 1000240,
                                                "nickname": "发生的范德萨",
                                                "avatar": "http://7xtg0b.com1.z0.glb.clouddn.com/topic/11/14793690643377046_300*300_.jpg",
                                                "signature": null
                                                },
                                                "like_count": 50,
                                                "already_like": "true"		// 该用户已对该评论点赞
                                                }
                                                ],
                                                "replys": [							// 普通评论，不大于20条
                                                {
                                                "id": 15,
                                                "reply_id": 1000220,		// 该评论为对该动态下的评论的回复
                                                "content": "评论测试",		// 评论内容
                                                "created_at": 1481080162,	// 评论时间
                                                "user": {					// 评论用户的信息
                                                "id": 1000240,
                                                "nickname": "发生的范德萨",
                                                "avatar": "http://7xtg0b.com1.z0.glb.clouddn.com/topic/11/14793690643377046_300*300_.jpg",
                                                "signature": null
                                                },
                                                "like_count": 50,				// 该评论点赞总数
                                                "already_like": "true"			// 该用户已对该评论点赞
                                                },
                                                {
                                                "id": 23,
                                                "reply_id": 0,					// 该评论为对该动态的评论
                                                "content": "评论测试第二条",
                                                "created_at": 1481080162,
                                                "user": {
                                                "id": 1000240,
                                                "nickname": "发生的范德萨",
                                                "avatar": "http://7xtg0b.com1.z0.glb.clouddn.com/topic/11/14793690643377046_300*300_.jpg",
                                                "signature": null
                                                },
                                                "like_count": 30,
                                                "already_like": "false"			// 该用户未对该评论点赞
                                                }
                                                ],
                                                "count": 5,
                                                "link": "http://www.goobird.com/api/tweets/91/details?limit=20&timestamp=1481017974"
     *     }),
     *     @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function details($id, Request $request)
    {
        try {
            $page = (int)$request -> get('page', 1);

            // 获取要查询的动态详情
            $tweets_data = Tweet::with([
                'hasOneContent',
                'belongsToManyTopic',
                'hasManyAt',
                'belongsToUser'=>function($q){
                    $q -> select('id', 'advertisement','nickname','avatar','hash_avatar','verify');
                }])
                -> able()
                -> findOrFail($id);

            // 判断用户是否为登录状态
            $user = Auth::guard('api')->user();

            // 判断是否只有好友可看
            $friends = $this -> friends($user, $tweets_data);

            // 判断是否允许
            if(403 === $friends) return response()->json(['error'=>'forbid'],403);

            // 取20条评论普通评论，按时间排序
            $replys = TweetReply::with(['belongsToUser' => function($q){
                $q -> select('id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info');
            }])
                -> where('tweet_id',$id)
                -> where('reply_id',NULL)
                -> orderBy('like_count','desc')
                -> status()
                -> forPage($page, $this -> paginate)
                -> get(['id', 'user_id', 'content', 'created_at', 'anonymity', 'like_count', 'grade']);

            // 统计或获取数据的数量
            $count = $replys->count();


            // 非第一次请求，只返回评论信息
            if($page > 1) {

                // 返回评论数据
                return [

                    // 评论数据
                    'replys'    =>  $this->tweetHotRepliesTransformer->transformCollection($replys->all()),

                    // 本次获取数据的总数量
                    'count'      => $count,

                    // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                    'link'       => $count
                        ? $request->url() .
                        '?page=' . ++$page  // 下一页
                        : ''      // 如果数量为0，则不附带搜索条件
                ];
            }

            // 获取颁奖嘉宾信息集合
            $trophy_users = TweetTrophyLog::where('tweet_id', $id)
                -> orderBy('id', 'desc')
                -> take(5)
                -> get(['anonymity', 'from']);

            // 初始化
            $trophy = [];

            // 判断是否有嘉宾数据
            if($trophy_users -> count()){

                foreach($trophy_users as $trophy_user)
                {
                    // 判断嘉宾是否为匿名
                    if($trophy_user -> anonymity === 1) {

                        // 匿名嘉宾只返回匿名信息为1的字段
                        $trophy[] = ['anonymity' => 1];
                    } else {

                        // 取出对应的头像信息
                        $avatar = User::findOrFail($trophy_user -> from, ['avatar', 'verify']);

                        // 公开嘉宾返回具体用户id及头像信息等
                        $trophy[] = [
                            'anonymity' => 0,
                            'user_id' => $trophy_user->from,
                            'avatar' => CloudStorage::downloadUrl($avatar->avatar),
                            'verify' => $avatar->verify
                        ];
                    }
                }
            }

            //TODO
            $hot_set = 10;

            // 取出热评信息，目前暂定20个赞以上为热评
            $hot_replys = TweetReply::with(['belongsToUser' => function($q){
                $q -> select('id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info');
            }])
                -> where('tweet_id', $id)
                -> where('like_count', '>', $hot_set)
                -> status()
                -> orderBy('like_count', 'DESC')
                -> take(3)
                -> get(['id', 'user_id', 'content', 'created_at', 'anonymity', 'like_count', 'grade']);

            //按时间排序的评论
            $now_replys = TweetReply::with(['belongsToUser' => function($q){
                $q -> select('id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info');
            }])
                -> where('tweet_id', $id)
                -> status()
                -> orderBy('created_at', 'DESC')
                -> forPage($page, $this -> paginate)
                -> get(['id', 'user_id', 'content', 'created_at', 'anonymity', 'like_count', 'grade']);

           // $reply_data =  array_merge($hot_replys->toArray(),$now_replys->toArray());

          //  $replys_data = mult_unique($reply_data);

            // 如果热评信息超过4条，就随机取出4条
//            if($hot_replys -> count() > 4) $hot_replys = $hot_replys -> random(4);

            // 声明广告地址变量
            $advertisement = '';

//            $advertisement = 'http://7xtg0b.com1.z0.glb.clouddn.com/tweet/57/o_1b0klsp3jelmojd1g4r18ce1ei496.mp4';

            // 判断是否推送广告
            if(0 === $tweets_data->belongsToUser->advertisement){

                // 用户未登录或登录后接受广告推送
                if(!$user || 0 === $user->advertisement){

                    $advertisement = '';

                    // 获取广告信息
//                    $advertisement = 'http://7xtg0b.com1.z0.glb.clouddn.com/tweet/57/o_1b0klsp3jelmojd1g4r18ce1ei496.mp4';
                }
            }

            // 动态浏览次数 +1
            $tweetPlay = new TweetPlayController();
            $tweetPlay -> countIncrement($tweets_data -> id, $user);

            // 返回数据
            return [

                // 广告信息
                'advertisement' => $advertisement,

                // 该动态详情与发表用户详情
                'tweets_data' => $this -> tweetsDetailsTransformer->transform($tweets_data),

                // 推荐动态信息
                'recommend_tweets' => $this -> related($id),

                // 颁奖嘉宾信息
                'trophy_users' => $trophy,

                // 颁奖嘉宾总数量
                'trophy_count' => $trophy_users -> count(),

                // 热门评论
                'hot_replys'  =>$this->tweetHotRepliesTransformer->transformCollection($hot_replys->all()),  //

                // 评论
                'replys'  =>  $this->tweetHotRepliesTransformer->transformCollection($now_replys->all()),

                // 本次获取评论的总数量
                'count'      => $count,

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link'       => $count
                    ? $request->url() .
                    '?page=' . ++$page  // 下一页
                    : ''      // 如果数量为0，则不附带搜索条件
            ];
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 动态简版，为发现页面附近使用
     * @param $id 动态id
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function information($id)
    {
        try {
            // 获取要查询的动态详情
            $tweets_data = Tweet::with('hasOneContent','belongsToUser')->able()->findOrFail($id);

            // 判断用户是否为登录状态
            $user = Auth::guard('api')->user();

            // 判断是否只有好友可看
            $friends = $this -> friends($user, $tweets_data);

            // 判断是否允许
            if(403 === $friends) return response()->json(['error'=>'forbid'],403);

            // 动态浏览次数 +1
            $tweetPlay = new TweetPlayController();
            $tweetPlay -> countIncrement($id, $user);

            // 返回数据
            return response()->json($this->tweetsNearbyTransformer->transform($tweets_data));

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 判断是否只有好友可看
     * 为方法 details 和 information 调用使用
     *
     * @param $user 登录用户的数据
     * @param $tweets_data 动态集合
     * @return int
     */
    protected function friends($user,$tweets_data){

        // 判断是否只有好友可看
        if(0 !== $tweets_data->visible){

            // 如果用户非登录状态，返回错误信息
            if(!$user) return 403;

            // 判断是否为自己可见
            if(3 === $tweets_data->visible) return 403;

            // 判断是否为好友关系
            $friend = Friend::ofIsFriend($user->id, $tweets_data->user_id);

            // 判断是否为好友圈私密
            if(2 === $tweets_data->visible) {

                // 好友关系
                if($friend) return 403;
            }

            // 非好友关系
            if(!$friend) return 403;

            // 好友关系下，是否有权限观看该动态 是否在指定好友可看范围内
            if(4 === $tweets_data->visible){
                if(!substr_count($tweets_data->visible_range,$tweets_data->id)) return 403;
            }

            // 好友关系下，是否有权限观看该动态 是否不在非可看名单内
            if(5 === $tweets_data->visible){
                if(substr_count($tweets_data->visible_range,$tweets_data->id)) return 403;
            }
        }

        // 可看
        return 200;
    }

    /**
     * 为更多相关提供数据
     * @param $tweet_id 动态id
     * @return array
     */
    protected function related($tweet_id)
    {

        # 相关 同一话题下
//        $topic_ids = TweetTopic::where('tweet_id', $tweet_id) -> pluck('topic_id') -> all();
//
//        $tweet_topic = TweetTopic::whereIn('topic_id', $topic_ids)
//            -> take(200)
//            -> pluck('tweet_id')
//            -> all();

        # 相关 同一赛事下
//        $activity_ids = TweetActivity::where('tweet_id', $tweet_id) -> pluck('activity_id') -> all();
//
//        $tweet_activity = TweetActivity::whereIn('activity_id', $activity_ids)
//            -> take(200)
//            -> pluck('tweet_id')
//            -> all();

        # 相关 同一频道下
//        $channel_ids = ChannelTweet::where('tweet_id', $tweet_id) -> pluck('channel_id') -> all();

//        $tweet_channel = ChannelTweet::whereIn('channel_id', $channel_ids)
//            -> orderBy('id', 'DESC')
//            -> take(100)
//            -> pluck('tweet_id')
//            -> all();

        # 相关 普通无关动态
//        $tweet_common = Tweet::visible()
//            -> where('type', 0)
//            -> where('original', 0)
//            -> orderBy('like_count', 'DESC')
//            -> able()
//            -> take(100)
//            -> pluck('id')
//            -> all();

//        $tweet_merge_ids = array_unique(array_merge($tweet_topic, $tweet_activity, $tweet_channel, $tweet_common));
//        $tweet_merge_ids = array_unique(array_merge($tweet_channel, $tweet_common));

//        $tweet_ids = array_values(array_diff($tweet_merge_ids, [$tweet_id]));

        # 相关 同一频道下
        $channel_ids = ChannelTweet::where('tweet_id', $tweet_id) -> pluck('channel_id') -> all();

        $tweets = Tweet::with(['hasOneContent', 'belongsToUser' => function($q){
            $q -> select('id', 'nickname');
        }]) -> whereHas('hasManyChannelTweet', function($q) use($channel_ids) {
            $q -> whereIn('channel_id', $channel_ids);
        })  -> visible()
            -> where('type', 0)
            -> where('original', 0)
            -> orderBy('like_count', 'DESC')
            -> able()
            -> take(100)
            -> get(['id', 'type', 'duration', 'user_id', 'screen_shot', 'photo', 'created_at','browse_times','video']);

        // 相关不足20个，则从普通取  TODO 上线前开启
        if($tweets -> count() < 20) {

            $tweets = Tweet::with(['hasOneContent', 'belongsToUser' => function($q){
                $q -> select('id', 'nickname');
            }]) -> visible()
                -> where('type', 0)
                -> where('original', 0)
                -> orderBy('like_count', 'DESC')
                -> able()
                -> take(100)
                -> get(['id', 'type', 'duration', 'user_id', 'screen_shot', 'photo', 'created_at','browse_times','video']);
        }

        // 判断数量 随机取20个
        return $this->tweetsAtTransformer->transformCollection($tweets->random(2)->values()->all());

    }

    /**
     * 发布动态 历史遗留比较多
     *
     * @Post("users/{id}/tweets")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("retweet", description="转发时不能为空,为要转发的动态ID"),
     *      @Parameter("original", description="转发时不能为空，为要转发的原始动态的ID"),
     *      @Parameter("content", description="文字信息，不是转发时，与image、video不能同时为空"),
     *      @Parameter("photo", description="图片字符串数组URL，不是转发时，与content、video不能同时为空"),
     *      @Parameter("video", description="视频URL，不是转发时，与content、image不能同时为空"),
     *      @Parameter("location", description="地理位置信息，直接存入数据库"),
     *      @Parameter("type",required=true,description="动态类型，0 => video , 1 => photo, 2=>text"),
     *      @Parameter("visible",required=true, description="0 : 全部可见，1 : 好友圈可见，2 : 好友圈私密，3 : 仅自己可见，4 : 指定好友可见", default=0),
     *      @Parameter("visible_range", description="type为4时，不能为空")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"id":6,"type":"2","video":null,"photo":null,"already_like":0,"like":0,"reply":0,"content":"动态内容","location":null,"screen_shot":null,"channel":{},"user":{"id":10001,"nickname":"test","avatar":null,"hash_avatar":null},"retweet":null,"created_at":1464678477}),
     *     @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function create($id,Request $request)
    {
        try{

            // 接收全部信息
            $input = $request -> all();

            $time = getTime();

            // 判断是否在黑名单里面
            if(isset($input['retweet'])){

                $to_id = Tweet::findOrFail($input['retweet'])->user_id;

                // 判断是否在黑名单内
                if(Blacklist::ofBlackIds($id,$to_id)->first()){

                    // 在自己的黑名单中
                    return response()->json(['error'=>'in_own_black_list'],431);
                }elseif(Blacklist::ofBlackIds($to_id,$id)->first()){

                    // 在对方的黑名单中
                    return response()->json(['error'=>'in_his_black_list'],432);
                }
            }

            $newTweet = [
                'user_id'       =>  $id,
                'retweet'       =>  (int)$request->get('retweet'),
                'original'      =>  (int)$request->get('original'),
                'photo'         =>  $request->get('photo'),
                'video'         =>  $request->get('video'),
                'duration'      =>  (int)$request->get('duration'),
                'size'          =>  (int)$request->get('size',0),
                'screen_shot'   =>  $request->get('screen_shot'),
                'shot_width_height'   =>  $request->get('shot_width_height'),
                'type'          =>  (int)$request->get('type') ?? 0,
                'visible'       =>  (int)$request->get('visible') ?? 0,
                'visible_range' =>  $request->get('visible_range'),
            ];

            // 内容 过滤内容
            $content = $request->get('content') ? removeXSS($request->get('content')) : null;

            // 判断
            if($newTweet['video']){

                // 判断是否为数字
                if(!$newTweet['duration']) return response()->json(['error'=>'bad_request'],403);
            }

            // 通过自定义函数，获取内容中是否包含所@的用户的id
            $at = $content ? $this->regexAt($content) : '';

            // 通过自定义函数，判断内容中是否有话题名称，如果有则返回内容中的话题名称
            $topics = $content ? $this->regexTopic($content) : '';

            // 判断用户是否为空，或者信息填写不全，则返回400错误
            if((is_null($newTweet['retweet'])
                    && is_null($content)
                    && is_null($newTweet['photo'])
                    && is_null($newTweet['video'])
                ) || (4 == $newTweet['visible']
                    && is_null($newTweet['visible_range'])
                )
            ){
                return response()->json([
                    'error' => 'bad_request'
                ],400);
            }

            // 判断visible是否为空，如果为空，增加默认值
//            if(!$newTweet['visible']) $newTweet['visible'] = 0;

            // 处理location信息
            $location = trim($request->get('location'));

            // 将json格式解析成数组格式
            if($location) $location = json_decode($location,true);

            // 开启事务
            DB::beginTransaction();

            // 判断所传数据中是否有位置信息
            if($location){

                // 匹配数据库中是否已经存在该记录
                $location_able = Location::where('formattedAddress',$location['formattedAddress'])->get()->first();

                // 如果不存在，将信息存入location表中
                if(!$location_able) $location_able = Location::create($location);

                // 将位置信息存入tweet数组中
                $newTweet['location_id'] = $location_able -> id;
            }

            // 发布动态的手机系统
            if($phone_type = removeXSS($request->get('phone_type')) && $phone_os = removeXSS($request->get('phone_os'))) {

                $phone = TweetPhone::where('phone_type',$phone_type)->where('phone_os',$phone_os)->first();

                if(!$phone) {

                    $phone = TweetPhone::create([
                        'phone_type'    => $phone_type,
                        'phone_os'      => $phone_os,
                        'time_add'      => $time,
                        'time_update'   => $time,
                    ]);
                }

                $phone_id = $phone -> id;

                // 将手机信息存入tweet数组中
                $newTweet['phone_id'] = $phone_id;
            }

            //TODO 应该加判断，//后不做正则匹配
            // 将数据存入 tweet 表中
            $tweet = Tweet::create($newTweet);

            // 动态内容 zx_tweet_content 表
            TweetContent::create([
                'tweet_id' => $tweet -> id,
                'content'  => $content
            ]);

            // 判断是否参与了赛事
            if(isset($input['activity_id']) && is_numeric($input['activity_id']) && Activity::where('active',1)->findOrFail($input['activity_id'])){

                // 如果之前该用户参与过赛事，则将原赛事进行删除，再添加新的赛事信息
                TweetActivity::where('user_id',$id)->where('activity_id',$input['activity_id'])->delete();

                // 保存
                TweetActivity::create([
                    'user_id'   => $id,
                    'tweet_id'  => $tweet -> id,
                    'activity_id'=> $input['activity_id'],
                    'time_add'   => $time,
                    'time_update'   => $time,
                ]);
            }

            // 根据用户ID及话题数组数据，创建不存在的话题，并返回所有话题的ID
            $select_topics = $this->createNewTopic($id, $topics, $tweet);

            // 判断话题id是否为空
            if(!empty($select_topics)){

                // 绑定动态与话题
                $this->createTweetTopic($tweet, $select_topics);

                // 绑定用户与话题
                $this->createUserTopic($newTweet['user_id'], $select_topics);
            }

            $data = [];
            if (isset($tweet->video)) {
                $arr = explode('/',$tweet->video);
                $new_key = 'tweet/' . $tweet->id . '/' . $arr[sizeof($arr) - 1];
                $data[$tweet->video] = $new_key;
                $tweet->video = $new_key;
            }
            if (isset($tweet->photo)) {
                $photos = json_decode($tweet->photo,true);
                $result = [];
                foreach ($photos as $photo) {
                    $arr = explode('/',$photo);
                    $new_key = 'tweet/' . $tweet->id . '/' . $arr[sizeof($arr) - 1];
                    $data[$photo] = $new_key;
                    $result[] = $new_key;
                }
                $tweet->photo = json_encode($result);
            }

            // 将存在七牛云上的内容进行重命名
            CloudStorage::batchRename($data);
            $tweet->save();

            // 创建提醒
            if(!empty($at)){

                $this->createNotification($id, $at, $tweet);
            }

            # 更新 users 表中的作品总量数据

            // 转发 数量+1
            if($tweet->retweet){

                // 转发总量加1
                User::findOrfail($id) -> increment('retweet_count');

                // 更新被转发动态的 retweet 字段的值 加1
                Tweet::findOrFail($newTweet['retweet']) -> increment('retweet_count');

            // 原创
            }else{

                // 作品总量加1
                User::findOrfail($id) -> increment('work_count');
            }

            // 获取 subscription 表中 集合
            $subscription = Subscription::where('to',$id) -> get();

            // 遍历集合
            foreach ($subscription as $item) {

                // 将 subscription 表中 unread 批量 +1
                $item -> unread ++;

                // 保存
                $item -> save();
            }

            DB::commit();

            return response()->json($this->tweetsTransformer->transform($tweet),201);

        }catch(ModelNotFoundException $e){

            DB::rollback();
            return response()->json(['error'=>'bad_request'],400);
        }catch(\Exception $e){

            DB::rollback();
            return response()->json(['error'=>'bad_request'],400);
        }
    }

    /**
     * 用户个人中心的动态置顶
     * @param $id 用户id
     * @param $tweet_id 动态id
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function topAdd($id,$tweet_id)
    {
        try {

            // 查询该用户是否有该动态
            $tweet = Tweet::where('id',$tweet_id)->where('user_id',$id)->able()->first();

            // 判断动态是否存在，是否为公开，是否已经置顶,是否为转发
            if(!$tweet || $tweet->visible != 0 || $tweet->user_top == 1 || $tweet->origin) return response()->json(['error'=>'bad_request'],403);

            // 判断该用户置顶动态的数量
            $top = Tweet::where('user_id',$id)->able()->ofUserTop();

            if($top->count() >= 2) return response()->json(['error'=>'top_already_two'],401);

            // 置顶设置
            $tweet -> user_top = 1;
            $tweet -> updated_at = Carbon::now();

            // 保存
            $tweet -> save();

            // 返回
            return response()->json(['status'=>'OK'],201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 取消 用户个人中心的动态置顶
     * @param $id 用户id
     * @param $tweet_id 动态id
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function topDelete($id,$tweet_id)
    {
        try {

            // 查询该用户是否有该动态
            $tweet = Tweet::where('id',$tweet_id)->where('user_id',$id)->able()->first();

            // 判断动态是否存在，是否已经置顶
            if(!$tweet || $tweet->user_top == 0) return response()->json(['error'=>'bad_request'],403);

            // 置顶设置
            $tweet -> user_top = 0;
            $tweet -> updated_at = Carbon::now();

            // 保存
            $tweet -> save();

            // 返回
            return response()->json(['status'=>'OK'],201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 删除动态，只修改tweet表中状态，暂时保留评论及统计信息，其他部分表中数据删除
     * @Delete("users/{id}/tweets/{id}")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(404,body={"error":"tweet_not_found"}),
     * })
     *
     */
    public function destroy($id,$tweet_id)
    {
        try{
            // 获取相应动态
            if(!$tweet = Tweet::able()->find($tweet_id)) return response()->json(['error'=>'tweet_not_found'],404);

            // 开启事务
            DB::transaction(function () use($id, $tweet_id, $tweet) {

                DB::table('tweet_like')->where('tweet_id',$tweet_id)->delete();
//                DB::table('tweet_reply')->where('tweet_id',$tweet_id)->delete();

                // 删除 tweet_topic 表中数据
                TweetTopic::where('tweet_id',$tweet_id)->delete();

                // 删除 channel_tweet 表中数据
                ChannelTweet::where('tweet_id',$tweet_id)->delete();

//                CloudStorage::deleteDirectory('tweet/' . $tweet_id);

                //删除提醒
                Notification::where('type',0)->where('type_id',$id)->delete();

                $this->updateRetweetCount($tweet,'del');

                // 修改为删除状态
                $tweet->active = 4;

                // 保存
                $tweet->save();
            });
            return response('',204);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'tweet_not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'unknown'], 500);
        }
    }

    /**
     * 获取关注者的动态 20170308
     */
//    public function attentionIndex($id,Request $request)
//    {
//        try {
//            // 所取数据页数
//            if(!is_numeric($page = $request -> get('page',1)))
//                return response()->json(['error' => 'bad_request'],403);
//
//            // 获取所有关注的用户id
//            $subscriptions = Subscription::where('from',$id)->pluck('to');
//
//            // 获取所有好友的用户id
//            $friends = Friend::where('from',$id)->get(['to'])->pluck('to')->all();
//
//            // 获取动态
//            $tweets = Tweet::with(['hasOneContent','belongsToUser','hasManyTweetReply'=>function($q){
//                $q->with('belongsToUser')->status()->where('anonymity',0)->orderBy('like_count','DESC');
//            }])
//                ->ofAttention($subscriptions,$friends, $id)
//                ->orderBy('id','DESC')
//                ->able()
//                ->forPage($page,$this->paginate)
//                ->get();
//
//            // 获取所有被关注者的id
//            $subscription_to = $tweets -> pluck('user_id')->unique()->values()->all();
//
//            // 将 subscription 表中的unread字段设置为0
//            Subscription::where('from',$id)->whereIn('to',$subscription_to) -> update(['unread'=>0]);
//
//            // 统计总动态的数量
//            $count = $tweets->count();
//
//            // 返回数据
//            return response()->json([
//                'data'       => $count ? $this->attentionTweetsTransformer->transformCollection($tweets->all()) : [],
//                'count'      => $count
//            ]);
//        } catch (ModelNotFoundException $e) {
//            return response()->json(['error' => 'bad_request'],403);
//        } catch (\Exception $e) {
//            return response()->json(['error' => 'bad_request'],403);
//        }
//    }

    /**
     * 获取关注者的动态 20170908
     */
    public function attentionIndex($id,Request $request)
    {
        try {
            // 所取数据页数
            if(!is_numeric($page = $request -> get('page',1)))
                return response()->json(['error' => 'bad_request'],403);

            // 获取所有关注的用户id
            $subscriptions = Subscription::where('from',$id)->pluck('to');

            // 获取所有好友的用户id
            $friends = Friend::where('from',$id)->get(['to'])->pluck('to')->all();

            // 获取动态
            $tweets = Tweet::with(['hasOneContent','belongsToUser','hasManyTweetReply'=>function($q){
                $q->with('belongsToUser')->status()->where('anonymity',0)->orderBy('like_count','DESC');
            },'belongsToManyChannel' => function($q){
                $q -> select('name');
            }])
                ->ofAttention($subscriptions,$friends, $id)
                ->orderBy('id','DESC')
                ->able()
                ->forPage($page,$this->paginate)
                ->get();

            // 获取所有被关注者的id
            $subscription_to = $tweets -> pluck('user_id')->unique()->values()->all();

            // 将 subscription 表中的unread字段设置为0
            Subscription::where('from',$id)->whereIn('to',$subscription_to) -> update(['unread'=>0]);

            // 统计总动态的数量
            $count = $tweets->count();

            // 返回数据
            return response()->json([
                'data'       => $count ? $this->attentionTweetsTransformer->transformCollection($tweets->all()) : [],
                'count'      => $count
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'bad_request'],403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'bad_request'],403);
        }
    }

    /**
     * 获取订阅的动态
     *
     * @Get("users/{id}/subscriptions/tweets?{limit,timestamp}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("limit", description="每次返回最大条数",default=20),
     *      @Parameter("timestamp", description="每次起始时间点",default="当前时间")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={"data":{
     *                          {
     *                              "id":106,
     *                              "type":0,
     *                              "video":null,
     *                              "photo":{"url1","url2"},
     *                              "already_like":0,
     *                              "like_count":123,
     *                              "reply_count":456,
     *                              "reply":{},
     *                              "retweet_count":3,
     *                              "already_like":0,
     *                              "content":"content",
     *                              "location":null,
     *                              "screen_shot":"url",
     *                              "channel":{"id":1,"name":"channel","icon":"url"},
     *                              "user":{"id":10000,"nickname":"啊啊啊","avatar":null},
     *                              "original":null,
     *                              "retweet":null,
     *                              "visible":"格式为数值,没有引号,0为公开才可以转发",
     *                              "created_at":1464250271
     *                          },
     *                          {
     *                              "id":107,
     *                              "type":0,
     *                              "video":"url",
     *                              "photo":{},
     *                              "like_count":123,
     *                              "reply_count":456,
     *                              "reply":{
     *                                           {
     *                                               "id": 25,
     *                                               "user_id": 10001,
     *                                               "tweet_id": 1,
     *                                               "reply_id": null,
     *                                               "content": "测试@test#新话题#",
     *                                               "created_at": 1464864566
     *                                           },
     *                                      },
     *                              "retweet_count":3,
     *                              "already_like":1,
     *                              "content":"content",
     *                              "location":null,
     *                              "screen_shot":"url",
     *                              "channel":{"id":1,"name":"channel","icon":"url"},
     *                              "user":{"id":10000,"nickname":"啊啊啊","avatar":null},
     *                              "original":"上面tweet的数据格式,为转发根节点的tweet数据",
     *                              "retweet":106,
     *                              "visible":"格式为数值,没有引号,0为公开才可以转发",
     *                              "created_at":1464250271
     *                          }},
     *                          "timestamp":123456,
     *                          "count":20,
     *                          "link":"url"
     *     }),
     *     @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function subscriptionIndex($id,Request $request)
    {
        // 格式化时间与条数
        list($date, $limit) = $this->transformerTimeAndLimit($request);

        // 获取所有关注的用户id
        $subscriptions = Subscription::where('from',$id)->get(['to'])->pluck('to')->all();

        // 获取所有好友的用户id
        $friends = Friend::where('from',$id)->get(['to'])->pluck('to')->all();

        // 获取动态
        $tweets = Tweet::with('hasOneContent','belongsToUser')
            ->ofSubscriptions($subscriptions,$friends, $id, $date)
            ->orderBy('created_at','desc')
            ->able()
            ->take($limit)
            ->get();

        // 获取所有所取数据的用户及话题的id，及subscription 表中的to字段的值
        $subscription_to = $tweets -> pluck('user_id')->unique()->values()->all();

        // 将 subscription 表中的unread字段设置为0
        Subscription::where('from',$id)->whereIn('to',$subscription_to) -> update(['unread'=>0]);

        // 统计总动态的数量
        $count = $tweets->count();

        // 返回数据
        return [
            'data'       => $count ? $this->channelTweetsTransformer->transformCollection($tweets->all()) : [],
            'timestamp'  => $count ? (int)strtotime($tweets->last()->created_at) : null,
            'count'      => $count,
            'link'       => $count
                ? $request->url() .
                '?channel=subscription&limit=' . $limit .
                '&timestamp=' . strtotime($tweets->last()->created_at)
                : null
        ];
    }

    // 旧版订阅信息，返回数据量太大，需做处理，暂时停用
    public function subscriptionIndexOld($id,Request $request)
    {
        // 格式化时间与条数
        list($date, $limit) = $this->transformerTimeAndLimit($request);

        // 获取所有关注的用户id
        $subscriptions = Subscription::where('from',$id)->get(['to'])->pluck('to')->all();

        // 获取所有好友的用户id
        $friends = Friend::where('from',$id)->get(['to'])->pluck('to')->all();

        // 获取动态
        $tweets = Tweet::with([
            'belongsToUser',
            'hasOneOriginal.belongsToManyChannel'=> function ($query) {
                $query->where('active',1);
            },
            'belongsToManyChannel' => function ($query) {
                $query->where('active',1);
            },
            'hasManyTweetReply' => function ($query) {
                $query->orderBy('created_at','desc')
                        ->take(3);
            }])
            ->ofSubscriptions($subscriptions,$friends, $id, $date)
            ->take($limit)
            ->able()
            ->orderBy('created_at','desc')
            ->get();
        $count = $tweets->count();
        return [
            'data'       => $count ? $this->tweetsTransformer->transformCollection($tweets->all()) : [],
            'timestamp'  => $count ? (int)strtotime($tweets->last()->created_at) : null,
            'count'      => $count,
            'link'       => $count
                ? $request->url() .
                '?channel=subscription&limit=' . $limit .
                '&timestamp=' . strtotime($tweets->last()->created_at)
                : null
        ];
    }

    // 频道动态  TODO 待进一步优化 广告如果不再使用需要删除  老版本，最新版为下面的 channelNewTweets ，可能还会改
    public function channelTweets($id,Request $request)
    {
        try {
            $page = (int)$request -> get('page', 1);

            // 通过下面arrayIntersect函数将channel数据添加到$data尾部
            $data = new Collection();

            // 用来接收channel的id
            $arr = [];

            // 初始化
            $topics = [];
            $top_data = [];
            $blacklist = [];

            // 第一次请求才有置顶、推荐动态、话题、广告位
            if (1 == $page) {

                //TODO 获取置顶及推荐的动态
                $top_tweets = $this -> tweetFilter($id, 'top');

                // 取2条置顶
                if ($top_tweets->count() > 2)
                    $top_tweets = $top_tweets->random(2);

                // 获取推荐
                $recommend_tweets = $this -> tweetFilter($id, 'recommend');

                // 推荐大于4条，从集合中随机取出四个
                if ($recommend_tweets->count() > 4)
                    $recommend_tweets = $recommend_tweets->random(4);

                // 置顶动态的处理
                $this->arrayIntersect($arr, $data, $top_tweets);

                // 推荐动态的处理
                $this->arrayIntersect($arr, $data, $recommend_tweets);

                // 将置顶动态和推荐动态过滤
                $top_data = $this->channelTweetsTransformer->transformCollection($data->all());

                // 获取话题集合
                $topics = Topic::with(['hasManyTopicUser'=>function($query){
                    $query -> with(['belongsToUser' => function($query){
                        $query -> select(['id','avatar']);
                    }]) -> select(['user_id','topic_id']);
                }])
                    -> recommend()
                    -> orderBy('users_count','desc')
                    -> take(3)
                    -> get(['id','name','size','icon','users_count'])
                    -> all();

                // 获取广告位    TODO 广告
//                $ads = AdvertisingRotation::where('channel_id',$id)->recommend()->take(1)->get();

                // 判断是否为空
//                if(!$ads->count()) $ads = AdvertisingRotation::recommend()->where('channel_id','<>',1)->where('channel_id','<>',2)->take(1)->get();
            }

            // 判断用户是否为登录状态
            if($user = Auth::guard('api')->user()) {

                // 黑名单
                $blacklist = Blacklist::where('from',$user->id)->pluck('to') -> all();
            }

            // 获取普通动态,判断是否有黑名单
            if(empty($blacklist)) {
                $tweets = $this -> tweetFilter($id, 'ordinary', [], $page, $this -> paginate);
            }else{
                $tweets = $this -> tweetFilter($id, 'ordinary', $blacklist, $page, $this -> paginate);
            }

            // 普通动态的过滤
            $tweets_data = $this->channelTweetsTransformer->transformCollection($tweets->all());

            // 如果话题集合存在，则与普通动态合并
            if(!empty($topics)) {

                $topics_data = $this->topicsTransformer->transformCollection($topics);

                // 合并
                $tweets_data = array_merge($topics_data,$tweets_data);

                // 自定义全局函数 打乱数组
                array_quake($tweets_data);
            }

            // 合并总数据
            if(count($top_data)) $tweets_data = array_merge($top_data,$tweets_data);

            // 如果data为空，返回
            if(!count($tweets_data)) return response()->json(['data'=>null,'count'=>0,'link'=>null],204);

            // 遍历集合
            $data->each(function($tweet) {

                // 判断是否为图片
                if($tweet -> type == 1){

                    // 将json格式字符串转为变量模式
                    foreach(json_decode($tweet -> photo) as $value){

                        $info[] = CloudStorage::downloadUrl($value);
                    }

                    // 将一维数组转为集合形式
                    $tweet -> photo = collect($info);
                }
            });

            return response()->json([

                // 数据
                'data' => $tweets_data,

                // 广告位  
//                'ads'  => count($ads) ? $this->zxHomeImagesTransformer->transformCollection($ads->all()) : [],
                'ads'  => [],

                // 总数量
                'count' => count($tweets_data),

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link' => count($tweets)
                    ? $request->url() .
                    '?page=' . ++$page
                    : null      // 如果数量为0，则不附带搜索条件
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 获取某一频道下的动态  热门和最新 20170923版，不知道还会怎么改
     *
     * @param int $id 话题id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function channelNewTweets($id,Request $request)
    {
        try {

            $page = (int)$request -> get('page', 1);

            // 获取所取动态的类型,0为最新，1为热门
            $type = 1 == $request -> get('type',0) ? 1 : 0;

            // 排序的方式
            $field_order = 1 == $type ? 'like_count' : 'id';

            // 查询动态数据
            $tweets= Tweet::with(['hasOneContent','belongsToUser'])
                -> able()
                -> whereHas('hasManyChannelTweet',function($query)use($id){
                    $query->where('channel_id',$id);
                })
                -> orderBy($field_order, 'DESC')
                -> get();

            // 返回数据
            return response()->json([

                // 数据
                'data' => $this->channelTweetsTransformer -> transformCollection($tweets-> forPage($page, $this -> paginate)->all()),

                // 总页码
                'page_count' => ceil($tweets -> count()/$this -> paginate),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 获取热门的动态  更换新表 新版 20170207 增加缓存
     */
//    public function popularIndex(Request $request)
//    {
//        try {
//
//            // 获取最后一个id
//            $last_id = $request -> get('last');
//
//            // 判断是否为数字
//            if($last_id){
//
//                if(!is_numeric($last_id)) return response()->json(['error'=>'bad_request'],403);
//            }
//
//            // 判断缓存数据 取出热门动态集合
//            if(Cache::has('SET:TWEET:HOT')){
//
//                $hot_tweets = Cache::get('SET:TWEET:HOT');
//            }else{
//
//                $hot_tweets = TweetHot::orderBy('id','desc')
//                    -> take($this->paginate)
//                    -> get();
//
//                // 保存至缓存中
//                Cache::put('SET:TWEET:HOT',$hot_tweets,30);
//            }
//
//            // 如果非第一次请求
//            if($last_id) $hot_tweets = $hot_tweets -> where('id','<',$last_id);
//
//            // 获取id
//            $tweets_ids = $hot_tweets -> pluck('tweet_id');
//
//            // 遍历
//            foreach($tweets_ids as $key=>$value){
//
//                // 判断缓存是否存在 SET 无序集合
//                if(Cache::has('SET:TWEET:INFO:'.$value)){
//
//                    $tweets[$key] = Cache::get('SET:TWEET:INFO:'.$value);
//                }else{
//
//                    $tweets[$key] = Tweet::with('hasOneContent','belongsToUser')->find($value);
//
//                        // 保存至缓存中
//                    Cache::put('SET:TWEET:INFO:'.$value,$tweets[$key],30);
//                }
//            }
//
//            $tweets = collect($tweets);
//
//                // 获取相关热门动态的数据
////            $tweets = Tweet::whereIn('id',$tweets_ids)->with('hasOneContent','belongsToUser')->get();
//
//            // 过滤
//            $tweets_data = $this->channelTweetsTransformer->transformCollection($tweets->all());
//
//            // 如果为第一次请求
//            if (!$last_id) {
//
//                //TODO 获取置顶及推荐的动态
//                // 获取置顶动态
////                $top_tweets = TweetHot::top()->get();
//
//                // 判断缓存数据 取出热门置顶动态集合
//                if(Cache::has('SET:TWEET:HOT-TOP')){
//
//                    $top_tweets = Cache::get('SET:TWEET:HOT-TOP');
//                }else{
//
//                    $top_tweets = TweetHot::top()->get();
//
//                    // 保存至缓存中
//                    Cache::put('SET:TWEET:HOT-TOP',$top_tweets,30);
//                }
//
//                // 如果置顶动态id多于两条，随机取两条置顶的动态
//                if ($top_tweets->count() > 2){
//                    $top_tweets = $top_tweets->random(2);
//                }
//
//                // 获取推荐动态
////                $recommend_tweets = TweetHot::recommend()->where('top_expires', '<=', getTime())->get();
//
//                // 判断缓存数据 取出热门置顶动态集合
//                if(Cache::has('SET:TWEET:HOT-RECOMMEND')){
//
//                    $recommend_tweets = Cache::get('SET:TWEET:HOT-RECOMMEND');
//                }else{
//
//                    $recommend_tweets = TweetHot::recommend()->where('top_expires', '<=', getTime())->get();
//
//                    // 保存至缓存中
//                    Cache::put('SET:TWEET:HOT-RECOMMEND',$recommend_tweets,30);
//                }
//
//                // 如果推荐动态id多于两条，随机取两条置顶的动态
//                if ($recommend_tweets->count() > 4){
//                    $recommend_tweets = $recommend_tweets->random(4);
//                }
//
//                // 合并置顶和推荐的数组
//                $special_ids = array_merge($top_tweets->pluck('tweet_id')->all(),$recommend_tweets->pluck('tweet_id')->all());
//
//                // 初始化
//                $special_data = [];
//
//                // 判断是否存在
//                if(count($special_ids)){
//
//                    // 获取相关热门动态的数据
////                    $special_tweets = Tweet::whereIn('id',$special_ids)->active()->get();
//
//                    foreach($special_ids as $key=>$value){
//
//                        // 判断缓存是否存在 SET 无序集合
//                        if(Cache::has('SET:TWEET:INFO:'.$value)){
//
//                            $special_tweets[$key] = Cache::get('SET:TWEET:INFO:'.$value);
//                        }else{
//
//                            $special_tweets[$key] = Tweet::with('hasOneContent','belongsToUser')->active()->find($value);
//
//                            // 保存至缓存中
//                            Cache::put('SET:TWEET:INFO:'.$value,$special_tweets[$key],30);
//                        }
//                    }
//
//                    $special_tweets = collect($special_tweets);
//
//                    // 过滤
//                    $special_data = $this->channelTweetsTransformer->transformCollection($special_tweets->all());
//                }
//
//                // 获取话题集合
////                $topics = Topic::official()->active()->orderBy('users_count','desc')->take(3)->get();
//
//                // 判断缓存数据 取出官方话题集合
//                if(Cache::has('SET:TOPIC:OFFICIAL')){
//
//                    $topics = Cache::get('SET:TOPIC:OFFICIAL');
//                }else{
//
//                    $topics = Topic::official()
//                                ->active()
//                                ->orderBy('users_count','desc')
//                                ->get();
//
//                    // 保存至缓存中
//                    Cache::put('SET:TOPIC:OFFICIAL',$topics,30);
//                }
//
//                $topics = $topics->take(3);
//
//                // 如果话题集合存在，则与普通动态合并
//                if($topics->count()) {
//
//                    $topics_data = $this->topicsTransformer->transformCollection($topics->all());
//
//                    // 合并
//                    $tweets_data = array_merge($topics_data,$tweets_data);
//
//                    // 自定义全局函数 打乱二维数组
//                    array_quake($tweets_data);
//                }
//
//                // 合并总数据
//                $tweets_data = array_merge($special_data,$tweets_data);
//
//                // 获取广告位
////                $ads = AdvertisingRotation::where('channel_id',1)->recommend()->take(5)->get();
//
//                // 判断缓存数据 取出官方话题集合
//                if(Cache::has('SET:CHANNEL-ADS')){
//
//                    $ads = Cache::get('SET:CHANNEL-ADS');
//                }else{
//
//                    $ads = AdvertisingRotation::recommend()->get();
//
//                    // 保存至缓存中
//                    Cache::put('SET:CHANNEL-ADS',$ads,30);
//                }
//
//                $ads = $ads->where('channel_id',1)->take(5);
//
//                // 判断是否为空
////                if(!$ads->count()) $ads = AdvertisingRotation::recommend()->get()->random(5)->values();
//            }
//
//            // 返回数据
//            return response()->json([
//
//                // 数据
//                'data' => $tweets_data,
//
//                // 广告位
//                'ads'  => count($ads) ? $this->zxHomeImagesTransformer->transformCollection($ads->all()) : [],
//
//                // 总数量
//                'count' => count($tweets),
//
//                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
//                'link' => count($tweets)
//                    ? $request->url() .
//                    '?last='.($hot_tweets -> last() -> id)
//                    : null      // 如果数量为0，则不附带搜索条件
//            ]);
//
//        } catch (\Exception $e) {
//            return response()->json(['error' => $e->getMessage()], $e->getCode());
//        }
//    }

    /**
     * 获取热门的动态  更换新表 新版 20170206
     */
//    public function popularIndex(Request $request)
//    {
//        try {
//
//            $page = (int)$request->get('page',1);
//
////            $ads = [];
//
//            $tweets = Tweet::selectListPageByWithAndWhereAndhas(
//                [['hasOneContent',['content','tweet_id']],['belongsToUser',['id','nickname','avatar','cover','verify','signature','verify_info']]],
//                ['hasOneHot'],
//                [['active', 1]],
//                [],
//                [],
//                [$page,$this->paginate],
//                ['id','type','screen_shot','duration','photo','shot_width_height','user_id']);
//
//            // 过滤
//            $tweets_data = $this->channelTweetsTransformer->transformCollection($tweets->all());
//
//            // 如果为第一次请求
//            if (1 === $page) {
//
//                //TODO 获取置顶及推荐的动态
//                // 获取置顶动态
//                $top_tweets = TweetHot::top()->pluck('tweet_id');
//
//                // 如果置顶动态id多于2条，随机取2条置顶的动态
//                if ($top_tweets->count() > 2)
//                    $top_tweets = $top_tweets->random(2);
//
//                // 获取推荐动态
//                $recommend_tweets = TweetHot::recommend()->whereNotIn('tweet_id', $top_tweets->all())->pluck('tweet_id');
//
//                // 如果推荐动态id多于4条，随机取4条置顶的动态
//                if ($recommend_tweets->count() > 4)
//                    $recommend_tweets = $recommend_tweets->random(4);
//
//                // 合并置顶和推荐的数组
//                $special_ids = array_merge($top_tweets->all(),$recommend_tweets->all());
//
//                // 初始化
//                $special_data = [];
//
//                // 判断是否存在
//                if(isset($special_ids[0])) {
//
//                    // 获取相关热门动态的数据
//                    $special_tweets = Tweet::whereIn('id',$special_ids)
//                        -> active()
//                        -> get(['id','type','screen_shot','photo','shot_width_height','user_id']);
//
//                    // 过滤
//                    $special_data = $this->channelTweetsTransformer->transformCollection($special_tweets->all());
//                }
//
//                // 获取话题集合
//                $topics = Topic::with(['hasManyTopicUser'=>function($query){
//                    $query -> with(['belongsToUser' => function($query){
//                        $query -> select(['id','avatar']);
//                    }]) -> select(['user_id','topic_id']);
//                }])
//                    -> official()
//                    -> active()
//                    -> orderBy('users_count','desc')
//                    -> take(3)
//                    -> get(['id','name','size','icon','users_count']);
//
//                // 如果话题集合存在，则与普通动态合并
//                if($topics->first()) {
//
//                    $topics_data = $this->topicsTransformer->transformCollection($topics->all());
//
//                    // 合并
//                    $tweets_data = array_merge($topics_data,$tweets_data);
//
//                    // 自定义全局函数 打乱二维数组
//                    array_quake($tweets_data);
//                }
//
//                // 合并总数据
//                $tweets_data = array_merge($special_data,$tweets_data);
//
//                // 获取广告位
////                $ads = AdvertisingRotation::recommend()->take(5)->get()->all();
//
//                // 为空,随机取几个
////                if(!count($ads)) $ads = AdvertisingRotation::recommend()->get()->random(5)->values()->all();
//            }
//
//            // 返回数据
//            return response()->json([
//
//                // 数据
//                'data' => $tweets_data,
//
//                // 广告位
//                'ads'  => [],
//
//                // 总数量
//                'count' => $tweets->count(),
//
//                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
//                'link' => $tweets->count()
//                    ? $request->url() .
//                    '?page='.(++$page)
//                    : null      // 如果数量为0，则不附带搜索条件
//            ]);
//
//        } catch (ModelNotFoundException $e) {
//            return response()->json(['error' => 'not_found'], 404);
//        } catch (\Exception $e) {
//            return response()->json(['error' => 'not_found'], 404);
//        }
//    }

    /**
     * 获取热门的动态  更换新表 新版 20170908  只有动态 第四次修改
     */
    public function popularIndex(Request $request)
    {
        try {

            $page = (int)$request->get('page',1);

            $time = getTime();

            // 随机生成选择样式
            $rand = array_rand([1, 2, 3]);

            $ads = [];
            $templates = [];
            $activity = [];

            if(0 == $rand){

                // 广告 TODO 待修改为根据用户爱好推送
                $ads = AdvertisingRotation::with('belongsToUser')
                    -> where('from_time','<',$time)
                    -> where('end_time','>',$time)
                    -> active()
                    -> get();

                // 广告
                if($ads -> count()) {
                    $ads = $ads -> random(1);
                    $ads = $this -> adsDiscoverTransformer -> transformCollection($ads->all());
                }

            } elseif(1 == $rand) {

                // 模板
                $templates = MakeTemplateFile::with('belongsToUser')
                    -> where('recommend', 1)
                    -> active()
                    -> where('status', 1)
                    -> orderBy('sort')
                    -> get(['id', 'user_id', 'name', 'intro', 'cover', 'preview_address', 'count', 'time_add']);

                if($templates -> count()) {
                    $templates = $templates -> random(1);
                    $templates = $this -> templateDiscoverTransformer -> transformCollection($templates->all());
                }

            } else {

                // 竞赛
                $activity = Activity::with(['belongsToUser', 'hasManyTweets'])
                    -> ofExpires()
                    -> recommend()
                    -> get(['id', 'user_id', 'comment', 'location', 'icon', 'recommend_expires', 'time_add']);

                if($activity -> count()) {

                    $activity = $activity -> random(1);

                    $activity = $this -> activityDiscoverTransformer -> transformCollection($activity->all());
                }
            }

            $tweets = Tweet::whereType(0) -> with(['belongsToManyChannel' => function($q){
                $q -> select('name');
            }]) -> selectListPageByWithAndWhereAndhas(
                [['hasOneContent',['content','tweet_id']],['belongsToUser',['id','nickname','avatar','cover','verify','signature','verify_info']]],
                ['hasOneHot'],
                [['active', 1]],
                [],
                [],
                [$page,$this->paginate]);

            // 过滤
            $tweets_data = $this->channelTweetsTransformer->transformCollection($tweets->all());

            // 如果为第一次请求
            if (1 === $page) {

                //TODO 获取置顶及推荐的动态
                // 获取置顶动态
                $top_tweets = TweetHot::top()->pluck('tweet_id');

                // 如果置顶动态id多于2条，随机取2条置顶的动态
                if ($top_tweets->count() > 2)
                    $top_tweets = $top_tweets->random(2);

                // 获取推荐动态
                $recommend_tweets = TweetHot::recommend()->whereNotIn('tweet_id', $top_tweets->all())->pluck('tweet_id');

                // 如果推荐动态id多于4条，随机取4条置顶的动态
                if ($recommend_tweets->count() > 4)
                    $recommend_tweets = $recommend_tweets->random(4);

                // 合并置顶和推荐的数组
                $special_ids = array_merge($top_tweets->all(),$recommend_tweets->all());

                // 初始化
                $special_data = [];

                // 判断是否存在
                if(isset($special_ids[0])) {

                    // 获取相关热门动态的数据
                    $special_tweets = Tweet::whereType(0)
                        -> whereIn('id',$special_ids)
                        -> active()
                        -> get(['id','type','screen_shot','location','photo','video','shot_width_height','user_id']);

                    // 过滤
                    $special_data = $this->channelTweetsTransformer->transformCollection($special_tweets->all());
                }

                // 合并总数据
                $tweets_data = array_merge($special_data,$tweets_data);
            }

            // 返回数据
            return response()->json([

                // 数据
                'data' => $tweets_data,

                // 广告位
                'ads'  => $ads,

                // 模板
                'templates'  => $templates,

                // 赛事
                'activity'  => $activity,

                // 总页码
                'page_count' => ceil($tweets->count()/$this->paginate)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 模糊搜索动态
     *
     * @Get("tweets/search/?{name,limit,timestamp}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("name", required=true, description="要搜索的名称")
     * })
     * @Transaction({
     *     @Response(201,body={{"id":18,"type":"topic","name":"aa","screen_shot":null,"comment":null,"updated_at":1464854994},{"id":19,"type":"topic","name":"aaaa","screen_shot":null,"comment":null,"updated_at":1464856166}}),
     *     @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function search(Request $request)
    {
        try{

            $page = (int)$request -> get('page', 1);

            // 搜索关键词
            if (!$name = removeXSS($request->get('name'))) {
                throw new \Exception('bad_request',400);
            }

            //  判断关键词表中有无该关键词
            $keyword = Keywords::where('keyword','LIKE BINARY', '%' . $name . '%')->first();
//            dd($keyword->count());
//            die($name);
            //  如果没有该关键词则加入过渡表
//            die($keyword);
            if(!$keyword)
            {
                //  判断过渡表中有无该词
                $filterKeyword = Word_filter::where('keyword',$name)->first();
//                dd($filterKeyword);
                //  如果没有则加入
                if(!$filterKeyword){
                    $newfilterword = new Word_filter;
                    $newfilterword->keyword = $name;
                    $newfilterword->count_sum = 1;
                    $newfilterword->create_at = time();
                    $newfilterword->update_at = time();

                    $newfilterword->save();
//                    DB::table('word_filter')->insert([
//                        'word' => $name,
//                        'count_sum' => 1,
//                        'create_at' => time(),
//                        'update_at' => time()
//                    ]);


                }else{
                //  如果有就加一次搜索次数
                    $filterKeyword->count_sum = ++$filterKeyword->count_sum;
                    $filterKeyword->count_day = ++$filterKeyword->count_day;
                    $filterKeyword->count_week = ++$filterKeyword->count_week;
                    $filterKeyword->update_at = time();
                    $filterKeyword->save();
                }

            }else{

            //  如果有就更新每日、每周以及总的搜索数
                $keyword->count_sum = ++$keyword->count_sum;
                $keyword->count_day = ++$keyword->count_day;
                $keyword->count_week = ++$keyword->count_week;
                $keyword->update_at = time();
//                dd($keyword->count_sum);
                $keyword->save();
            }

            $hotsearchword = HotSearch::where('hot_word',$name)->first();
//            dd($hotsearchword);
            if($hotsearchword)
            {
               $hotsearchword->sort  = ++$hotsearchword->sort;
               $hotsearchword->time_update = time();
               $hotsearchword->save();
            }

            // 验证格式
//            list($timestamp, $limit) = $this->transformerTimeAndLimit($request);

            // 调成为一次15条数据
//            $limit = 15;

            // 是否有精确完全相等的数据
            $name_tweets = Tweet::whereHas('hasOneContent',function($q)use($name){
                $q->ofName($name);
            }) -> with('hasOneContent')
               -> able()
               -> visible()
               -> forPage($page, $this->paginate)
               -> orderBy('id','desc')
               -> get();

            if($name_tweets->count() < $this -> paginate) {

                // 获取相似数据集合
                $tweets = Tweet::whereHas('hasOneContent',function($q)use($name){
                    $q->ofSearch($name);
                })  -> with('hasOneContent')
                    -> able()
                    -> visible()
                    -> forPage($page, $this->paginate)
                    -> orderBy('id','desc')
                    -> get();
            }

            // 如果能精确匹配成功数据，将数据添加至总数据集合中
            if ($name_tweets !== null) {

                $name_tweets->each(function($name_tweet)use($tweets){

                    $tweets->prepend($name_tweet);
                });
            }

//            // 如果能精确匹配成功数据，将数据添加至总数据集合中
//            if ($name_tweets !== null) {
//
//
//
//                $name_tweets->each(function($name_tweet)use($tweets){
//
//                    $tweets->prepend($name_tweet);
//                });
//
//
//
//
//            }

            // 判断用户登录状态
            if($user = Auth::guard('api')->user()){

                // 判断该用户是否已经进行了搜索
                $user_search = UserSearchLog::where('user_id',$user->id)->where('search',$name)->first();

                // 如果没有该用户的搜索记录，将保存搜索记录
                if(!$user_search){

                    // 将搜索词语存入 zx_user_search_log 表中
                    UserSearchLog::create([
                        'user_id'   => $user->id,
                        'search'    => $name,
                        'time_add'  => getTime()
                    ]);
                }
            }else{

                // 将搜索词语存入 zx_user_search_log 表中
                UserSearchLog::create([
                    'search'    => $name,
                    'time_add'  => getTime()
                ]);
            }

            // 返回数据
            return response()->json([

                // 数据
                'data' => count($tweets) ? $this->tweetsSearchTransformer->transformCollection($tweets->all()) : null,

                // 总数量
                'count' => count($tweets),

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link' => count($tweets)
                    ? $request->url() .
                    '?name=' . $name .
                    '&page=' . ++$page
                    : null      // 如果数量为0，则不附带搜索条件
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    // 遍历将多个可能为空的数组合并为一个数组
    public function getMerge(&$array,$data){
        // 遍历存值
        if($data){
            foreach($data['data'] as $value){
                $array[] = $value;
            }
        }
    }

    /**
     * 获取某一话题的动态 旧版
     *
     * @Get("topics/tweets?{mode,id,limit,timestamp}")
     * @Versions({"V1"})
     * @Parameters({
     *      @Parameter("mode",required=true,description="1代表刷新，2代表加载"),
     *      @Parameter("id",required=true,description="话题id")
     * })
     * @Transaction({
     *      @Response(201,body={{""}})
     *      @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function topics(Request $request){

        try{

            // 获取所取数据的方式，mode为1代表刷新，2代表加载
            $mode = $request->get('mode',2);

            // 获取话题id
            $topic_id = $request->get('id');

            // 判断是否为空
            if(null === $mode || null ===$topic_id){

                // 返回错误
                throw new \Exception('bad_request',400);
            }

            // 获取限制条件
            $limit = $request -> input('limit',20);

            // 获取所有时间戳
            $video_timestamp = $request->input('video_timestamp');
            $photo_timestamp = $request->input('photo_timestamp');

            // 判断时间戳是否都为空
            if(!$video_timestamp && !$photo_timestamp){

                $video_timestamp = $photo_timestamp = time();
            }

            // 初始化
            $tweets_photo = null;
            $tweets_video = null;

            // 获取动态数据 photo 1
            if($photo_timestamp) $tweets_photo = $this -> getTweet($mode,1,3,$photo_timestamp,$topic_id);

            // 获取动态数据 video 0
            if($video_timestamp) $tweets_video = $this -> getTweet($mode,0,$limit-$tweets_photo['count'],$video_timestamp,$topic_id);

            // 如果数据都为空，返回空
            if(!$tweets_photo && !$tweets_video) {

                // 判断是否为刷新,加载返回204，刷新返回201
                if($mode == 1){

                    // 刷新返回数据
                    return response()->json([
                        'data'=>null,
                        'count'=>0,
                        'link'=>
                            $request->url() .
                            '?limit=' . $limit .
                            '&mode=' . $mode .
                            '&id=' . $topic_id .
                            '&photo_timestamp='.$photo_timestamp.
                            '&video_timestamp='.$video_timestamp
                    ],201);

                }else{

                    // 返回204 无内容
                    return response()->json(['data'=>null,'count'=>0,'link'=>null],204);
                }
            }

            // 定义新数组，接收合并后的数据
            $data = [];

            // 调用自定义函数，合并集合
            $this -> getMerge($data,$tweets_photo);
            $this -> getMerge($data,$tweets_video);

            // 获取最新时间戳
            $time_photo = $tweets_photo['timestamp'];
            $time_video = $tweets_video['timestamp'];

            // 判断所取总条数，是否达到 $limit 未达到要求再获取动态里的photo
            if($tweets_photo['count']+$tweets_video['count']<$limit){

                $tweets_photo_new = $this -> getTweetData($mode,1,$limit-$tweets_photo['count']-$tweets_video['count'],$time_photo);

                if($tweets_photo_new['count']){

                    $this -> getMerge($data,$tweets_photo_new);

                    // 更新动态中photo的最新时间戳
                    $time_photo = $tweets_photo_new['timestamp'];
                }
            }

            // 如果为刷新，有一个时间戳为空则将返回时间戳设定为请求中的时间戳
            if($mode == 1){
                $time_photo = $time_photo ? $time_photo : $photo_timestamp;
                $time_video = $time_video ? $time_video : $video_timestamp;
            }

            // 调用自定义全局函数，将数组打乱
            array_quake($data);

            return response()->json([
                // 数据
                'data' => $data,

                // 总数量
                'count' => count($data),

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link' => count($data)
                    ? $request->url() .
                    '?limit=' . $limit .
                    '&mode=' . $mode .
                    '&id=' . $topic_id .
                    '&photo_timestamp='.$time_photo.'&video_timestamp='.$time_video
                    : null      // 如果数量为0，则不附带搜索条件
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 关注人中是否有未读动态
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function focusTweet($id)
    {
        $unread = Subscription::where('from',$id) -> where('unread', '<>', 0) -> count();

        return response() -> json(['status' => $unread ? 1 : 0], 200);
    }

    /**
     * 获取某一话题下的动态  热门和最新
     *
     * @param int $id 话题id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topicTweets($id,Request $request)
    {
        try {

            $page = (int)$request -> get('page', 1);

            // 获取所取动态的类型,0为最新，1为热门
            $type = 1 == $request -> get('type',0) ? 1 : 0;

            // 排序的方式
            $field_order = 1 == $type ? 'like_count' : 'id';

            // 查询动态数据
            $tweets= Tweet::with(['hasOneContent','belongsToUser'])
                -> able()
                -> whereHas('belongsToManyTopic',function($query)use($id){
                    $query->where('topic_id',$id);
                })
                -> orderBy($field_order, 'DESC')
                -> get();

            $topic_top = '';

            // 获取置顶的动态
            if(1 == $page)
                $topic_top = Tweet::whereHas('belongsToTopicTop', function($q)use($id){
                    $q -> where('topic_id',$id);
                })
                    -> first();

            // 返回数据
            return response()->json([

                // 数据
                'top' => $topic_top ? $this->channelTweetsTransformer -> transform($topic_top) : '',

                // 数据
                'data' => $this->channelTweetsTransformer -> transformCollection($tweets-> forPage($page, $this -> paginate)->all()),

                // 总页码
                'page_count' => ceil($tweets -> count()/$this -> paginate),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 根据位置锁定动态
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function locationTweets(Request $request)
    {
        try {

            // 位置信息
            if(!$location = $request -> get('location'))
                return response()->json(['error' => 'bad_request'], 403);

            $page = (int)$request -> get('page', 1);

            // 获取所取动态的类型,0为最新，1为热门
            $type = 1 == $request -> get('type',0) ? 1 : 0;

            // 排序的方式
            $field_order = 1 == $type ? 'like_count' : 'id';

            // 查询动态数据
            $tweets= Tweet::with('hasOneContent','belongsToUser')
                -> able()
                -> where('location', $location)
                -> orderBy($field_order, 'DESC')
                -> get();

            $users_count = 0;

            if($tweets -> count()) {

                $users_count = count(array_unique($tweets -> pluck('user_id') -> all()));
            }

            // 返回数据
            return response()->json([

                // 数据
                'data' => $this->channelTweetsTransformer -> transformCollection($tweets-> forPage($page, $this -> paginate)->all()),

                // 参与人数
                'users_count'   => $users_count,

                // 总页码
                'page_count' => ceil($tweets -> count()/$this -> paginate),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 用户点赞的动态
     * @param $id   用户id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postLikeTweets($id,Request $request)
    {
        try{
            // 获取动态数据
            $tweets = TweetLike::with('belongsToManyTweet.belongsToUser','belongsToManyTweet.hasOneContent')
                -> where('user_id',$id)
                -> orderBy('id','DESC')
                -> forPage((int)$request->get('page',1),$this->paginate)
                -> get();

            return response()->json([
                'data'  => $this -> tweetsLikeTransformer -> transformCollection($tweets->all()),
                'count' => $this -> paginate
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 404], 'not_found');
        } catch (\Exception $e) {
            return response()->json(['error' => 404], 'not_found');
        }
    }

    /**
     * 获取某一赛事下的动态  排行榜和全部
     *
     */
//    public function competitionTweets($id,Request $request)
//    {
//        try {
//            // 获取所取动态的类型,0为全部，1为排行榜,2为自己参加
//            $type = $request -> get('type',0);
//
//            // 如果为排行榜，一次取全部50条数据
//            if (1 == $type) {
//
//                $tweets = TweetActivity::with('hasOneTweet','hasOneUser')
//                        -> where('activity_id',$id)
//                        -> orderBy('like_count','desc')
//                        -> take(50)
//                        -> get();
//
//                $count = $tweets -> count();
//
//                # 获取每个动态的奖金
//                $account = new GoldTransactionService;
//
//                $tweet_bonus = $account -> bonusAllocation($count,Activity::findOrFail($id)->bonus);
//
//                foreach($tweets as $key=>$value){
//
//                    $value -> bonus = $tweet_bonus[$key];
//                }
//
//            } elseif (2 == $type) {
//
//                // 判断用户是否登录
//                if(!$user = Auth::guard('api')->user())
//                    return response()->json(['error'=>'bad_request'],403);
//
//                $tweets = TweetActivity::with('hasOneTweet')
//                    -> where('activity_id',$id)
//                    -> where('user_id',$user->id)
//                    -> get();
//
//            } else {
//
//                // 页码
//                $page = $request -> get('page',1);
//
//                $tweets = TweetActivity::with('hasOneTweet')
//                    -> where('activity_id',$id)
//                    -> orderBy('id','desc')
//                    -> forPage($page,$this -> paginate)
//                    -> get();
//            }
//
//            // 返回数据
//            return response()->json([
//
//                // 数据
//                'data' => count($tweets) ? $this->activityTweetsTransformer->transformCollection($tweets->all()) : [],
//
//                // 每次应返回数据数量
//                'count' => 1 == $type ? 50 : $this -> paginate,
//            ]);
//
//        } catch (ModelNotFoundException $e) {
//            return response()->json(['error' => 404], 'not_found');
//        } catch (\Exception $e) {
//            return response()->json(['error' => 404], 'not_found');
//        }
//    }

    public function competitionTweets($id,Request $request)
    {
        try {
            // 获取所取动态的类型,0为全部，1为排行榜,2为自己参加
            $type = $request -> get('type',0);

            // 如果为排行榜，一次取全部50条数据
            if (1 == $type) {

                // 动态详情
                $tweets = TweetActivity::with(['hasOneUser'=>function($q){
                    $q -> select('id','nickname','avatar','verify');
                }, 'hasOneTweet'=>function($q){
                    $q -> with(['hasOneContent', 'hasManyTweetReply'=>function($q) {
                        $q -> with(['belongsToUser' => function($q) {
                            $q -> select('id', 'nickname');
                        }]) -> where('anonymity', 0)    // 公开
                        -> status()
                            -> orderBy('like_count', 'DESC')
                            -> select('id', 'user_id', 'tweet_id', 'reply_user_id', 'content');
                    }]) -> select('id', 'user_id', 'duration', 'video', 'like_count', 'browse_times', 'reply_count','screen_shot', 'created_at' );
                }])
                    -> where('activity_id',$id)
                    -> orderBy('like_count','desc')
                    -> take(50)
                    -> get(['activity_id', 'tweet_id', 'user_id', 'like_count']);

                $count = $tweets -> count();

                # 获取每个动态的奖金
                $account = new GoldTransactionService;

                $tweet_bonus = $account -> bonusAllocation($count,Activity::findOrFail($id)->bonus);

                foreach($tweets as $key=>$value){

                    $value -> bonus = $tweet_bonus[$key];
                }

            } else {

                // 页码
                $page = $request -> get('page',1);

                $tweets = TweetActivity::with(['hasOneUser'=>function($q){
                    $q -> select('id','nickname','avatar','verify');
                }, 'hasOneTweet'=>function($q){
                    $q -> with(['hasOneContent', 'hasManyTweetReply'=>function($q) {
                        $q -> with(['belongsToUser' => function($q) {
                            $q -> select('id', 'nickname');
                        }]) -> where('anonymity', 0)    // 公开
                        -> status()
                            -> orderBy('like_count', 'DESC')
                            -> select('id', 'user_id', 'tweet_id', 'reply_user_id', 'content');
                    }]) -> select('id', 'user_id', 'duration', 'video', 'like_count', 'browse_times', 'reply_count','screen_shot', 'created_at' );
                }])
                    -> where('activity_id',$id)
                    -> orderBy('id','desc')
                    -> forPage($page,$this -> paginate)
                    -> get(['activity_id', 'tweet_id', 'user_id', 'like_count']);
            }

            // 返回数据
            return response()->json([

                // 数据
                'data' => count($tweets) ? $this->activityTweetDetailsTransformer->transformCollection($tweets->all()) : [],

                // 每次应返回数据数量
                'count' => 1 == $type ? 50 : $this -> paginate,
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 404], 'not_found');
        } catch (\Exception $e) {
            return response()->json(['error' => 404], 'not_found');
        }
    }

    /**
     * 获取某一赛事下的动态详情 列表方式，方便用户更流畅的切换数据
     *
     */
//    public function activityTweetsDetails(Request $request)
//    {
//        try {
//            // 参数获取
//            $activity_id = (int)$request -> get('activity_id');
//            $type = $request -> get('type');    // 类型，1排行榜，2全部
//
//            if(!in_array($type, [1,2]) || !$activity_id)
//                return response() -> json(['error' => 'bad_request'], 403);
//
//            // 排序方式
//            $order = 1 == $type ? 'like_count' : 'id';
//
//            // 页码
//            $page = $request -> get('page',1);
//
//            // 动态详情
//            $data = TweetActivity::with(['hasOneUser'=>function($q){
//                $q -> select('id','nickname','avatar','verify');
//            }, 'hasOneTweet'=>function($q){
//                $q -> with(['hasOneContent', 'hasManyTweetReply'=>function($q) {
//                    $q -> with(['belongsToUser' => function($q) {
//                        $q -> select('id', 'nickname');
//                    }]) -> where('anonymity', 0)    // 公开
//                    -> status()
//                        -> orderBy('like_count', 'DESC')
//                        -> select('id', 'user_id', 'tweet_id', 'reply_user_id', 'content');
//                }]) -> select('id', 'user_id', 'duration', 'video', 'like_count', 'browse_times', 'reply_count','screen_shot', 'created_at' );
//            }])
//                -> where('activity_id',$activity_id)
//                -> orderBy($order,'desc')
//                -> paginate(2, ['activity_id', 'tweet_id', 'user_id', 'like_count'], 'page', $page);    // TODO 测试翻页
////                -> paginate($this->paginate, ['activity_id', 'tweet_id', 'user_id', 'like_count'], 'page', $page);
//
//            // 返回数据
//            return response()->json([
//
//                // 数据
//                'data' => $data->count() ? $this->activityTweetDetailsTransformer->transformCollection($data->all()) : [],
//
//                'page_count' => $data -> toArray()['last_page']
//            ]);
//
//        } catch (ModelNotFoundException $e) {
//            return response()->json(['error' => 404], 'not_found');
//        } catch (\Exception $e) {
//            return response()->json(['error' => 404], 'not_found');
//        }
//    }

    /**
     * 获取赛事详情里的动态详情
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activityTweetsDetails(Request $request)
    {
        try {
            // 参数获取
            $activity_id = (int)$request -> get('activity_id');
            $type = $request -> get('type');    // 类型，1排行榜，2全部
            $sort = $request -> get('sort');    // 排序，1向上请求，2向下请求，
            $tweet_id = (int)$request -> get('tweet_id');

            if(!in_array($type, [1,2]))
                return response() -> json(['error' => 'bad_request'], 403);

            // 排序方式
            $order = 1 == $type ? 'like_count' : 'id';

            // 如果从赛事外面进，没有 $sort 数据
            if(!$sort) {

                $tweet_self = TweetActivity::with(['hasOneUser'=>function($q){
                    $q -> select('id','nickname','avatar','verify');
                }, 'hasOneTweet'=>function($q){
                    $q -> with(['hasOneContent', 'hasManyTweetReply'=>function($q) {
                        $q -> with(['belongsToUser' => function($q) {
                            $q -> select('id', 'nickname');
                        }]) -> where('anonymity', 0)    // 公开
                            -> status()
                            -> orderBy('like_count', 'DESC')
                            -> select('id', 'user_id', 'tweet_id', 'reply_user_id', 'content');
                    }]) -> select('id', 'user_id', 'duration', 'video', 'like_count', 'browse_times', 'reply_count','screen_shot', 'created_at' );
                }])
                    -> where('activity_id', $activity_id)
                    -> where('tweet_id', $tweet_id)
                    -> firstOrFail();

                $tweets_up = $this -> upDownTweets($order, 1, $tweet_id, $activity_id, $tweet_self);
                $tweets_down = $this -> upDownTweets($order, 2, $tweet_id, $activity_id, $tweet_self);

                // 返回数据
                return response()->json([

                    // 数据
                    'tweet_self' => $this->activityTweetDetailsTransformer->transform($tweet_self),
                    'tweets_up' => $tweets_up,
                    'tweets_down' => $tweets_down,
                ]);
            }

            if(!in_array($sort, [1,2]))
                return response() -> json(['error' => 'bad_request'], 403);

            $tweet_self = TweetActivity::where('tweet_id', $tweet_id)->firstOrFail(['id','like_count']);

            // 如果只是单向（向上/向下）请求
            $tweets = $this -> upDownTweets($order, $sort, $tweet_id, $activity_id, $tweet_self);

            // 返回数据
            return response()->json([

                // 数据
                'data' => $tweets,
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 404], 'not_found');
        } catch (\Exception $e) {
            return response()->json(['error' => 404], 'not_found');
        }
    }

    /**
     * 供上面调用使用
     *
     * @param $order    // 类型，1排行榜，2全部
     * @param $sort     // 排序，1向上请求，2向下请求
     * @param $tweet_id     // 动态id
     * @param $activity_id     // 赛事id
     * @return \Illuminate\Http\JsonResponse
     */
    protected function upDownTweets($order, $sort, $tweet_id, $activity_id, $tweet)
    {
        try {

            // 动态详情
            $data = TweetActivity::with(['hasOneUser'=>function($q){
                $q -> select('id','nickname','avatar','verify');
            }, 'hasOneTweet'=>function($q){
                $q -> with(['hasOneContent', 'hasManyTweetReply'=>function($q) {
                    $q -> with(['belongsToUser' => function($q) {
                        $q -> select('id', 'nickname');
                    }]) -> where('anonymity', 0)    // 公开
                    -> status()
                        -> orderBy('like_count', 'DESC')
                        -> select('id', 'user_id', 'tweet_id', 'reply_user_id', 'content');
                }]) -> select('id', 'user_id', 'duration', 'video', 'like_count', 'browse_times', 'reply_count','screen_shot', 'created_at' );
            }])
                -> ofTypeSort($order, $tweet, $sort)
                -> where('activity_id',$activity_id)
                -> take(2)
                -> get(['activity_id', 'tweet_id', 'user_id', 'like_count']);

            // 返回数据
            return $data -> count() ? $this->activityTweetDetailsTransformer->transformCollection($data->all()) : [];

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 404], 'not_found');
        } catch (\Exception $e) {
            return response()->json(['error' => 404], 'not_found');
        }
    }

    /** 新
     * 获取动态的数据
     * @param $mode        刷新类型  1为刷新，2为加载
     * @param $type         动态类型  0为视频，1为图片
     * @param $limit        所取条数
     * @param $timestamp    时间戳
     * @param $topic_id     话题id
     * @return array
     */
    public function getTweet($mode,$type,$limit,$timestamp,$topic_id){

        // 判断是否为空
        if(!$timestamp) return null;

        // 将获取的时间转格式
        $date = Carbon::createFromTimestamp($timestamp)->toDateTimeString();

        // 判断是否有$topic有为带话题的
        if($topic_id){

            // 查询动态数据
            $tweets = Tweet::with('hasOneContent','belongsToLabel', 'belongsToUser')
                ->whereHas('belongsToManyTopic',function($query)use($topic_id){
                    $query->where('topic_id',$topic_id);
                })
                ->ofFlushDate($mode,$date)
                ->visible()
                ->where('type',$type)
                ->able()
                ->orderBy('created_at','desc')
                ->take($limit)->get();
        }else{

            // 查询动态数据
            $tweets = Tweet::with('hasOneContent','belongsToLabel', 'belongsToUser')
                ->ofFlushDate($mode,$date)
                ->visible()
                ->where('type',$type)
                ->able()
                ->orderBy('created_at','desc')
                ->take($limit)->get();
        }

        // 统计总数量
        $tweet_count = $tweets->count();

        // 如果数量为0，返回null
        if(!$tweet_count) return null;

        // 遍历集合
        $tweets->each(function($tweet) {

            // 判断是否为图片
            if($tweet -> type == 1){

                // 将json格式字符串转为变量模式
                foreach(json_decode($tweet -> photo) as $value){

                    $data[] = CloudStorage::downloadUrl($value);
                }

                // 将一维数组转为集合形式
                $tweet -> photo = collect($data);
            }
        });

        // 根据mode类型进行获取不同时间戳
        if($mode == 1){
            // 刷新时的时间戳
            $timestamp_new = $tweets->first()->created_at;
        }else{
            // 加载时的时间戳
            $timestamp_new = $tweets->last()->created_at;
        }

        return [
            // 获取的数据
            'data' => $tweet_count ? $this->hotTweetsTransformer->transformCollection($tweets->all()) : [],

            // 最后一条信息的时间戳
            'timestamp' => $tweet_count ? (int)strtotime($timestamp_new) : null,

            // 本次获取的总数量
            'count' => $tweet_count,
        ];
    }

    /** 旧
     * 获取动态的数据
     * @param $type         动态类型  0为视频，1为图片
     * @param $limit        所取条数
     * @param $timestamp    时间戳
     * @return array
     */
    public function getTweetData($type,$limit,$timestamp){

        // 判断是否为空
        if(!$timestamp) return null;

        // 将获取的时间转格式
        $date = Carbon::createFromTimestamp($timestamp)->toDateTimeString();

        // 查询动态数据
        $tweets = Tweet::with('belongsToLabel','belongsToUser')
            ->ofDate($date)
            ->where('type',$type)
            ->orderBy('created_at','desc')
            ->take($limit)->get();

        // 统计总数量
        $tweet_count = $tweets->count();

        // 遍历集合
        $tweets->each(function($tweet) {

            // 判断是否为图片
            if($tweet -> type == 1){

                // 将json格式字符串转为变量模式
                foreach(json_decode($tweet -> photo) as $value){

                    $data[] = CloudStorage::downloadUrl($value);
                }

                // 将一维数组转为集合形式
                $tweet -> photo = collect($data);
            }
        });

        return [
            // 获取的数据
            'data' => $tweet_count ? $this->hotTweetsTransformer->transformCollection($tweets->all()) : [],

            // 最后一条信息的时间戳
            'timestamp' => $tweet_count ? (int)strtotime($tweets->last()->created_at) : null,

            // 本次获取的总数量
            'count' => $tweet_count,
        ];
    }

    /**
     * 验证动态 content中内容是否包括@数据 并返回@人的id
     * @param $content
     * @return null|array
     */
    protected function regexAt($content)
    {
        $match_at = regex_at($content);
        // 有@的匹配值
        if ($match_at[0]) {
            $arr = array_map(function($value){
                return str_replace(' ','',str_replace('@','',$value));
            },$match_at[1][0]);
            return User::whereIn('nickname',$arr)->get(['id','nickname']);
        }
        return null;
    }

    /**
     * 验证动态的content是否包括话题,并返回话题名称
     * @param $content
     * @return array|null
     */
    protected function regexTopic($content)
    {
        // 使用自定义函数，返回匹配成功的次数及匹配的结果集所组成的数组
        $match_topic = regex_topic($content);

        // 判断成功匹配的次数
        if ($match_topic[0]) {

            // 返回去除#符号后的话题名称
            return array_unique(array_map(function($value){
                return str_replace('#','',$value);
            },$match_topic[1][0]));

        }
        return null;
    }

    /**
     * 供上面调用使用，查询置顶、推荐、黑名单、普通的动态
     * @param int $channel_id   频道id
     * @param string $filter    过滤条件  置顶、推荐
     * @param array $blacklist  屏蔽黑名单的动态
     * @param int $page 页码
     * @param int $paginate 每一页数量
     * @return mixed
     */
    protected function tweetFilter($channel_id, $filter, $blacklist = [], $page=1, $paginate=20)
    {
        $tweets = Tweet::with('hasOneContent','belongsToUser')
            ->ofFilter($filter, $blacklist, $page, $paginate)
            ->whereHas('hasManyChannelTweet',function ($q) use ($channel_id) {
                $q->where('channel_id', $channel_id);
            })
            ->visible()
            ->where('type',0)
            ->orderBy('id','desc')
            ->active()
            ->get();

        return $tweets;
    }

    /**
     * 根据用户ID及话题数组数据，创建不存在的话题，并返回所有话题的ID
     * @param $id
     * @param $topics
     * @param $tweet
     * @return mixed
     */
    protected function createNewTopic($id, $topics, $tweet)
    {
        if (empty($topics)) {
            return null;
        }

        // 查看是否有已经存在的话题集合
        $topics_exists = Topic::whereIn('name',$topics)->get();

        // 取出话题集合中的name值
        $select_topics = $topics_exists ->pluck('name')->all();

        // 初始化 topics 的id接收数组
        $topics_ids = [];

        // 判断是否有已存在的话题
        if($topics_exists->count()) {

            // 遍历数组
            foreach($topics_exists as $value){

                // 将相关话题的作品总数 加1
                Topic::findOrFail($value -> id) -> increment('work_count');

                $topics_ids[] = $value -> id;
            }
        }

        // 获取两个数组的差集
        $diff = array_diff($topics, $select_topics);

        $time = new Carbon();

        // 遍历存入表中
        foreach ($diff as $item) {

            $topic = Topic::create([
                'name'          => $item,
                'user_id'       => $id,
                'comment'       => $tweet->content,
                'work_count'    => 1,
                'created_at'    => $time,
                'updated_at'    => $time
            ]);

            $topics_ids[] = $topic -> id;
        }

        return $topics_ids;
    }

    /**
     * 绑定动态与话题
     * @param $tweet
     * @param $topics
     * @return false;
     */
    protected function createTweetTopic($tweet,$topics)
    {
        if ($topics === null) {
            return false;
        }
        $time = new Carbon();

        $tweet_topics = [];
        foreach ($topics as $topic) {
            $tweet_topics[] = [
                'tweet_id'      => $tweet->id,
                'topic_id'      => $topic,
                'created_at'    => $time,
                'updated_at'    => $time
            ];
        }
        DB::table('tweet_topic')->insert($tweet_topics);
    }

    /**
     * 绑定用户与话题
     * @param $user_id 用户id
     * @param $topics  话题id数组
     * @return false;
     */
    protected function createUserTopic($user_id,$topics)
    {
        if ($topics === null) {
            return false;
        }
        $user_topics = [];

        $time = new Carbon();

        foreach ($topics as $topic) {

            // 判断该用户是否已经参与该话题，如果未参与则存入数据库
            if(!TopicUser::where('topic_id',$topic)->where('user_id',$user_id)->get()->first()){

                // 将信息存入数组中
                $user_topics[] = [
                    'user_id'      => $user_id,
                    'topic_id'      => $topic,
                    'created_at'    => $time,
                    'updated_at'    => $time
                ];

                // 话题参与人数加1
                Topic::findOrFail($topic) -> increment('users_count');
            }
        }
        // 如果数组不为空，则存入数据库
        if(count($user_topics)) DB::table('topic_user')->insert($user_topics);
    }

    // 创建提醒，并存储@用户信息
    protected function createNotification($id, $at, $tweet)
    {
        if($at->count()){
            $time = new Carbon();
            $new_time = getTime();
            foreach($at as $item){

                // 如果不允许@
                $personalAllow = new CommonController();
                if(!$personalAllow->personalAllow($id,$item->id,'stranger_at')) continue;

                Notification::create([
                    'user_id'        => $id,
                    'notice_user_id' => $item->id,
                    'type'           => 0,
                    'type_id'        => $tweet->id,
                    'created_at'     => $time,
                    'updated_at'     => $time
                ]);

                // 存储@的用户信息
                TweetAt::create([
                    'tweet_id'  => $tweet->id,
                    'user_id'   => $item->id,
                    'nickname'  => $item->nickname,
                    'time_add'  => $new_time
                ]);
            }
        }
    }

    protected function updateRetweetCount($tweet, $type)
    {
        $retweet = Tweet::with('hasOneTweet')->where('id',$tweet->id)->first();
        if ($type === 'add') {
            while ($retweet->hasOneTweet !== null) {
                $retweet = $retweet->hasOneTweet;
                $retweet->retweet_count ++;
                $retweet->save();
            }
        } else if($type === 'del') {
            while ($retweet->hasOneTweet !== null) {
                $retweet = $retweet->hasOneTweet;
                $retweet->retweet_count --;
                $retweet->save();
            }
            if ($tweet->retweet !== null) {
                Tweet::where('retweet',$tweet->id)
                    ->update(['retweet' => $tweet->retweet]);
            }
        }
    }

    /**
     * 动态去重，并合并多个类型的动态
     * @param $array 存储id
     * @param $data  存储所有的值
     * @param $tweets
     */
    public function arrayIntersect(&$array, &$data, $tweets)
    {
        if (isset($tweets)){
            foreach ($tweets as $tweet) {
                $intersect = array_intersect($array,[$tweet->id]);
                if (! sizeof($intersect)) {

                    array_push($array,$tweet->id);

                    // 把数据添加到$data尾部
                    $data->push($tweet);
                }
            }
        }
    }

    /**
     * 判断请求中条数及时间戳的格式，并将时间戳转格式
     * @param Request $request
     * @return array
     */
    public function transformerTimeAndLimit(Request $request)
    {
        $limit = $request->get('limit');
        $timestamp = $request->get('timestamp');

        $limit = isset($limit) && is_numeric($limit) ? $limit : 20;
        $timestamp = isset($timestamp) && is_numeric($timestamp) ? $timestamp : time();

        // 将获取的时间转格式
        $date = Carbon::createFromTimestamp($timestamp)->toDateTimeString();

        return array($date, $limit);
    }
}