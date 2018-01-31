<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ChannelTweetsTransformer;
use App\Api\Transformer\CorrelationTweetsTransformer;
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
use App\Models\JoinVideo;
use App\Models\Keywords;
use App\Models\KeywordTweets;
use App\Models\Make\MakeTemplateFile;
use App\Models\Mark;
use App\Models\NoExitWord;
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
use App\Models\TweetJoin;
use App\Models\TweetLike;
use App\Models\ChannelTweet;
use App\Models\TweetMark;
use App\Models\TweetPhone;
use App\Models\TweetQiniuCheck;
use App\Models\TweetTopic;
use App\Models\TopicUser;
use App\Models\TweetReply;
use App\Models\TweetTrasf;
use App\Models\TweetTrasformer;
use App\Models\TweetTrophyLog;
use App\Models\UserChannel;
use App\Models\UserKeywords;
use App\Models\UserSearchLog;
use App\Models\User;
use App\Models\UsersLikes;
use App\Models\UsersUnlike;
use App\Models\Word_filter;
use Carbon\Carbon;
use CloudStorage;
use function foo\func;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\GoldTransactionService;
use Auth;
use Illuminate\Support\Facades\Cache;
use DB;
use Illuminate\Support\Collection;
use App\Services\TweetService;
use Qiniu\Processing\ImageUrlBuilder;
use Tymon\JWTAuth\Facades\JWTAuth;

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

    private $correlationTweetsTransformer;

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
        AdsDiscoverTransformer $adsDiscoverTransformer,
        CorrelationTweetsTransformer $correlationTweetsTransformer

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
        $this -> correlationTweetsTransformer = $correlationTweetsTransformer;
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

            // 获取登录用户信息
            $user = Auth::guard('api')->user();

            // 判断是否为好友关系
            if ($user && Friend::ofIsFriend($id, $user->id)->first()) {

                // 查询可以查看的好友的动态
                $tweets = Tweet::ofFriendTweets($user->id)->whereNotIn('active',[2,5]);

                // 自己
            } else if ($user && $user->id == $id) {

                $tweets = new Tweet();
            } else {

                // 非好友关系，只能看对方设置为公开的
                $tweets = Tweet::where('visible', 0)->whereNotIn('active',[2,5]);
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
                        -> where('anonymity',0)
                        -> status()
                        -> orderBy('like_count','DESC')
                        -> select(['id', 'user_id', 'tweet_id', 'content','created_at']);
                }])
                -> where('user_id', $id)
                -> able()
                -> orderBy('user_top', 'DESC')
                -> orderBy('id', 'DESC')
                -> ofSearch($search)
                -> forPage($page,$this->paginate)
                -> get();


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
     * 动态的详情
     * @param $id
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function details($id, Request $request)
    {
        try {
            $page = (int)$request -> get('page', 1);

            // 获取要查询的动态详情
            $tweets_data = Tweet::with([
                'hasOneContent'=>function($q){
                    $q->select(['tweet_id','content']);
                },
                'belongsToManyTopic'=>function($q){
                    $q->select(['topic_id','name']);
                },
                'hasManyAt',
                'belongsToUser'=>function($q){
                    $q -> select('id', 'advertisement','nickname','avatar','hash_avatar','verify');
                }])
                -> able()
                -> find($id,['id','user_id','visible','type','location','retweet','browse_times','video','like_count','reply_count','shot_width_height','screen_shot','created_at','tweet_grade_total','tweet_grade_times','original']);

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
                -> status()
                -> orderBy('like_count','desc')
                -> where('reply_id',NULL)
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
                -> take(5)
                -> orderBy('id', 'desc')
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

                -> status()
                -> take(3)
                -> where('like_count', '>', $hot_set)
                -> where('reply_id',null)
                -> orderBy('like_count', 'DESC')
                -> get(['id', 'user_id', 'content', 'created_at', 'anonymity', 'like_count', 'grade']);

            //按时间排序的评论
            $now_replys = TweetReply::with(['belongsToUser' => function($q){
                $q -> select('id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info');
            }])
                -> where('tweet_id', $id)
                -> status()
                -> orderBy('created_at', 'DESC')
                -> where('reply_id',null)
                -> forPage($page, $this -> paginate)
                -> get(['id', 'user_id', 'content', 'created_at', 'anonymity', 'like_count', 'grade']);

            // 声明广告地址变量
            $advertisement = '';

            // 判断是否推送广告
            if(0 === $tweets_data->belongsToUser->advertisement){

                // 用户未登录或登录后接受广告推送
                if(!$user || 0 === $user->advertisement){

                    $advertisement = '';

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
        }catch (ModelNotFoundException $e) {
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
            $tweets_data = Tweet::with([
                'hasOneContent'=>function($q){
                    $q->select(['tweet_id','content']);
                },'belongsToUser'=>function($q){
                    $q -> select('id', 'advertisement','nickname','avatar','hash_avatar','verify');
                }])
                ->able()
                -> find($id,['id','user_id','visible','type','location','retweet','browse_times','video','like_count','reply_count','shot_width_height','screen_shot','created_at','tweet_grade_total','tweet_grade_times','original']);

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
            ->whereNotIn('active',[2,5])
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
                ->whereNotIn('active',[2,5])
                -> get(['id', 'type', 'duration', 'user_id', 'screen_shot', 'photo', 'created_at','browse_times','video']);
        }

        // 判断数量 随机取20个
        return $this->tweetsAtTransformer->transformCollection($tweets->random(2)->values()->all());

    }

    /**
     * 发布动态和赛事作品
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
     *      @Parameter("visible",required=true, description="0 : 全部可见，1 : 好友圈可见，2 : 好友圈私密，3 : 仅自己可见，, default=0),
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
            $input = $request -> all();   //2017-12-06 16:27:46

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
                'user_id'           =>  $id,
                'retweet'           =>  (int)$request->get('retweet'),
                'original'          =>  (int)$request->get('original'),
//                'photo'           =>  $request->get('photo'),
                'video'             =>  $request->get('video'),
                'duration'          =>  (int)$request->get('duration'),
                'size'              =>  (int)$request->get('size',0),
                'screen_shot'       =>  $request->get('screen_shot'),
                'shot_width_height' =>  $request->get('shot_width_height'),
                'type'              =>  (int)$request->get('type') ?: 0,
                'visible'           =>  (int)$request->get('visible') ?: 0,
                'visible_range'     =>  $request->get('visible_range'),
                'is_download'       =>  $request->get('is_download',1),
                'fragment_id'       =>  $request->get('fragment_id') ?: 0,
                'filter_id'         =>  $request->get('filter_id') ?: 0,
                'template_id'       =>  $request->get('template_id') ?: 0,
                'lossless'          =>  $request -> get('lossless','0'),
                'lgt'               =>  $request->get('lgt',''),
                'lat'               =>  $request->get('lat',''),
            ];


            // 内容 过滤内容
            $content = $request->get('content') ? removeXSS($request->get('content')) : '';

            // 判断
            if($newTweet['video']){

                // 有视频一定就要有时长
                if(!$newTweet['duration']) return response()->json(['error'=>'badrequest'],403);

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

            // 处理location信息
            $location = $request->get('location');

            // 开启事务
            DB::beginTransaction();

            // 判断所传数据中是否有位置信息
            if($location){

                // 匹配数据库中是否已经存在该记录
//                $location_able = Location::where('formattedAddress',$location['formattedAddress'])->get()->first();

                // 如果不存在，将信息存入location表中
//                if(!$location_able) $location_able = Location::create($location);

                // 将位置信息存入tweet数组中
//                $newTweet['location_id'] = $location_able -> id;
                $newTweet['location'] = $location;
            }

//``````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````

//      //TODO 发布动态的手机系统
        if($request->get('phone_type','') || $request->get('phone_os','') || $request->get('camera_type','')) {


              //手机类型
              $phone_type = $request->get('phone_type', '');

              //手机系统信息
              $phone_os = $request->get('phone_os', '');

              //相机信息
              $camera_type = $request->get('camera_type', '');

              $phone = TweetPhone::where('phone_type', $phone_type)->where('phone_os', $phone_os)->where('camera_type', $camera_type)->first();

              if (!$phone) {

                  $phone = TweetPhone::create([
                      'phone_type' => $phone_type,
                      'phone_os' => $phone_os,
                      'camera_type' => $camera_type,
                      'time_add' => $time,
                      'time_update' => $time,
                  ]);
              }

              $phone_id = $phone->id;

              // 将手机信息存入tweet数组中
              $newTweet['phone_id'] = $phone_id;
        }

            // 将数据存入 tweet 表中
            $tweet = Tweet::create($newTweet);

            //是否无损
            if ($request->get('lossless','0') === '0'){
                //判断尺寸
                $shot_width_height = $request->get('shot_width_height');
                $width = substr($shot_width_height,0,strrpos($shot_width_height,'*'));
                $height = substr($shot_width_height,strrpos($shot_width_height,'*')+1,strlen($shot_width_height));
                if ( ( $width >= 1280  || $height >= 720 ) && $request->get('joinvideo') === '0'  ){
                    TweetTrasf::create([
                        'tweet_id' =>   $tweet->id,
                    ]);
                }
            }

            //关键词提取
            if($content) {

                //提取关键词
                $arr = getKeywords($content);


                $keywords = [];
                foreach ($arr as $v) {
                    $res = Keywords::where('keyword', '=', $v)->first();
                    if ($res) {
                        $keywords[$res->id] = $v;
                    }
                }

                $tweet_keywords = getRandomN( array_keys($keywords) );

                //存入数据
                if($tweet_keywords){
                    foreach ($tweet_keywords as $v){
                        DB::table('keywords_tweet')->insert([
                            'tweet_id'      => $tweet->id,
                            'keyword_id'    => $v,
                            'create_time'   => time(),
                            'update_time'   => time(),
                        ]);
                    }
                }
            }

//``````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````
            // 动态内容 zx_tweet_content 表
            $a = TweetContent::create([
                'tweet_id' => $tweet -> id,
                'content'  => $content ? $content : '',
            ]);

            if(isset($input['activity_id'])
                && is_numeric($input['activity_id'])
                && Activity::where('active',0)->find($input['activity_id'])){

                if (Activity::where('active',0)->find($input['activity_id'])->user_id == $id){
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

            }elseif(isset($input['activity_id']) && is_numeric($input['activity_id']) && Activity::where('active',1)->findOrFail($input['activity_id'])){

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

            //拼接
            if ($request->get('joinvideo') === '1' ){
                if (is_null( $join_id = $request->get('joinid'))) return response()->json(['message'=>'joinid empty'],400);
                JoinVideo::find($join_id)->increment('down_count');
                TweetJoin::create(['tweet_id' => $tweet->id,'join_id'  => $join_id,]);
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

            // 创建提醒
            if(!empty($at)){

                $this->createNotification($id, $at, $tweet);
            }

            # 更新 users 表中的作品总量数据

            // 转发 数量+1
            if($tweet->retweet){

                $time = User::find($id)->last_token;
                // 转发总量加1
                User::findOrfail($id) -> increment('retweet_count');

                // 更新被转发动态的 retweet 字段的值 加1
                Tweet::findOrFail($newTweet['retweet']) -> increment('retweet_count');

                User::where('id',$id)->update(['last_token'=>$time]);

            // 原创
            }else{

                $time = User::find($id)->last_token;
                // 作品总量加1
                User::findOrfail($id) -> increment('work_count');

                User::where('id',$id)->update(['last_token'=>$time]);

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

//            大于10分钟  人工审核
            if( (int)$request->get('duration') <= 600){
                //写入待检测
                DB::table('tweet_to_qiniu')->insert([
                    'tweet_id'    => $tweet->id,
                    'create_time' => time(),
                ]);
            }

            //是否添加水印
            if (!is_numeric($request->get('mark',2))) return  response()->json(['message'=>'markType is error'],403);

            //默认为不添加
            $mark = $request->get('mark',2);

            if($mark==1){
                //接收水印id
                if ( !is_numeric($request->get('mark_id'))) return  response()->json(['message'=>'markId is error'],403);

                //水印id
                $mark_id = $request->get('mark_id');

                TweetMark::create([
                    'tweet_id'      =>  $tweet->id,
                    'mark_id'       =>  $mark_id,
                    'create_time'   =>  time(),
                ]);
            }

            return response()->json($this->tweetsTransformer->transform($tweet),201);

        }catch(ModelNotFoundException $e){

            DB::rollback();

            return response()->json(['error'=>'badrequest'],400);
        }catch(\Exception $e){

            DB::rollback();
            return response()->json(['error'=>'bad___request'],400);
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
                ->whereNotIn('active',[2,5])
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
     * 获得关注者的动态
     * @param $id
     * @param Request $request
     * @return array
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
        $tweets = Tweet::with('hasOneContent','belongsToUser','hasOnePhone')
            ->ofSubscriptions($subscriptions,$friends, $id, $date)
            ->orderBy('created_at','desc')
            ->able()
            ->take($limit)
            ->whereNotIn('active',[2,5])
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
            ->whereNotIn('active',[2,5])
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
            $tweets= Tweet::with(['hasOneContent','belongsToUser','hasOnePhone'])
                -> whereHas('hasManyChannelTweet',function($query)use($id){
                    $query->where('channel_id',$id);
                })
                ->where('active',1)
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
     * 获取热门的动态  更换新表 新版 20170908  只有动态 第四次修改
     */
    public function popularIndex(Request $request)
    {
            try {
                //接收页码
                if (!is_numeric($page = $request->get('page',1))) return response()->json(['message'=>bad_request],403);

                $time = getTime();

                // 随机生成选择样式
                $rand = array_rand([1, 2, 3]);

                $ads = [];
                $templates = [];
                $activity = [];

                //获取用户信息
                $user = Auth::guard('api')->user();

                if(0 == $rand){
                    // 广告
                    if ($user){

                        $user_id = $user->id;

//                        $ids_obj = UsersUnlike::where('user_id',$user_id)->where('type','1')->pluck('work_id');
                        $ids_obj = UsersUnlike::whereRaw("user_id={$user_id} and type='1'")->pluck('work_id');

                        $ids_arr = $ids_obj->all();

                        $ads = AdvertisingRotation::with(['belongsToUser'=>function($q){
                            $q->select(['id','nickname','avatar','signature','verify','verify_info']);
                        }])
                            -> active()
                            -> whereNotIn('id',$ids_arr)
                            -> where('from_time','<',$time)
                            -> where('end_time','>',$time)
                            -> get(['id','user_id','type_id','type','url','count','image','time_add','name']);

                    }else{

                        $ads = AdvertisingRotation::with(['belongsToUser'=>function($q){
                            $q->select(['id','nickname','avatar','signature','verify','verify_info']);
                        }])
                            -> whereRaw("active=1 and from_time < $time and end_time > $time")
                            -> get(['id','user_id','type_id','type','url','count','image','time_add','name']);

                    }
                    // 广告
                    if($ads -> count()) {
                        $ads = $ads -> random(1);
                        $ads = $this -> adsDiscoverTransformer -> transformCollection($ads->all());
                    }

                } elseif(1 == $rand) {
                    if ($user){
                        $user_id = $user->id;
                        $ids_obj = UsersUnlike::where('user_id',$user_id)->where('type','3')->pluck('work_id');
                        $ids_arr = $ids_obj->all();

                        // 模板
                        $templates = MakeTemplateFile::with(['belongsToUser'=>function($q){
                            $q->select(['id','nickname','avatar','signature','verify','verify_info']);
                        }])
                            -> where('recommend', 1)
                            -> active()
                            -> where('status', 1)
                            -> orderBy('sort')
                            -> whereNotIn('id',$ids_arr)
                            -> get(['id', 'user_id', 'name','folder_id','intro', 'cover', 'preview_address', 'count', 'time_add']);

                    }else{
                        // 模板
                        $templates = MakeTemplateFile::with(['belongsToUser'=>function($q){
                            $q->select(['id','nickname','avatar','signature','verify','verify_info']);
                        }])
                            -> where('recommend', 1)
                            -> active()
                            -> where('status', 1)
                            -> orderBy('sort')
                            -> get(['id', 'user_id', 'name','folder_id','intro', 'cover', 'preview_address', 'count', 'time_add']);
                    }

                    if($templates -> count()) {
                        $templates = $templates -> random(1);
                        $templates = $this -> templateDiscoverTransformer -> transformCollection($templates->all());
                    }

                } else {
                    $time = time();
                    if ($user){
                        $user_id = $user->id;
                        $ids_obj = UsersUnlike::where('user_id',$user_id)->where('type','2')->pluck('work_id');
                        $ids_arr = $ids_obj->all();
                        // 竞赛
                        $activity = Activity::with(['belongsToUser'=>function($q){
                            $q->select(['id','nickname','avatar','signature','verify','verify_info']);
                        }])
                            -> recommend()
                            -> ofExpires()
                            ->whereNotIn('id',$ids_arr)
                            -> get(['id', 'user_id', 'comment', 'location', 'icon', 'recommend_expires', 'time_add']);
                    }else{
                        // 竞赛
                        $activity = Activity::with(['belongsToUser'=>function($q){
                            $q->select(['id','nickname','avatar','signature','verify','verify_info']);
                        }])
                            -> recommend()
                            -> ofExpires()
                            -> get(['id', 'user_id', 'comment', 'location', 'icon', 'recommend_expires', 'time_add']);
                    }


                    if($activity -> count()) {
                        $activity = $activity -> random(1);
                        $activity = $this -> activityDiscoverTransformer -> transformCollection($activity->all());
                    }
                }

                //接收用户信息
                $user = Auth::guard('api')->user();

                if ($user){
                    //查看用户喜好
                    $user_channels = UsersLikes::where('user_id',$user->id)->pluck('channel_id');

                    //获取用户ID
                    $user_id = $user->id;

                    //将用户不感兴趣的排除
                    $ids_obj = UsersUnlike::where('user_id',$user_id)->where('type','0')->pluck('work_id');
                    $ids_arr = $ids_obj->all();

                    //获取用户的黑名单
                    $users_id_black = Blacklist::where('from',$user_id)->pluck('to');
                    $users_id_black = $users_id_black->all();

                    if ($user_channels->all()) {
                        $user_channels = explode(',', $user_channels->all()[0]);

                        $user_channels = Channel::where('active',1)->whereIn('id',$user_channels)->pluck('id')->all();

                        //获取推荐的动态
                        $hot = TweetHot::with(['hasOneTweet'])
                            ->where('top_expires', '>=', time())
                            ->orWhere('recommend_expires', '>=', time())
                            ->pluck('tweet_id');

                        $tweets_data = Tweet::WhereHas('hasOneHot', function ($q) use ($hot) {
                            $q->whereIn('tweet_id', $hot->all());
                        })
                            ->where('type', 0)
                            ->where('active', 1)
                            ->where('visible', 0)
                            ->with(['belongsToManyChannel' => function ($q) {
                                $q->select(['name']);
                            }, 'hasOneContent' => function ($q) {
                                $q->select(['content', 'tweet_id']);
                            }, 'belongsToUser' => function ($q) {
                                $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                            },'hasOnePhone' =>function($q){
                                $q->select(['id','phone_type','phone_os','camera_type']);
                            }])
                            ->orderBy('created_at', 'DESC')
                            ->whereIn('channel_id', $user_channels)
                            ->whereNotIn('id',$ids_arr)
                            ->whereNotIn('user_id',$users_id_black)
                            ->forPage($page, $this->paginate)
                            ->get(['id', 'user_id', 'created_at', 'type', 'screen_shot', 'duration', 'location','phone_id','lat','lgt',
                                'browse_times', 'like_count', 'reply_count', 'tweet_grade_total', 'tweet_grade_times',
                                'video', 'transcoding_video', 'video_m3u8', 'norm_video', 'high_video', 'join_video']);

                            $tweets_data = $this->channelTweetsTransformer->transformCollection($tweets_data->all());

                    }else{
                        //获取推荐的动态
                        $except  = TweetHot::with(['hasOneTweet'])
                            ->where('top_expires','>=',time())
                            ->orWhere('recommend_expires','>=',time())
                            ->pluck('tweet_id');

                        $tweets = Tweet::where('type',0)
                            ->where('active',1)
                            ->where('visible',0)
                            ->with(['belongsToManyChannel' =>function($q){
                                $q -> select(['name']);
                            },'hasOneContent' =>function($q){
                                $q->select(['content','tweet_id']);
                            },'belongsToUser' =>function($q){
                                $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
                            },'hasOnePhone' =>function($q){
                                $q->select(['id','phone_type','phone_os','camera_type']);
                            }])
                            ->orderBy('created_at','DESC')
                            ->whereIn('id',$except->all())
                            ->whereNotIn('id',$ids_arr)
                            ->whereNotIn('user_id',$users_id_black)
                            ->forPage($page,$this->paginate)
                            ->get(['id','user_id','created_at','type','screen_shot','duration','location','phone_id','lat','lgt',
                                'browse_times','like_count','reply_count','tweet_grade_total','tweet_grade_times',
                                'video','transcoding_video','video_m3u8','norm_video','high_video','join_video']);

                        $tweets_data = $this->channelTweetsTransformer->transformCollection($tweets->all());
                    }
                }else{
                    $tweets_data = $this -> hot($page);
                }

                // 返回数据
                return response()->json([

                    // 动态
                    'data'       => $tweets_data,

                    // 广告位
                    'ads'        => $ads,

                    // 模板
                    'templates'  => $templates,

                    // 赛事
                    'activity'  => $activity,

                    // 总页码
//                    'page_count' => ceil($tweets->count()/$this->paginate)
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'not_found'], 404);
            }
    }

    private function hot($page)
    {
        //获取推荐的动态
        $except  = TweetHot::with(['hasOneTweet'])
            ->where('top_expires','>=',time())
            ->orWhere('recommend_expires','>=',time())
            ->pluck('tweet_id');

        $tweets = Tweet::where('type',0)
            ->where('active',1)
            ->where('visible',0)
            ->orderBy('created_at','DESC')
            ->with(['belongsToManyChannel' =>function($q){
                $q -> select(['name']);
            },'hasOneContent' =>function($q){
                $q->select(['content','tweet_id']);
            },'belongsToUser' =>function($q){
                $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
            },'hasOnePhone' =>function($q){
                $q->select(['id','phone_type','phone_os','camera_type']);
            }])
            ->whereIn('id',$except->all())
            ->forPage($page,$this->paginate)
            ->get(['id','user_id','created_at','type','screen_shot','duration','location','phone_id','lat','lgt',
                'browse_times','like_count','reply_count','tweet_grade_total','tweet_grade_times',
                'video','transcoding_video','video_m3u8','norm_video','high_video','join_video']);

            $tweets_data = $this->channelTweetsTransformer->transformCollection($tweets->all());

        return $tweets_data;
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
                $keyword->save();
            }

            $hotsearchword = HotSearch::where('hot_word',$name)->first();
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
                ->whereNotIn('active',[2,5])
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
                    -> whereNotIn('active',[2,5])
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
            $tweets= Tweet::with(['hasOneContent','belongsToUser','hasOnePhone'])
                -> able()
                -> whereHas('belongsToManyTopic',function($query)use($id){
                    $query->where('topic_id',$id);
                })
                ->whereNotIn('active',[2,5])
                -> orderBy($field_order, 'DESC')
                -> get();

            $topic_top = '';

            // 获取置顶的动态
            if(1 == $page)
                $topic_top = Tweet::whereHas('belongsToTopicTop', function($q)use($id){
                    $q -> where('topic_id',$id);
                })
                    ->whereNotIn('active',[2,5])
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
                ->whereNotIn('active',[2,5])
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


    public function competitionTweets($id,Request $request)
    {
        try {
            // 获取所取动态的类型,0为全部，1为排行榜,2为自己参加
            $type = $request -> get('type',0);

            // 如果为排行榜，一次取全部50条数据
            if (1 == $type) {       // 1 热门  0全部

                // 动态详情
                $tweets = TweetActivity::WhereHas('hasOneTweet',function($q){
                    $q->whereIn('active',[0,1]);
                })
                    ->with(['hasOneUser'=>function($q){
                    $q -> select('id','nickname','avatar','verify');
                }, 'hasOneTweet'=>function($q){
                    $q -> with(['hasOneContent'=>function($q){
                        $q->select(['tweet_id','content']);
                    }, 'hasManyTweetReply'=>function($q) {
                        $q -> with(['belongsToUser' => function($q) {
                            $q -> select('id', 'nickname');
                        }]) -> where('anonymity', 0)    // 公开
                        -> status()
                            -> orderBy('like_count', 'DESC')
                            -> select('id', 'user_id', 'tweet_id', 'reply_user_id', 'content');
                    }]) -> select('id', 'user_id','video_m3u8','norm_video','high_video',
                        'join_video','duration', 'video','transcoding_video','location',
                        'tweet_grade_total','tweet_grade_times', 'like_count', 'browse_times',
                        'reply_count','screen_shot', 'created_at' );
                }])
                    -> where('activity_id',$id)
                    -> orderBy('like_count','desc')
                    -> take(50)
                    -> get(['activity_id', 'tweet_id', 'user_id', 'like_count']);

                $count = $tweets -> count();

                if(!$count){
                    return response()->json(['data'=>[]],200);
                }

                # 获取每个动态的奖金
                $account = new GoldTransactionService;

                $tweet_bonus = $account -> bonusAllocation($count,Activity::find($id)->bonus);

                foreach($tweets->toArray() as $key=>$value){
                    $tweets[$key] -> bonus = $tweet_bonus[$key];
                }


            } else {

                // 页码
                $page = $request -> get('page',1);

                $tweets = TweetActivity::WhereHas('hasOneTweet',function($q){
                    $q->whereIn('active',[0,1]);
                })
                    ->with(['hasOneUser'=>function($q){
                    $q -> select(['id','nickname','avatar','verify']);
                }, 'hasOneTweet'=>function($q){
                    $q -> with(['hasOneContent'=>function($q){
                        $q->select(['tweet_id','content']);
                    }, 'hasManyTweetReply'=>function($q) {
                        $q -> with(['belongsToUser' => function($q) {
                            $q -> select(['id', 'nickname']);
                        }]) -> where('anonymity', 0)    // 公开
                        -> status()
                            -> orderBy('like_count', 'DESC')
                            -> select('id', 'user_id', 'tweet_id', 'reply_user_id', 'content');
                    }]) -> select('id', 'user_id','video_m3u8','norm_video','high_video',
                        'join_video', 'duration','transcoding_video','join_video','video',
                        'location','tweet_grade_total','tweet_grade_times', 'like_count',
                        'browse_times', 'reply_count','screen_shot', 'created_at' )->whereIn('active',[0,1]);
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
                ->whereNotIn('active',[2,5])
                ->orderBy('created_at','desc')
                ->take($limit)->get();
        }else{

            // 查询动态数据
            $tweets = Tweet::with('hasOneContent','belongsToLabel', 'belongsToUser')
                ->ofFlushDate($mode,$date)
                ->visible()
                ->where('type',$type)
                ->able()
                ->whereNotIn('active',[2,5])
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
            ->where('type',$type)
            ->whereNotIn('active',[2,5])
            ->ofDate($date)
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
            ->active()
            ->where('type',0)
            ->orderBy('id','desc')
            ->whereNotIn('active',[2,5])
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

    /**
     * @param $id
     * @return mixed
     */
    public function videoShow($id)
    {
        return CloudStorage::privateUrl(Tweet::find($id)->video);
    }

    /**
     * 动态相关
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function correlation($id,Request $request)
    {
        //接收页数
        if(!is_numeric( $page = $request->get('page',1)))
            return response()->json(['error'=>'bad_request'],403);

        //判断用户是否登录
        $user = Auth::guard('api')->user();

        //获取片段的关键词
        $keywords_ids = KeywordTweets::where('tweet_id','=',$id)->pluck('keyword_id')->all();

        //根据关键词进行匹配
        $tweets = Tweet::WhereHas('belongsToManyKeywords',function ($q) use ($keywords_ids){
            $q->whereIn('keyword_id',$keywords_ids);
        })
            ->with(['belongsToUser'=>function($q){
                $q->select(['id','nickname','avatar','verify','cover','verify_info','signature']);
            },'hasOneContent'=>function($q){
                $q->select(['id','tweet_id','content']);
            }])
            ->where('visible',0)
            ->where('active',1)
            ->orderBy('browse_times','DESC')
            ->where('id','!=',$id)
            ->forPage($page,$this->paginate)
            ->get(['id','user_id','created_at','type','screen_shot','duration','location',
                'browse_times','like_count','reply_count','tweet_grade_total','tweet_grade_times',
                'video','transcoding_video','video_m3u8','norm_video','high_video','join_video']);

        if($user) {
            if ($tweets->count() < $this->paginate) {
                //搜索好友
                $res1 = Friend::where('from', $user->id)->pluck('to');

                if ($res1->all()) {
                    $friends = [];
                    foreach ($res1->toArray() as $k => $v) {
                        $res2 = Friend::where('from', $v)->first();

                        if ($res2) {
                            $friends[] = $v;
                        }
                    }

                    //朋友可见的动态
                    $friends_tweets = Tweet::WhereHas('belongsToUser', function ($q) use ($friends) {
                        $q->whereIn('id', $friends);
                    })
                        ->WhereHas('belongsToManyKeywords', function ($q) use ($keywords_ids) {
                            $q->whereIn('keyword_id', $keywords_ids);
                        })
                        ->with(['belongsToUser' => function ($q) {
                            $q->select(['id','nickname','avatar','verify','cover','verify_info','signature']);
                        }, 'hasOneContent' => function ($q) {
                            $q->select(['id', 'tweet_id', 'content']);
                        }])
                        ->where('visible', 1)
                        ->whereIn('active',[0,1])
                        ->orderBy('created_at', 'desc')
                        ->where('id', '!=', $id)
                        ->forPage($page, $this->paginate)
                        ->get(['id','user_id','created_at','type','screen_shot','duration','location',
                            'browse_times','like_count','reply_count','tweet_grade_total','tweet_grade_times',
                            'video','transcoding_video','video_m3u8','norm_video','high_video','join_video']);

                }else{
                    $friends_tweets = Tweet::where('visible',10)->get();
                }

                //仅自己可见的动态
                $self_tweets = Tweet::WhereHas('belongsToUser', function ($q) use ($user) {
                    $q->where('id', $user->id);
                })
                    ->WhereHas('belongsToManyKeywords', function ($q) use ($keywords_ids) {
                        $q->whereIn('keyword_id', $keywords_ids);
                    })
                    ->with(['belongsToUser' => function ($q) {
                        $q->select(['id','nickname','avatar','verify','cover','verify_info','signature']);
                    }, 'hasOneContent' => function ($q) {
                        $q->select(['id', 'tweet_id', 'content']);
                    }])
                    ->where('visible', 2)
                    ->orderBy('created_at', 'desc')
                    ->whereIn('active',[0,1])
                    ->where('id', '!=', $id)
                    ->forPage($page, $this->paginate)
                    ->get(['id','user_id','created_at','type','screen_shot','duration','location',
                        'browse_times','like_count','reply_count','tweet_grade_total','tweet_grade_times',
                        'video','transcoding_video','video_m3u8','norm_video','high_video','join_video']);

            }
        }
            //合并数据
        if($user){
            if ($tweets->count() < $this->paginate) {
                $data = array_merge($tweets->toArray(), $friends_tweets->toArray(), $self_tweets->toArray());
            }else{
                $data = $tweets->toArray();
            }
        }else{
            $data = $tweets->toArray();
        }
        $data = $this->correlationTweetsTransformer->transformCollection($data);

        if(!$data){
            $except  = TweetHot::with(['hasOneTweet'])
                ->where('top_expires','>=',time())
                ->orWhere('recommend_expires','>=',time())
                ->pluck('tweet_id');

            $tweets = Tweet::with(['belongsToUser'=>function($q){
                    $q->select(['id','nickname','avatar','verify','cover','verify_info','signature']);
                },'hasOneContent'=>function($q){
                    $q->select(['id','tweet_id','content']);
                }])
                ->where('visible',0)
                ->where('active',1)
                ->orderBy('browse_times','DESC')
                ->where('id','!=',$id)
                ->whereIn('id',$except->all())
                ->get(['id','user_id','created_at','type','screen_shot','duration','location',
                    'browse_times','like_count','reply_count','tweet_grade_total','tweet_grade_times',
                    'video','transcoding_video','video_m3u8','norm_video','high_video','join_video']);

            if ($tweets->count()>= 5 ){
                $tweets = $tweets->random(5);
            }
            $data = $this->correlationTweetsTransformer->transformCollection($tweets->toArray());
        }

        return response()->json([
            'data'=>$data,
        ],200);

    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ratio($id,Request $request)
    {
         if ( !is_numeric( $ratio = $request -> get('ratio',0)) ) return response()->json(['message'=>'ratio must be a number'],400);

         switch ($ratio){
             case 0 :
                 return response()->json([
                     'video'  => Tweet::find($id)->transcoding_video ? CloudStorage::downloadUrl(Tweet::find($id)->transcoding_video) : '',
                 ],200);
             case 1 :
                 return response()->json([
                     'video'  =>  Tweet::find($id)->video_m3u8 ? CloudStorage::downloadUrl(Tweet::find($id)->video_m3u8) : '',
                 ],200);
             case 2 :
                 return response()->json([
                     'video'  =>  Tweet::find($id)->norm_video ? CloudStorage::downloadUrl(Tweet::find($id)->norm_video) : '' ,
                 ],200);
             case 3 :
                 return response()->json([
                     'video'  =>  Tweet::find($id)->high_video ? CloudStorage::downloadUrl(Tweet::find($id)->high_video) : '',
                 ],200);
             default :
                 return response()->json(['message'=>'bad request'],400);
         }
    }

}