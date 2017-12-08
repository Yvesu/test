<?php

namespace App\Http\Controllers\NewAdmin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\TweetBlocking;
use App\Models\TweetBlockingReason;
use App\Models\TweetCheckLog;
use App\Models\TweetReply;
use App\Models\TweetTrophyLog;
use App\Models\User;
use App\Models\ChannelTweet;
use App\Models\Tweet;
use Carbon\Carbon;
use App\Http\Transformer\TweetDetailsTransformer;
use App\Http\Transformer\TweetHotReplyTransformer;
use App\Http\Transformer\VideoIndexTransformer;
use App\Http\Transformer\VideoIndexUndeterminedTransformer;
use App\Http\Transformer\VideoIndexForbidTransformer;
use App\Http\Transformer\TrophyLogTransformer;
use App\Http\Transformer\TweetCheckTransformer;
use App\Http\Transformer\VideoNoCheckTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use DB;
use Auth;

// TODO 调试期使用Controller，后期换回 BaseSessionController（自己封装的访问权限管理，通过路由管理）
class VideosController extends Controller
{

    private $paginate = 8;
    private $tweetDetailsTransformer;
    private $tweetHotReplyTransformer;
    private $videoIndexUndeterminedTransformer;
    private $videoIndexForbidTransformer;
    private $videoIndexTransformer;
    private $trophyLogTransformer;
    private $tweetCheckTransformer;
    private $videoNoCheckTransformer;

    public function __construct(
        TweetDetailsTransformer $tweetDetailsTransformer,
        TweetHotReplyTransformer $tweetHotReplyTransformer,
        VideoIndexUndeterminedTransformer $videoIndexUndeterminedTransformer,
        VideoIndexForbidTransformer $videoIndexForbidTransformer,
        VideoIndexTransformer $videoIndexTransformer,
        TrophyLogTransformer $trophyLogTransformer,
        TweetCheckTransformer $tweetCheckTransformer,
        VideoNoCheckTransformer $videoNoCheckTransformer
    )
    {
        $this -> tweetDetailsTransformer = $tweetDetailsTransformer;
        $this -> tweetHotReplyTransformer = $tweetHotReplyTransformer;
        $this -> videoIndexUndeterminedTransformer = $videoIndexUndeterminedTransformer;
        $this -> videoIndexForbidTransformer = $videoIndexForbidTransformer;
        $this -> videoIndexTransformer = $videoIndexTransformer;
        $this -> trophyLogTransformer = $trophyLogTransformer;
        $this -> tweetCheckTransformer = $tweetCheckTransformer;
        $this -> videoNoCheckTransformer = $videoNoCheckTransformer;
    }

    /**
     *  通过视频的主页
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 登录信息
//        $user = Auth::guard('api') -> user();

        $page = $request -> get('page', 1);

        // 0待审批（视频审查），1推荐视频（有所属频道），2屏蔽，4待定
        $active = (int)$request -> get('active', 4);

        // 搜索条件
        $search_keywords = $request -> get('keywords', '');
        $search_type = $request -> get('type', '');
        $search_time = $request -> get('time', '');
        $search_duration = $request -> get('duration', '');
        $search_browse = $request -> get('browse', '');
        $operator = $request->get('operator_id',null);

        // 视频总条数
        $count = Tweet::get(['id', 'created_at'])->count();
        $today_count = Tweet::where('created_at', '>', date('Y-m-d H:i:s', getTime()))->get(['id', 'created_at'])->count();

        // 判断动态类型,0待审批（视频审查），1推荐视频（有所属频道），2屏蔽，4待定
        if(4 == $active) {

            $tweets = Tweet::has('belongsToCheck') -> with(['belongsToCheck', 'hasOneContent']);

        } elseif(0 == $active) {
            return response() -> json([
                'tweets' => [],
                'count'  => $count,
                'today_count'  => $today_count,
            ], 200);
//
        } elseif(2 == $active) {

            $tweets = Tweet::has('belongsToReason') -> with('belongsToReason','hasOneContent','belongsToReasonAdmin');

        } elseif(1 == $active) {

            $tweets = Tweet::has('hasManyChannelTweet');
        }

        $tweets = $tweets -> where('original',0)->where('type','=',0)
            -> ofNewSearch($search_keywords, $search_type, $search_time, $search_duration, $search_browse,$operator)
            -> where('active', $active)
            -> forPage($page, $this -> paginate)
            -> get(['id', 'user_id', 'screen_shot', 'video', 'duration', 'created_at', 'browse_times']);

        // 根据动态类型过滤数据
        if(4 == $active) {

            $data = $this->videoIndexUndeterminedTransformer->transformCollection($tweets -> all());

        } elseif(0 == $active) {

//            $data = $this->videoNoCheckTransformer->transformCollection($tweets -> all());
            $data = [];
        } elseif(2 == $active) {

            $data = $this->videoIndexForbidTransformer->transformCollection($tweets -> all());

        } elseif(1 == $active) {

            $data = $this->videoIndexTransformer->transformCollection($tweets -> all());
        }



        return response() -> json([
            'tweets' => $data,
            'count'  => $count,
            'today_count'  => $today_count,
            'batchBehavior'=>   [
                'dotype' => '推荐',
                'dohot'  => '热门',
                'cancelhot' => '取消热门',
                'cs'=> '取消屏蔽',
                'delete' => '删除',
            ],
        ], 200);
    }

    /**
     * 动态详情
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function details($id, Request $request)
    {
        try {
//            if(!$id = (int)$request -> get('id'))
//                return response()->json(['error'=>'no_id', 403]);

            # 1:上一个 2:下一个
            // 0待审批（视频审查），1推荐视频（有所属频道），2屏蔽，4待定
//            $active = $request -> get('active', null);
//            if(is_null($active)){
//                return response()->json(['message'=>'数据不合法'],200);
//            }

            // 搜索条件
            $search_keywords = $request -> get('keywords', '');
            $search_type = $request -> get('type', '');
            $search_time = $request -> get('time', '');
            $search_duration = $request -> get('duration', '');
            $search_browse = $request -> get('browse', '');
            $operator = $request->get('operator_id',null);
            // 上一个或下一个 id
            $tweets_data = Tweet::with(['hasOneContent','belongsToUser'=>function($q){
                $q -> select('id', 'advertisement');
            }])->find($id);
            $active = $tweets_data->active;
            $next_tweet = $this -> nextPrev($id,2,$active,$search_keywords, $search_type, $search_time, $search_duration, $search_browse,$operator);
            $prev_tweet = $this -> nextPrev($id,1,$active,$search_keywords, $search_type, $search_time, $search_duration, $search_browse,$operator);


            $page = (int)$request -> get('page', 1);

            // 获取要查询的动态详情
//            $tweets_data = Tweet::with([
//                'hasOneContent',
//                'belongsToUser'=>function($q){
//                    $q -> select('id', 'advertisement');
//                }])
//                -> able()
//                -> findOrFail($id);

            // 取8条评论普通评论，按时间排序
//            $replys_count = TweetReply::with(['belongsToUser' => function($q){
//                $q -> select('id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info');
//            }, 'belongsToReply' => function($q) use($id) {
//                $q -> with(['belongsToUser' => function($q) {
//                    $q -> select(['id','nickname']);
//                }])-> where('status', 0);
//            }])
//                -> where('tweet_id',$id)
//                -> orderBy('id','desc')
//                -> status();

            $replys_count = TweetReply::with(['belongsToUser'=>function($q){
                $q->select('id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info');
            }])->where('tweet_id',$id)->where('reply_id','=',null)->Status()->orderBy('created_at','desc')->limit(8);
            $replys = $replys_count -> forPage($page, $this -> paginate)
                -> get(['id', 'user_id', 'reply_id', 'content', 'created_at', 'anonymity', 'like_count', 'grade']);


            // 评论总数量
            $count = $replys_count->count();

            // 获取颁奖嘉宾信息集合
            $trophy_count = TweetTrophyLog::where('tweet_id', $id)
                -> orderBy('id', 'desc')
                -> count();
            // 取出热评信息，目前暂定20个赞以上为热评
//            $hot_replys = TweetReply::with(['belongsToUser' => function($q){
//                $q -> select('id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info');
//            }, 'belongsToReply' => function($q) use($id) {
//                $q -> with(['belongsToUser' => function($q) {
//                    $q -> select(['id','nickname']);
//                }])-> where('status', 0);
//            }])
            $hot_replys = TweetReply::with(['belongsToUser'=>function($q){
                $q->select('nickname');
            }])
                -> where('tweet_id', $id)
                -> where('reply_id','=',null)
                -> where('status', 0)
                -> where('like_count', '>' , 20)
                -> orderBy('like_count', 'DESC')
                -> take(3)
                -> get(['id', 'reply_id', 'user_id', 'content', 'created_at', 'anonymity', 'like_count', 'grade']);

//dd(($hot_replys->count()==0)?'':$this->tweetHotReplyTransformer->transformCollection($hot_replys->all()));
            // 返回数据
//dd($tweets_data);
            if($active == 2){
                $behavior = [
                    'delete'=>'删除',
                    'cancelshield' => '取消屏蔽',
                ];
            }else{
                $behavior = [
                    'dotype'=>'推荐',
                    'pass' => '待定',
                    'doshield' => '屏蔽',
                ];
            }
            return [

                // 该动态详情与发表用户详情
                'tweets_data'  => $this -> tweetDetailsTransformer->transform($tweets_data),

                // 颁奖嘉宾总数量
                'trophy_count' => $trophy_count,

                // 评论总数量
                'replys_count' => $count,

                // 热门评论
                'hot_replys'   => ($hot_replys->count()==0)?'':$this->tweetHotReplyTransformer->transformCollection($hot_replys->all()),

                // 评论
                'replys'       => ($replys->count()==0)?'':$this->tweetHotReplyTransformer->transformCollection($replys->all()),


                // 评论总页码
                'page_count'   => ceil($count/$this->paginate),

                // 操作总数量
                'check_count' => TweetCheckLog::where('tweet_id', $id) -> count(),

                // 下一个id
                'next_id'      => $next_tweet ? $next_tweet -> id : '',

                // 上一个id
                'prev_id'      => $prev_tweet ? $prev_tweet -> id : '',

                // 操作
                'behavior' => $behavior
            ];
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 上一个 下一个 id
     * @param $id
     * @param $sort 1上一个，2下一个
     * @param $active  0待审批（视频审查），1推荐视频（有所属频道），2屏蔽，4待定
     * @param $search_keywords
     * @param $search_type
     * @param $search_time
     * @param $search_duration
     * @param $search_browse
     * @return mixed
     */
    protected function nextPrev($id,$sort,$active,$search_keywords, $search_type, $search_time, $search_duration, $search_browse,$operator){

        // 判断动态类型,0待审批（视频审查），1推荐视频（有所属频道），2屏蔽，4待定
        if(4 == $active) {

            $tweets = Tweet::has('belongsToCheck') -> with(['belongsToCheck', 'hasOneContent']);

        } elseif(0 == $active) {

            $tweets = Tweet::with(['belongsToUser' => function($q){
                $q -> select('id', 'nickname');
            },'hasOneContent']);

        } elseif(2 == $active) {

            $tweets = Tweet::has('belongsToReason') -> with('belongsToReason','hasOneContent','belongsToReasonAdmin');

        } elseif(1 == $active) {

            $tweets = Tweet::has('hasManyChannelTweet');
        }
        $tweets = $tweets -> with('belongsToPhone') -> where('original',0)
            -> ofNewSearch($search_keywords, $search_type, $search_time, $search_duration, $search_browse,$operator)
            -> where('active', $active);

        // sort  1上一个，2下一个
        if(2 == $sort){
            $tweet = $tweets -> where('id', '>', $id) -> orderBy('id') -> first();
        } else {
            $tweet = $tweets -> where('id', '<', $id) -> orderBy('id', 'DESC') -> first();
        }

        return $tweet;
    }

    /**
     * 评论
     * @param Request $request
     * @return array
     */
    public function reply(Request $request) {

        try {
            if(!$id = (int)$request -> get('id'))
                return response()->json(['error'=>'no_id', 403]);

            $page = $request -> get('page', 1);

            $replys_count = TweetReply::with(['belongsToUser' => function($q){
                $q -> select('id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info');
            }, 'belongsToReply' => function($q) use($id) {
                $q -> with(['belongsToUser' => function($q) {
                    $q -> select(['id','nickname']);
                }]) -> where('status', 0);
            }])
                -> where('tweet_id',$id)
                -> orderBy('id','desc')
                -> status();

            // 取8条评论普通评论，按时间排序
            $replys = $replys_count
                -> forPage($page, $this -> paginate)
                -> get(['id', 'user_id', 'reply_id', 'content', 'created_at', 'anonymity', 'like_count', 'grade']);

            // 返回数据
            return [

                // 评论
                'data'       => $this->tweetHotReplyTransformer->transformCollection($replys->all()),

                // 评论总页码
                'page_count'   => ceil($replys_count->count()/$this->paginate),
            ];

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 荣誉记录
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function trophy(Request $request) {

        if(!$id = (int)$request -> get('id'))
            return response()->json(['error'=>'no_id', 403]);

        $page = $request -> get('page', 1);

        // 获取颁奖嘉宾信息集合
        $trophy_users = TweetTrophyLog::with(['belongsToUser' => function($q){
            $q -> select('id','nickname','verify');
        }, 'belongsToTrophy' => function($q){
            $q -> select('id','name');
        }])
            -> where('tweet_id', $id)
            -> orderBy('id', 'desc');

        $trophy = $trophy_users
            -> forPage($page, $this -> paginate)
            -> get(['anonymity', 'from']);

        // 返回数据
        return [

            // 评论
            'data'       => $this -> trophyLogTransformer -> transformCollection($trophy -> all()),

            // 评论总页码
            'page_count'   => ceil($trophy_users->count()/$this->paginate),
        ];
    }

    /**
     * 视频的操作记录
     * @param Request $request
     * @return array
     */
    public function check(Request $request)
    {

        try {
            if(!$id = (int)$request -> get('id'))
                return response()->json(['error'=>'no_id', 403]);

            $page = $request -> get('page', 1);

            $check_count = TweetCheckLog::with('belongsToCheckAdmin')
                -> where('tweet_id',$id)
                -> orderBy('id','desc');

            // 取8条操作记录，按时间排序
            $check = $check_count
                -> forPage($page, $this -> paginate)
                -> get();

            // 返回数据
            return [

                // 评论
                'data'       => $this -> tweetCheckTransformer->transformCollection($check->all()),

                // 评论总页码
                'page_count'   => ceil($check_count->count()/$this->paginate),
            ];

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 推广记录 TODO 具体未明确，确定后建表写接口
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function advertising(Request $request)
    {
        try{
            if(!$id = (int)$request -> get('id'))
                return response()->json(['error'=>'no_id', 403]);

            $page = (int)$request -> get('page', 1);

            //

        }catch(\Exception $e){
            return response() -> json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 现用推荐信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommendInfo(Request $request)
    {
        // 登录信息
//        $user = Auth::guard('api') -> user();

        $channel = Channel::active()->orderBy('sort')->get(['id','name']);

        return response() -> json([
            'channel' => $channel,
        ], 200);
    }

    /**
     * 推荐频道处理
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommendDispose(Request $request)
    {
        try{
            // 管理员信息
            $admin = Auth::guard('api')->user();

            // 动态id array
            if(empty($tweet_ids = $request -> get('tweet')) || !is_array($tweet_ids))
                return response() -> json(['error'=>'bad_request', 403]);

            // 推荐类目的id array
            if(empty($recommend_ids = $request -> get('recommend')) || !is_array($recommend_ids))
                return response() -> json(['error'=>'bad_request', 403]);
            $recommend_ids = array_slice($recommend_ids,0,2,true);
            $time = new Carbon();

            DB::beginTransaction();

            // 处理
            foreach($tweet_ids as $tweet_id){

                foreach($recommend_ids as $recommend_id){
                    ChannelTweet::where('tweet_id','=',$tweet_id)->delete();
                    ChannelTweet::create([
                        'channel_id'    => $recommend_id,
                        'tweet_id'      => $tweet_id,
                        'created_at'    => $time,
                        'updated_at'    => $time,
                    ]);

                    // 操作记录
                    TweetBlocking::where('tweet_id','=',$tweet_id)->delete();
                    TweetCheckLog::create([
                        'tweet_id'      => $tweet_id,
                        'active'        => 1,
                        'admin_id'      => $admin->id,
                        'time_add'      => $time,
                        'time_update'   => $time
                    ]);
                }
            }

            DB::commit();

            return response() -> json([
                'status' => 'ok',
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response() -> json(['error'=>'bad_request', 404]);
        }
    }

    /**
     * 屏蔽视频的原因选择
     *
     * @param Request $request
     * @return mixed
     */
    public function forbidReasons(Request $request)
    {

        $reasons = TweetBlockingReason::active() -> get(['id', 'reason']);

        return $reasons -> all();
    }

    /**
     * 视频的禁止操作
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forbidDone(Request $request)
    {
        try{
            $tweet_id = (int)$request -> get('tweet_id');
            $reason_id = (int)$request -> get('reason_id');
            $reason_content = $request -> get('reason', '');

            if(TweetBlocking::where('tweet_id', $tweet_id) -> first())
                return response() -> json(['error' => 'already_forbid'], 403);

            // 管理员信息
            $admin = Auth::guard('api')->user();

            $tweet = Tweet::findOrfail($tweet_id);
            $reason = TweetBlockingReason::findOrfail($reason_id);

            $time = getTime();

            DB::beginTransaction();

            $tweet -> update(['active' => 2]);

            TweetBlocking::create([
                'reason_id' => $reason_id,
                'reason' => $reason_content,
                'tweet_id' => $tweet_id,
                'admin_id' => $admin->id,
                'time_add' => $time,
                'time_update' => $time
            ]);

            // 操作记录
            TweetCheckLog::create([
                'tweet_id'      => $tweet_id,
                'active'        => 2,
                'admin_id'      => $admin->id,
                'time_add'      => $time,
                'time_update'   => $time
            ]);

            DB::commit();

            return response() -> json(['status' => 'ok'], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response() -> json(['error'=>'bad_request', 404]);
        }
    }

    /**
     * 视频的删除操作
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        try{
            $tweet_id = (int)$request -> get('tweet_id');

            // 管理员信息
            $admin = Auth::guard('api')->user();

            $tweet = Tweet::findOrfail($tweet_id);

            $time = getTime();

            DB::beginTransaction();

            $tweet -> update(['active' => 5]);

            // 操作记录
            TweetCheckLog::create([
                'tweet_id'      => $tweet_id,
                'active'        => 5,
                'admin_id'      => $admin->id,
                'time_add'      => $time,
                'time_update'   => $time
            ]);

            DB::commit();

            return response() -> json(['status' => 'ok'], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response() -> json(['error'=>'bad_request', 404]);
        }
    }


}
