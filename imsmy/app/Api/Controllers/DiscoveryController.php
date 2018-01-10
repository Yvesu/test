<?php

namespace App\Api\Controllers;

use App\Api\Transformer\DiscoveryTopicsTransformer;
use App\Api\Transformer\ZxHomeImagesTransformer;
use App\Api\Transformer\LocationTweetsTransformer;
use App\Api\Transformer\UsersTransformer;
use App\Api\Transformer\Discover\DiscoverFilmTransformer;
use App\Api\Transformer\Discover\HotActivityTransformer;
use App\Api\Transformer\HotSearchTransformer;
use App\Api\Transformer\RecommendActivityTransformer;
use App\Api\Transformer\TweetsNearbyTransformer;
use App\Api\Transformer\TweetsWatchingTransformer;
use App\Api\Transformer\FeaturedMediaTransformer;
use App\Models\Activity;
use App\Models\FeaturedMedia;
use App\Models\Tweet;
use App\Models\Adcode;
use App\Models\Discover\Cinema;
use App\Models\TweetsPush;
use App\Models\View;
use App\Models\HotSearch;
use App\Models\User;
use App\Models\TweetsPushStatistics;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Cache;
use CloudStorage;

/**
 * 发现相关接口
 *
 * @Resource("Discovery")
 */
class DiscoveryController extends BaseController
{

    private $discoveryTopicsTransformer;
    private $zxHomeImagesTransformer;
    private $locationTweetsTransformer;
    private $hotSearchTransformer;
    private $recommendActivityTransformer;
    private $tweetsNearbyTransformer;
    private $tweetsWatchingTransformer;
    protected $usersTransformer;
    protected $discoverFilmTransformer;
    protected $featuredMediaTransformer;

    // 热门赛事 活动
    protected $hotActivityTransformer;

    // 条数
    protected $paginate = 20;

    public function __construct(
        DiscoveryTopicsTransformer $discoveryTopicsTransformer,
        ZxHomeImagesTransformer $zxHomeImagesTransformer,
        LocationTweetsTransformer $locationTweetsTransformer,
        HotSearchTransformer $hotSearchTransformer,
        RecommendActivityTransformer $recommendActivityTransformer,
        TweetsNearbyTransformer $tweetsNearbyTransformer,
        UsersTransformer $usersTransformer,
        DiscoverFilmTransformer $discoverFilmTransformer,
        HotActivityTransformer $hotActivityTransformer,
        TweetsWatchingTransformer $tweetsWatchingTransformer,
        FeaturedMediaTransformer $featuredMediaTransformer
    )
    {
        $this->discoveryTopicsTransformer = $discoveryTopicsTransformer;
        $this->zxHomeImagesTransformer = $zxHomeImagesTransformer;
        $this->locationTweetsTransformer = $locationTweetsTransformer;
        $this->hotSearchTransformer = $hotSearchTransformer;
        $this->recommendActivityTransformer = $recommendActivityTransformer;
        $this->tweetsNearbyTransformer = $tweetsNearbyTransformer;
        $this->usersTransformer = $usersTransformer;
        $this->discoverFilmTransformer = $discoverFilmTransformer;
        $this->hotActivityTransformer = $hotActivityTransformer;
        $this->tweetsWatchingTransformer = $tweetsWatchingTransformer;
        $this->featuredMediaTransformer = $featuredMediaTransformer;
    }

    /**
     * 发现首页接口
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // 获取 page
            $page = (int)$request -> get('page',1);

            // 获取院线的信息
            $films = Cinema::with(['hasManyPicture' => function($q){
                $q -> orderBy('id','desc');
            }]) -> active() -> get();

            // 获取赛事（原活动）数据
            $activities = Activity::with('belongsToUser', 'hasManyTweets')
                -> ofExpires()
                -> active()
                -> orderBy('like_count', 'desc')
                -> forPage($page, 5)
                -> get();

            // 本次请求活动数量
            $count = $activities -> count();

            // 非第一次请求，只返回动态活动数据
            if($page > 1){

                // 返回数据
                return response()->json([

                    // 热门赛事
                    'activities'   => $this -> hotActivityTransformer->transformCollection($activities->all()),

                    // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                    'link' => $count
                        ? $request->url() .
                        '?page=' . ++$page
                        : null      // 如果数量为0，则不附带搜索条件
                ]);
            }

            $hot_users = Cache::remember('DISCOVERY:HOT:USERS',mt_rand(60,90),function(){

                return User::where('status',0)
                    -> whereIn('verify',[1,2])
                    -> orderBy('like_count','DESC')
                    -> take(200)
                    -> get(['id', 'nickname', 'avatar', 'verify', 'signature', 'verify_info']);
            });

            $hot_users = $hot_users->random(20)->values();

            foreach($hot_users as $value){
                $value -> avatar = CloudStorage::downloadUrl($value -> avatar);
            }

            // 推荐 图片
            $recommend = View::recommend()->take(5)->get();

            // 大家都在搜
//            $hot = HotSearch::recommend()->take(6)->get();

//            dd($activities->all());
            return response()->json([

                // 推荐 图片
                'recommend' => $recommend->count() ? $this->zxHomeImagesTransformer->transformCollection($recommend->all()) : [],

                // 大家都在搜
//                'hot' => $hot->count() ? $this->hotSearchTransformer->transformCollection($hot->all()) : [],

                // 热门用户
                'hot_users' => $hot_users->all(),

                // 院线信息
                'films'    => $films -> count() ?  $this-> discoverFilmTransformer -> transformCollection($films->all()) : [],

                // 热门赛事
//                'activities'   => $activities->count() ? $this->hotActivityTransformer->transformCollection($activities->all()) : [],
                'activities'   => $data,

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link' => $count
                    ? $request->url() .
                    '?page=' . ++$page
                    : null      // 如果数量为0，则不附带搜索条件
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 附近动态接口 TODO 停用
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function nearby(Request $request)
    {
        try {

            // 获取最后一个评论的id
            $last_id = (int)$request -> get('last_id');

            // 获取类型，1代表刷新，2代表加载
            $mode = $request -> get('mode');

            // 判断是否为刷新或加载,否则返回400错误
            if(1 != $mode && 2 != $mode) throw new \Exception('bad_request',400);

            // 获取一次所取数量
            $limit = $request -> get('limit',10);

            // 获取用户所在区的编码
            $adcode = $request -> get('adcode','');

            // 获取用户所在市的编码
            $citycode = $request -> get('citycode','');

            // 判断是否为空
            if(!$adcode || !$citycode)  throw new \Exception('bad_request',400);

            // 获取在 zx_adcode 表中的id 区
//            $nearby_id = Adcode::where('citycode',$citycode)->where('adcode',$adcode) -> get() -> pluck('id');

            // 获取在 zx_adcode 表中的id 市
            $nearby_id = Adcode::where('citycode',$citycode) -> pluck('id');

            // 获取附近动态视频 区县级
            $nearby_tweets = Tweet::with('hasOneContent')
                -> whereIn('nearby_id',$nearby_id)
                -> ofNearbyDate($mode,$last_id)
                -> where('type',0)
                -> orderBy('id','desc')
                -> able()
                -> visible()
                -> take($limit)
                -> get();

            return response()->json([

                // 获取的数据
                'data' => $nearby_tweets ? $this->tweetsNearbyTransformer->transformCollection($nearby_tweets->all()) : [],

                // 本次获取的总数量
                'count' => count($nearby_tweets),

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link' => count($nearby_tweets)
                    ? $request->url() .
                    '?limit=' . $limit .
                    '&mode=' . $mode .
                    '&citycode=' . $citycode .
                    '&adcode=' . $adcode .
                    '&last_id=' . ($mode == 1 ? $nearby_tweets -> first() -> id : $nearby_tweets -> last() -> id) // 最后一条信息的id
                    : null      // 如果数量为0，则不附带搜索条件
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 大家都在看 TODO 停用 注意 hasOneStatistics
     */
    public function watching()
    {
        try{
            // 获取当日日期
            $date = date('Ymd');

            // 获取每日推荐视频
            $push_data = TweetsPush::where('date',$date) -> take($this -> paginate) -> get();

            // 判断是否存在
            if(!$push_data->count()) return response()->json([],204);

            // 将字符串解析成数组
            $push_tweets = $push_data -> pluck('tweet_id') -> all();

            // 获取具体视频信息
            $data = Tweet::with('hasOneContent','belongsToUser')->able()->whereIn('id',$push_tweets)->get();

            // 判断用户登录情况
            $user = Auth::guard('api')->user();

            if($user){

                // 将用户记录存入 tweets_push_statistics 表
                TweetsPushStatistics::create([
                    'user_id'          => $user -> id,
                    'tweet_push_date'  => $date,
                    'time_add'         => getTime()
                ]);
            }

            // 返回数据
            return response()->json([

                // 所获取的数据
                'data'  => $this -> tweetsWatchingTransformer -> transformCollection($data -> all()),
                'count' => $push_data->count()
            ]);
        } catch (\Exception $e){
            return response()->json(['error' => $e -> getMessage()],$e -> getCode());
        }
    }

    /**
     * 大家都在搜
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        try{
            // 大家都在搜
            $hot = HotSearch::recommend()->take(6)->get();

            return response()->json([
                'data'=>$hot->count() ? $this->hotSearchTransformer->transformCollection($hot->all()) : [],
            ],200);
        }catch(ModelNotFoundException $e){
            return response()->json(['error'=>'bad_request'],403);
        }catch(\Exception $e){
            return response()->json(['error'=>'bad_request'],403);
        }
    }

    /**
     * 发现页面 精选媒体
     *
     * @param Request $request
     * @return array
     */
    public function featured(Request $request)
    {
        $page = (int)$request -> get('page', 1);
//
//        $top_count = 5;
//
//        $top_data = [];
//
//        // 第一次请求 有置顶
//        if(1 == $page) {
//
//            $top = FeaturedMedia::with(['belongsToUser' => function($q){
//                $q -> select('id', 'avatar', 'nickname', 'verify', 'verify_info');
//            }])
//                -> where('top', '<>', 0)
//                -> orderBy('sort')
//                -> take($top_count)
//                -> get(['user_id']);
//
//            $top_data = $this -> featuredMediaTransformer -> transformCollection($top -> all());
//        }
//
//        // 普通
//        $media_count = FeaturedMedia::with(['belongsToUser' => function($q){
//            $q -> select('id', 'avatar', 'nickname', 'verify', 'verify_info');
//        }])
//            -> where('top', 0)
//            -> orderBy('sort');
//
//        $media = $media_count -> forpage($page, $this -> paginate)
//            -> get(['user_id']);
//
//        return [
//            'top'           => $top_data,
//            'media'         => $this -> featuredMediaTransformer -> transformCollection($media -> all()),
//            'page_count'    => ceil($media_count -> count()/$this -> paginate)
//        ];


        //精选用户

        $top = Cache::remember('top'.$page, 5, function () use ($page){

            $top = User::where('active',2)
                ->where('is_vip','!=',0)
                ->where('verify','!=',0)
                ->forPage($page,$this->paginate)
                ->get(['id', 'avatar', 'nickname', 'verify', 'verify_info']);

            $top_data = $this -> featuredMediaTransformer -> ptransform($top);

            return [
                'top'           => $top_data,
                'page_count'    => ceil($top -> count()/$this -> paginate)
            ];
        });

        return $top;

    }

}