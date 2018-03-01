<?php
namespace App\Api\Controllers;

use App\Api\Transformer\FilmRecommendTransformer;
use App\Models\ActivityFilmRecommend;
use App\Models\Filmfests;
use App\Models\TweetActivity;
use Illuminate\Support\Facades\Cache;
use App\Models\Activity;
use App\Models\Tweet;
use App\Models\TweetReply;
use App\Models\AdvertisingRotation;
use App\Api\Transformer\ZxHomeImagesTransformer;
use App\Api\Transformer\TweetsActivityTransformer;
use App\Api\Transformer\TweetActivityRepliesTransformer;
use App\Api\Transformer\Discover\HotActivityTransformer;
use App\Api\Transformer\ActivityTransformer;
use Illuminate\Http\Request;
use CloudStorage;
use Auth;

/**
 * 广告相关
 *
 * Class AdvertisingController
 * @package App\Api\Controllers
 */
class ActivityController extends BaseController
{

    private $zxHomeImagesTransformer;
    private $hotActivityTransformer;
    private $activityTransformer;

    // 动态详情 details方法
    protected $tweetsActivityTransformer;
    protected $tweetActivityRepliesTransformer;

    protected $filmRecommendTransformer;

    private $paginate = 20;

    public function __construct(
        ZxHomeImagesTransformer $zxHomeImagesTransformer,
        TweetsActivityTransformer $tweetsActivityTransformer,
        HotActivityTransformer $hotActivityTransformer,
        ActivityTransformer $activityTransformer,
        TweetActivityRepliesTransformer $tweetActivityRepliesTransformer,
        FilmRecommendTransformer $filmRecommendTransformer

    )
    {
        $this -> zxHomeImagesTransformer = $zxHomeImagesTransformer;
        $this -> tweetsActivityTransformer = $tweetsActivityTransformer;
        $this->hotActivityTransformer = $hotActivityTransformer;
        $this->activityTransformer = $activityTransformer;
        $this->tweetActivityRepliesTransformer = $tweetActivityRepliesTransformer;
        $this -> filmRecommendTransformer = $filmRecommendTransformer;
    }

    /**
     * 赛事页面的轮播广告
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rotation()
    {
        try{
            $ads = Cache::remember('advertising_rotation', 1440, function() {

                return AdvertisingRotation::recommend()->take(5)->get(['type','type_id','image','url']);
            });

            return response() -> json([
                'data' => $ads->count() ? $this->zxHomeImagesTransformer->transformCollection($ads->all()) : [],
            ], 200);

        } catch (\Exception $e) {
            return response() -> json(['error'=>'not_found'], 404);
        }
    }

    /**
     * 赛事页面的列表 不同类型
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $type = $request -> get('type', 1);
            $page = (int)$request -> get('page', 1);

            // 1推荐，2热门，3最新
            if(!in_array($type, [1,2,3])) return response() -> json(['error'=>'bad_request'], 403);

            // 判断是否为推荐
            if(1 == $type) {
                return $this -> activeFilmRecommend($page);
            }

            // 缓存,热门和最新
                if ($type == 2){
                    $data = Cache::remember('activity_list_'.$type.'_'.$page, 5, function() use($page, $type) {
                    // 获取赛事（原活动）数据
                    $data = Activity::with(['belongsToUser' => function($q){
                        $q -> select('id','nickname','avatar','cover','verify','signature','verify_info');
                    }, 'hasManyTweets'=>function($q){
                        $q->select(['tweet_id','screen_shot']);
                    }])
                        -> active()
                        -> ofType($type)
                        -> ofExpires()
                        -> paginate($this->paginate, ['id','user_id','bonus','comment','expires','time_add','icon','work_count'], 'page', $page);

                    return [
                        'data' => $this -> hotActivityTransformer->transformCollection($data->all()),
                        'page_count' => $data -> toArray()['last_page']
                    ];
                    });
                    return $data;
                }


                if ($type == 3){

                    $data_1 = Activity::active()
                        ->ofType($type)
                        ->ofExpires()
                        -> forPage($page,$this->paginate)
                        -> pluck('id');

                    $arr = $data_1->all();

                    $user = Auth::guard('api')->user();

                    if ($user){
                           $data_2 = Activity::where('user_id',$user->id)
                               -> where('active',0)
                               -> ofType($type)
                               -> ofExpires()
                               -> forPage($page,$this->paginate)
                               -> pluck('id');
                           $arr = array_merge($data_2->all(),$data_1->all());
                    }

                    $data = Activity::with(['belongsToUser' => function($q){
                        $q -> select('id','nickname','avatar','cover','verify','signature','verify_info');
                    }, 'hasManyTweets'=>function($q){
                        $q->select(['tweet_id','screen_shot'])->whereIn('active',[0,1]);
                    }])
                        ->whereIn('id',$arr)
                        ->orderBy('time_add','desc')
                        -> paginate($this->paginate, ['id','user_id','bonus','comment','expires','time_add','icon','work_count'], 'page', $page);

                    return [
                        'data' => $this -> hotActivityTransformer->transformCollection($data->all()),
                        'page_count' => $data -> toArray()['last_page']
                    ];
                }


        }catch(\Exception $e){
            return response() -> json(['error'=>'not_found'], 404);
        }
    }

    private function activeFilmRecommend($page)
    {
        $time = time();

        //电影节
//        $film_ids_obj = ActivityFilmRecommend::where('type','1')
//            ->where('expires','>',$time)
//            ->forPage($page,)
//            ->pluck('work_id');

//        $film_ids_arr = $film_ids_obj -> all();

//        $film = Filmfests::whereIn('id',$film_ids_arr)
//            ->get(['id','name','cover','url','cost','count','time_end']);

//        if ($film->count()>10){
//            $film = $film->random(10);
//        }

//        $number = $this->paginate - $film->count();

        //获取推荐
        $activity_ids_obj = ActivityFilmRecommend::where('type','0')
            ->where('expires','>',$time)
            ->forPage($page,$this->paginate)
            ->pluck('work_id');

        $activity_ids_arr = $activity_ids_obj -> all();

        $film_ids = Activity::where('is_child',1)->whereIn('id',$activity_ids_obj->all())->pluck('id');

        //web端发起的赛事
        $film_ids =$film_ids->all();

        //将web过滤
        foreach( $activity_ids_arr  as $k=>$v) {
            if( in_array($v,$film_ids,TRUE) ){
                unset($activity_ids_arr[$k]);
            }
        }

        //赛事
        $data = Activity::with(['belongsToUser','hasManyTweets.belongsToUser' => function ($q){
            $q -> select(['id','avatar']);
        }])
            ->whereIn('id',$activity_ids_arr)
            ->get(['id','user_id','bonus','comment','expires','time_add','icon','users_count']);

        if ($data->count()>10){
            $data = $data->random(10);
        }

        //获取web端的赛事
        $film = Activity::with(['filmfest','belongsToUser','hasManyTweets.belongsToUser'=> function ($q){
            $q -> select(['id','avatar']);
        }])
            ->whereIn('id',$film_ids)
            ->get();

        return [
            'film'  => $this ->filmRecommendTransformer ->transformCollection($film->all()),
            'data'  => $this -> activityTransformer -> transformCollection($data->all()),
        ];
    }

    /**
     * 赛事页面的列表 推荐
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function recommend($page)
    {
        try{
            // 缓存
            $data = Cache::remember('activity_list_1_'.$page, 5, function() use($page) {
                // 获取赛事（原活动）数据
                $data = Activity::with(['belongsToUser','hasManyTweets.belongsToUser' => function ($q){
                    $q -> select(['id','avatar']);
                }])
                    -> ofExpires()
                    -> ofType(1)
                    -> active()
                    -> paginate($this->paginate, ['id','user_id','bonus','comment','expires','time_add','icon','users_count'], 'page', $page);

                return [
                    'data' => $this -> activityTransformer -> transformCollection($data->all()),
                    'page_count' => $data -> toArray()['last_page']
                ];
            });

            return $data;

        } catch (\Exception $e) {
            return response() -> json(['error'=>'not_found'], 404);
        }
    }

    /**
     * 具体赛事的动态详情
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function tweetDetails(Request $request)
    {
        try {
            // 参数获取
            $tweet_id = (int)$request -> get('tweet_id');
            $type = $request -> get('type');    // 类型，1排行榜，2全部

            if(!in_array($type, [1,2]))
                return response() -> json(['error' => 'bad_request'], 403);

            // 排序方式
            $order = 1 == $type ? 'like_count' : 'id';

            $tweet = TweetActivity::where('tweet_id', $tweet_id) -> firstOrFail();

            // 上一个 下一个 的id
            $prevNextIds = TweetActivity::where('activity_id', $tweet -> activity_id)
                -> orderBy($order, 'DESC')
                -> pluck('tweet_id');

            foreach($prevNextIds as $key => $value){
                if($value == $tweet -> tweet_id)
                    $prevNextId = $key;
            }

            $prev = $prevNextId - 1;
            $next = $prevNextId + 1;

            $prev_id = isset($prevNextIds[$prev]) ? $prevNextIds[$prev] : 0;
            $next_id = isset($prevNextIds[$next]) ? $prevNextIds[$next] : 0;

            // 排名
            $rank = TweetActivity::where('activity_id', $tweet -> activity_id)
                -> orderBy('like_count', 'DESC')
                -> pluck('tweet_id');

            foreach($rank as $key => $value){
                if($value == $tweet -> tweet_id)
                    $ranking = ++ $key;
            }

            // 获取要查询的动态详情
            $tweets_data = Tweet::with(['hasOneContent'])
                -> able()
                -> findOrFail($tweet -> tweet_id);

            // 取出10条热评信息，按点赞量排序
            $hot_replys = TweetReply::with(['belongsToUser' => function($q){
                $q -> select('id', 'nickname');
            }])
                -> where('tweet_id', $tweet -> tweet_id)
                -> where('anonymity', 0)    // 公开
                -> status()
                -> orderBy('like_count', 'DESC')
                -> take(10)
                -> get(['id', 'user_id', 'content']);

            $reply = [];

            if($hot_replys->first()) {
                foreach($hot_replys as $value) {
                    $reply[] = [
                        'nickname'  => $value -> belongsToUser -> nickname,
                        'content'  => $value -> content,
                    ];
                }
            }

            $user = Auth::guard('api')->user();

            // 动态浏览次数 +1
            $tweetPlay = new TweetPlayController();
            $tweetPlay -> countIncrement($tweet -> tweet_id, $user);

            // 返回数据
            return [

                // 该动态详情与发表用户详情
                'tweets_data' => $this -> tweetsActivityTransformer->transform($tweets_data),

                // 名次
                'ranking'      =>  $ranking,

                // 上一个
                'prev_id'      => $prev_id,

                // 下一个
                'next_id'      => $next_id,

                // 热门评论
                'hot_replys'  =>  $reply,
            ];
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 赛事动态详情的评论
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tweetReply(Request $request)
    {
        $page = (int)$request -> get('page', 1);

        $tweet_id = (int)$request -> get('tweet_id');

        //
        $reply = TweetReply::with(['belongsToUser' => function($q) {
            $q -> select(['id','nickname','avatar']);
        }, 'belongsToReply' => function($q) use($tweet_id) {
            $q -> with(['belongsToUser' => function($q) {
                $q -> select(['id','nickname','avatar']);
            }])-> where('tweet_id', $tweet_id) -> where('status', 0);
        }])
            -> where('tweet_id', $tweet_id)
            -> where('status', 0)
            -> orderBy('like_count', 'DESC')
//            -> where('reply_id','!=',null)
            -> get();

       if(!$reply->toArray()){
           $reply = TweetReply::with(['belongsToUser' => function($q) {
               $q -> select(['id','nickname','avatar']);
           }]) -> where('tweet_id', $tweet_id)
               -> where('status', 0)
               -> orderBy('like_count', 'DESC')
               -> where('reply_id','=',null)
               -> get();
       }
       $data = $this->tweetActivityRepliesTransformer->transformCollection($reply-> forPage($page, $this->paginate)->all());
        $data = array_values($data);
       return [
            'page_count' => ceil($reply -> count()/$this->paginate),
            'data' => $data,
        ];

    }

    /**
     * 附近的赛事
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function nearbyActivity(Request $request)
    {
        if (is_null($lgt = $request->get('lgt'))   ||  is_null( $lat = $request ->get('lat'))) return response()->json(['message'=>'bad_request'],403);

        $big_lgt = $lgt + 0.5;

        $small_lgt = $lgt - 0.5;

        $big_lat = $lat + 0.5;

        $small_lat = $lat - 0.5;

        $page = $request->get('page',1);

        $data_1 = Activity::ofExpires()
            -> ofType(2)
            -> active()
            -> whereBetween('lgt',[$small_lgt,$big_lgt])
            -> whereBetween('lat',[$small_lat,$big_lat])
            -> forPage($page,$this->paginate)
            -> pluck('id');

            $arr = $data_1->all();

            $user = Auth::guard('api')->user();

            if ($user){
                $data_2 = Activity::where('user_id',$user->id)
                    -> ofExpires()
                    -> ofType(3)
                    -> where('active',0)
                    -> whereBetween('lgt',[$small_lgt,$big_lgt])
                    -> whereBetween('lat',[$small_lat,$big_lat])
                    -> forPage($page,$this->paginate)
                    -> pluck('id');
                $arr = array_merge($data_2->all(),$data_1->all());
            }

            $data = Activity::with(['belongsToUser' => function($q){
                $q -> select('id','nickname','avatar','cover','verify','signature','verify_info');
            }, 'hasManyTweets'])
                ->whereIn('id',$arr)
                ->orderBy('time_add','desc')
                -> paginate($this->paginate, ['id','user_id','bonus','comment','expires','time_add','icon','work_count'], 'page', $page);

            return [
                'data' => $this -> hotActivityTransformer->transformCollection($data->all()),
                'page_count' => $data -> toArray()['last_page']
            ];

    }
}
