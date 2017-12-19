<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ActivityTransformer;
use App\Api\Transformer\FragCollectTransformer;
use App\Api\Transformer\MakeFiterTransformer;
use App\Api\Transformer\MakeTemplateFileDetailsTransformer;
use App\Api\Transformer\NewFragmentSearchTransformer;
use App\Api\Transformer\NewTemplateSearchTransformer;
use App\Api\Transformer\NewTweetChannelTransformer;
use App\Api\Transformer\NewTweetSearchTransformer;
use App\Api\Transformer\NewUserSearchTransformer;
use App\Api\Transformer\SearchTopicsTransformer;
use App\Facades\CloudStorage;
use App\Models\Activity;
use App\Models\Blacklist;
use App\Models\Fragment;
use App\Models\Friend;
use App\Models\Keywords;
use App\Models\Make\MakeAudioEffectFile;
use App\Models\Make\MakeAudioFile;
use App\Models\Make\MakeEffectsFile;
use App\Models\Make\MakeFilterFile;
use App\Models\Make\MakeTemplateFile;
use App\Models\NoExitWord;
use App\Models\SensitiveWord;
use App\Models\Topic;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use DB;

class AllSearchController extends Controller
{
    //默认的页数
    protected $paginate = 20;

    private $newUserSearchTransformer;

    private $makeFiterTransformer;

    private $activityTransformer;

    private $newTweetsSearchTransformer;

    private $searchTopicsTransformer;

    private $newTemplateSearchTransformer;

    private $newFragmentSearchTransformer;

    public function __construct
    (
        NewUserSearchTransformer $newUserSearchTransformer,
        MakeFiterTransformer $makeFiterTransformer,
        ActivityTransformer $activityTransformer,
        NewTweetSearchTransformer $newTweetSearchTransformer,
        SearchTopicsTransformer $searchTopicsTransformer,
        NewTemplateSearchTransformer $newTemplateSearchTransformer,
        NewFragmentSearchTransformer $newFragmentSearchTransformer
    )
    {
        $this -> newUserSearchTransformer       =   $newUserSearchTransformer;
        $this -> makeFiterTransformer           =   $makeFiterTransformer;
        $this -> activityTransformer            =   $activityTransformer;
        $this -> newTweetsSearchTransformer     =   $newTweetSearchTransformer;
        $this -> searchTopicsTransformer        =   $searchTopicsTransformer;
        $this -> newTemplateSearchTransformer   =   $newTemplateSearchTransformer;
        $this -> newFragmentSearchTransformer   =   $newFragmentSearchTransformer;

        if (!Cache::get('keywords')){
            $keyword_obj = Keywords::distinct('keyword')->get(['keyword']);

            $arr = $keyword_obj->toArray();

            $keyword_arr = array_column($arr, 'keyword');

            Cache::put('keywords',$keyword_arr,'1450');
        }

        if (!Cache::get('sensitivewords')){
            $sensitiveword = SensitiveWord::distinct('sensitive_word')->get(['sensitive_word']);

            $arr = $sensitiveword->toArray();

            $sensitivewords = array_column($arr, 'sensitive_word');

            Cache::put('sensitivewords',$sensitivewords,'1450');
        }

        if(!Cache::get('noExitWord')){
            $noExitWord_obj =  NoExitWord::distinct('keyword')->get(['keyword']);

            $arr = $noExitWord_obj->toArray();

            $noExitWord_arr = array_column($arr, 'keyword');

            Cache::put('noExitWord',$noExitWord_arr,'60');
        }

    }

    public function search(Request $request)
    {
        try{
            //过滤数据
            if(!is_numeric($request->get('page',1)) || !is_numeric($request->get('type'))) return response()->json(['message'=>'bad_request'],403);
//dd($request->get('keyword'));
            //页数
            $page = $request->get('page',1);

            //类型
            $type = $request -> get('type');

            //搜索的内容
            $keyword = removeXSS($request->get('keyword'));

            //过滤
            if(empty($keyword) && strlen($keyword)<1 && $keyword!=0) return response()->json(['message'=>'keyword is empty'],403);

            //1用户  2动态   3话题  4赛事  5混合  6 滤镜  7模板  8音乐  9音频  10片段  11全部
            switch ($type){
                case 1 :
                    return $this->user($page,$keyword);
                case 2 :
                    return $this->tweet($page,$keyword);
                case 3 :
                    return $this->topic($page,$keyword);
                case 4 :
                    return $this->activity($page,$keyword);
                case 5 :
                    return $this->mixture($page,$keyword);
                case 6 :
                    return $this->filter($page,$keyword);
                case 7 :
                    return $this->template($page,$keyword);
                case 8 :
                    return $this->audio($page,$keyword);
                case 9 :
                    return $this->audioEffect($page,$keyword);
                case 10 :
                    return $this->fragment($page,$keyword);
                case 11;
                    return $this->allType($page,$keyword);
                default :
                    return response()->json(['message'=>'bad_request'],403);
            }

        }catch (\Exception $e){
            return response()->json(['message'=>'bad_request'],500);
        }
    }

    /**
     * 用户搜索
     * @param $page
     * @param $keyword
     * @return \Illuminate\Http\JsonResponse
     */
    private function user($page,$keyword)
    {
        $user = \Cache::remember('user:'.$keyword,'1',function() use ($page,$keyword){
            try{
                if( $this->sensitivity($keyword) === 'yes' ){        //不涉及敏感词汇
                //获取用户信息
                $user = Auth::guard('api')->user();

                $res = preg_match("/^1[3,4,5,7,8][0-9]{9}$/",$keyword);

                if (is_numeric($keyword) && strlen($keyword)==11 && $res){

                    if ($user) {        //手机号搜索  排除黑名单   且用户登录

                        $user_id = $user->id;

                        $blacklist  = Blacklist::where('from',$user_id)->pluck('to');

                        $user_info = User::WhereHas('hasOneLocalAuth', function ($q) use ($keyword) {
                            $q->where('username', $keyword);
                        })
                            ->where('is_phonenumber', 1)
                            ->where('search_phone', 1)
                            ->where('id','!=',$user_id)
                            ->whereIn('active', [1,2])
                            ->orderBy('fans_count','DESC')
                            ->whereNotIn('id',$blacklist)
                            ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);
                    }else{                  //手机号搜索   用户未登录

                        $user_info = User::WhereHas('hasOneLocalAuth', function ($q) use ($keyword) {
                            $q->where('username', $keyword);
                        })
                            ->where('is_phonenumber', 1)
                            ->where('search_phone', 1)
                            ->whereIn('active', [1,2])
                            ->orderBy('fans_count','DESC')
                            ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);

                    }

                    if (!$user_info->count()){

                        if($user){              //如果手机格式搜索不到  用户登录   排除黑名单
                            $user_id = $user->id;

                            $blacklist  = Blacklist::where('from',$user_id)->pluck('to');

                            $user_info = User::where('id','!=',$user_id)
                                ->orderBy('fans_count','DESC')
                                ->where('nickname','like','%'.$keyword.'%')
                                ->whereNotIn('id',$blacklist)
                                ->forPage($page,$this->paginate)
                                ->whereIn('active', [1,2])
                                ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);

                        }else{          //如果搜索不到   用户未登录

                            $user_info = User::where('nickname','like','%'.$keyword.'%')
                                ->orderBy('fans_count','DESC')
                                ->forPage($page,$this->paginate)
                                ->whereIn('active', [1,2])
                                ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);
                        }
                    }

                }else{

                    if($user){              //按昵称搜索   用户登录   排除黑名单
                        $user_id = $user->id;

                        $blacklist  = Blacklist::where('from',$user_id)->pluck('to');

                        $user_info = User::where('id','!=',$user_id)
                            ->orderBy('fans_count','DESC')
                            ->where('nickname','like','%'.$keyword.'%')
                            ->whereNotIn('id',$blacklist)
                            ->forPage($page,$this->paginate)
                            ->whereIn('active', [1,2])
                            ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);

                    }else{          //昵称搜索   用户未登录

                        $user_info = User::where('nickname','like','%'.$keyword.'%')
                            ->orderBy('fans_count','DESC')
                            ->forPage($page,$this->paginate)
                            ->whereIn('active', [1,2])
                            ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);
                    }

                }

                if($user_info->count()){

                    return response()->json([
                        'data'  =>  $this ->newUserSearchTransformer->transformCollection($user_info->toArray()),
                    ],200);

                }else{

                    return response()->json([
                        'data'  => [],
                    ],200);
                }
                }else{                                               //如果涉及敏感词汇
                    return response()->json(['message'=>'Sensitive vocabulary'],403);
                }

            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],403);
            }
        });

        return $user;
    }

    /**
     * 混合搜索
     * @param $page
     * @param $keyword
     * @return \Illuminate\Http\JsonResponse
     */
    private function mixture($page,$keyword)
    {
        $mixture = \Cache::remember('mixture:'.$keyword,'5',function() use ($keyword,$page){
            try{
                if( $this->sensitivity($keyword) === 'yes' ){        //不涉及敏感词汇
                    $with = [['belongsToUser',['nickname']],['belongsToFolder',['name']]];

                    $audio = MakeEffectsFile::where('active',1)
                        -> where('test_result',1)
                        ->orderBy('count','DESC')
                        ->where('name','like','%'.$keyword.'%')
                        -> selectListPageByWithAndWhereAndWhereHas($with, [], [], [], [$page, $this->paginate]);

                    return $this -> file($audio, 2);
                }else{                                               //如果涉及敏感词汇
                    return response()->json(['message'=>'Sensitive vocabulary'],403);
                }
            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });
        return $mixture;

    }

    /**
     * 编辑视频，效果文件详情 上面调用
     * @param $audio    集合
     * @param $type   1为自己下载过的，2为其他
     * @param array $integral_ids   自己下载过的收费文件id
     * @return \Illuminate\Http\JsonResponse
     */
    private function file($audio, $type, $integral_ids=[])
    {
        try{

            // 拼接地址
            foreach($audio as $key => $value){

                $value -> file_id = Crypt::encrypt($value->id);

                // 免费的文件和自己已经下载过的会有下载地址，收费的下载地址为空
                if(0 == $value->integral
                    || 1 == $type
                    || in_array($value->id, $integral_ids)){


                    $value -> address = CloudStorage::downloadUrl($value -> address);
                    $value -> high_address = CloudStorage::downloadUrl($value -> high_address);
                    $value -> super_address = CloudStorage::downloadUrl($value -> super_address);
                    $value -> integral = 0; // 已经下载过的则将下载所需金币变为0
                } else {

                    $value -> address = CloudStorage::downloadUrl($value -> address);
                    $value -> high_address = CloudStorage::downloadUrl($value -> high_address);
                    $value -> super_address = CloudStorage::downloadUrl($value -> super_address);

//                    $value -> address = '';
//                    $value -> high_address = '';
//                    $value -> super_address = '';
                }

                // 效果预览
//                $value -> address = CloudStorage::privateUrl_zip($value -> address);

                $value -> preview_address = CloudStorage::downloadUrl($value -> preview_address);

                // 效果封面
                $value -> cover = CloudStorage::downloadUrl($value -> cover);

                // 删除原id
                unset($value -> id);
            }

            return response() -> json(['data'=>$audio],200);

        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 滤镜搜索
     * @param $page
     * @param $keyword
     * @return \Illuminate\Http\JsonResponse
     */
    private function filter($page,$keyword)
    {
        $filter = \Cache::remember('filter:'.$keyword,'5',function() use ($keyword,$page){
            try{
                if( $this->sensitivity($keyword) === 'yes' ){        //不涉及敏感词汇
                    //  2017 11 27 修改
                    $audio = MakeFilterFile::with(['belongsToUser'=>function($q){
                        $q->select(['id','nickname']);
                    },'belongsToManyFolder'=>function($q){
                        $q->select(['name']);
                    },'belongsToTextureMixType'])
                        ->where('test_result',1)
                        ->where('active',1)
                        ->where('name','like','%'.$keyword.'%')
                        ->forpage($page,$this->paginate)
                        ->orderBy('count','DESC')
                        ->get(['id','user_id','name','cover','content','count','integral','time_add','texture','texture_mix_type_id']);

                    foreach($audio as $value){
                        $value -> cover = $value -> cover;
                    }

                    // 调用内部函数，返回数据
                    return response() -> json(['data'=>$this ->makeFiterTransformer->transformCollection($audio->toArray())],200);
                }else{                                               //如果涉及敏感词汇
                    return response()->json(['message'=>'Sensitive vocabulary'],403);
                }
            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });

        return $filter;

    }

    /**
     * 模板搜索
     * @param $page
     * @param $keyword
     * @return \Illuminate\Http\JsonResponse
     */
    private function template($page,$keyword)
    {
        $template = \Cache::remember('template:'.$keyword,'5',function() use ($keyword,$page){
            try{
                if( $this->sensitivity($keyword) === 'yes' ){        //不涉及敏感词汇
                    $details = MakeTemplateFile::with(['belongsToFolder'=>function($q){
                        $q->select(['id','name']);
                    }])
                        ->where('test_result',1)
                        -> where('active',1)
                        ->where('name','like','%'.$keyword.'%')
                        ->forpage($page,$this->paginate)
                        ->orderBy('count','DESC')
                        -> get( ['id','folder_id', 'name','preview_address','address','cover','duration','watch_count']);

                    // 调用内部函数，返回数据
                    return response() -> json([
                        'data'  => $this->newTemplateSearchTransformer->transformCollection($details->toArray()),
                    ], 200);
                }else{                                               //如果涉及敏感词汇
                    return response()->json(['message'=>'Sensitive vocabulary'],403);
                }

            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });
        return $template;
    }

    /**
     * 音乐
     * @param $page
     * @param $keyword
     * @return mixed
     */
    private function audio($page,$keyword)
    {
        $audio = \Cache::remember('audio:'.$keyword,'5',function() use ($keyword,$page){
            try{
                if( $this->sensitivity($keyword) === 'yes' ){        //不涉及敏感词汇
                    $audio = MakeAudioFile::with(['belongsToFolder'])
                        -> where('test_result',1)
                        -> where('active',1)
                        ->where('name','like','%'.$keyword.'%')
                        -> forPage($page, $this->paginate)
                        ->orderBy('count','DESC')
                        -> get(['id','name','intro','count','audition_address','address','integral','duration']);

                    // 调用内部函数，返回数据
                    return $this -> audiohandle($audio);
                }else{                                               //如果涉及敏感词汇
                    return response()->json(['message'=>'Sensitive vocabulary'],403);
                }
            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });

        return $audio;
    }

    /**
     * 编辑视频，音频文件详情 上面调用
     * @param object $audio    集合
     * @param array $integral_ids   登录用户下载过的收费文件的id
     * @return \Illuminate\Http\JsonResponse
     */
    private function audiohandle($audio, $integral_ids=[])
    {
        try{
            // 拼接地址
            foreach($audio as $key => $value) {

                // 对id进行加密
                $value -> file_id = Crypt::encrypt($value->id);

                $value -> audition_address = CloudStorage::privateUrl_zip($value -> audition_address);

                // 免费的文件和自己已经下载过的会有下载地址，收费的下载地址为空
                if(0 == $value->integral || in_array($value->id, $integral_ids)){
                    $value -> address = CloudStorage::privateUrl_zip($value -> address);
                    $value -> integral = 0; // 已经下载过的则将下载所需金币变为0
                } else {
                    $value -> address = '';
                }

                // 删除原id
                unset($value -> id);
            }

            return response() -> json(['data'=>$audio],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 音频
     * @param $page
     * @param $keyword
     */
    private function audioEffect($page,$keyword)
    {
        $audioEffect = \Cache::remember('audioeffect:'.$keyword,'5',function () use ($keyword,$page){
            try{
                if( $this->sensitivity($keyword) === 'yes' ){        //不涉及敏感词汇
                    // 获取数据
                    $audio = MakeAudioEffectFile::with(['belongsToFolder'])
                        ->where('test_result',1)
                        -> where('active',1)
                        ->where('name','like','%'.$keyword.'%')
                        -> forPage($page, $this->paginate)
                        ->orderBy('count','DESC')
                        -> get(['id','name','intro','count','audition_address','address','integral','duration']);

                    // 调用内部函数，返回数据
                     return $this -> audioEffecthandle($audio);

                }else{                                               //如果涉及敏感词汇
                    return response()->json(['message'=>'Sensitive vocabulary'],403);
                }
            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });

        return $audioEffect;
    }

    /**
     * 编辑视频，音频文件详情 上面调用
     * @param object $audio    集合
     * @param array $integral_ids   登录用户下载过的收费文件的id
     * @return \Illuminate\Http\JsonResponse
     */
    private function audioEffecthandle($audio, $integral_ids=[])
    {
        try{
            // 拼接地址
            foreach($audio as $key => $value) {

                // 对id进行加密
                $value -> file_id = Crypt::encrypt($value->id);

                $value -> audition_address = CloudStorage::privateUrl_zip($value -> audition_address);

                // 免费的文件和自己已经下载过的会有下载地址，收费的下载地址为空
                if(0 == $value->integral || in_array($value->id, $integral_ids)){

                    $value -> address = CloudStorage::privateUrl_zip($value -> address);

                    $value -> integral = 0; // 已经下载过的则将下载所需金币变为0
                } else {
                    $value -> address = '';
                }

                // 删除原id
                unset($value -> id);
            }

            return response() -> json(['data'=>$audio],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 赛事
     * @param $page
     * @param $keyword
     * @return array|\Illuminate\Http\JsonResponse
     */
    private function activity($page,$keyword)
    {
        $activity = Cache::remember('activity'.$keyword,'5',function () use ($page,$keyword){
            try{
                if( $this->sensitivity($keyword) === 'yes' ){        //不涉及敏感词汇
                    // 获取赛事
                    $data = Activity::with(['belongsToUser','hasManyTweets.belongsToUser' => function ($q){
                        $q -> select(['id','avatar']);
                    }])
                        -> active()
                        -> where('comment','like','%'.$keyword.'%')
                        -> paginate($this->paginate, ['id','user_id','bonus','comment','expires','time_add','icon','users_count'], 'page', $page);

                    return [
                        'data' => $this -> activityTransformer -> transformCollection($data->all()),
                    ];

                }else{                                               //如果涉及敏感词汇
                    return response()->json(['message'=>'Sensitive vocabulary'],403);
                }
            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });

        return $activity;

    }

    /**
     * 片段搜索          播放次数
     * @param $page
     * @param $keyword
     * @return \Illuminate\Http\JsonResponse
     */
    private function fragment($page,$keyword)
    {
        $fragment = Cache::remember('fragment:'.$keyword,'5',function() use ($page,$keyword){
            try{
                if( $this->sensitivity($keyword) === 'yes' ){        //不涉及敏感词汇

                    $fragment_info = Fragment::WhereHas('keyWord', function ($q) use ($keyword) {
                        $q->where('keyword', '=', $keyword);
                    })
                        ->with(['belongsToManyFragmentType'=>function($q){
                            $q->select(['name']);
                        }])
                        ->where('test_results',1)
                        ->where('active', 1)
                        ->orWhere('name', 'like', '%' . $keyword . '%')
                        ->forPage($page, $this->paginate)
                        ->orderBy('watch_count', 'DESC')
                        ->get(['id','name','cover','net_address','duration','watch_count']);

                    return response()->json([
                        'data' => $this->newFragmentSearchTransformer->transformCollection($fragment_info->toArray()),
                    ], 200);

                }else{                                               //如果涉及敏感词汇
                    return response()->json(['message'=>'Sensitive vocabulary'],403);
                }

            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });

        return $fragment;
    }

    /**
     * 动态搜索
     * @param $page
     * @param $keyword
     * @return \Illuminate\Http\JsonResponse
     */
    private function tweet($page,$keyword)
    {
        try{
            //获取用户信息
            $user = Auth::guard('api')->user();

//            $noExitWord_obj =  NoExitWord::distinct('keyword')->get(['keyword']);
//
//            $arr = $noExitWord_obj->toArray();
//
//            $noExitWord_arr = array_column($arr, 'keyword');
//
//            \Cache::put('noExitWord',$noExitWord_arr,'60');

            if( $this->sensitivity($keyword) === 'yes' ){        //不涉及敏感词汇

                $tweet_1 = Tweet::WhereHas('hasOneContent',function ($q) use ($keyword){
                    $q->where('content','like','%'.$keyword.'%');
                })
                    ->with(['hasOneContent'=>function($q){
                        $q->select(['tweet_id','content']);
                    },'belongsToManyChannel'=>function($q){
                        $q->select(['name']);
                    }])
                    ->where('active',1)
                    ->where('visible',0)
                    ->forPage($page,$this->paginate)
                    ->orderBy('browse_times','DESC')
                    ->get(['id','duration','screen_shot','browse_times']);

                $number = $this->paginate - $tweet_1->count();

                $tweet_2 = Tweet::WhereHas('belongsToManyKeywords',function($q) use ($keyword){
                    $q->where('keyword',$keyword);
                })
                    ->with(['hasOneContent'=>function($q){
                        $q->select(['tweet_id','content']);
                    },'belongsToManyChannel'=>function($q){
                        $q->select(['name']);
                    }])
                    ->where('active',1)
                    ->where('visible',0)
                    ->forPage($page,$number)
                    ->orderBy('browse_times','DESC')
                    ->get(['id','duration','screen_shot','browse_times']);

                if ($user){     //用户已登录
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
                    }
                    $number_1 = $number - $tweet_2->count();

                    //朋友可见的动态
                    $friends_tweets_1 = Tweet::WhereHas('belongsToUser', function ($q) use ($friends) {
                        $q->whereIn('id', $friends);
                    })
                        ->WhereHas('belongsToManyKeywords',function($q) use ($keyword){
                            $q->where('keyword',$keyword);
                        })
                        ->with(['hasOneContent'=>function($q){
                            $q->select(['tweet_id','content']);
                        },'belongsToManyChannel'=>function($q){
                            $q->select(['name']);
                        }])
                        ->where('visible', 1)
                        ->whereIn('active',[0,1])
                        ->forPage($page, $number_1)
                        ->orderBy('browse_times','DESC')
                        ->get(['id','duration','screen_shot','browse_times']);

                    $number_2 = $number_1 - $friends_tweets_1->count();
                    $friends_tweets_2 = Tweet::WhereHas('belongsToUser', function ($q) use ($friends) {
                        $q->whereIn('id', $friends);
                    })
                        ->WhereHas('hasOneContent',function ($q) use ($keyword){
                            $q->where('content','like','%'.$keyword.'%');
                        })
                        ->with(['hasOneContent'=>function($q){
                            $q->select(['tweet_id','content']);
                        },'belongsToManyChannel'=>function($q){
                            $q->select(['name']);
                        }])
                        ->where('active',1)
                        ->where('visible',0)
                        ->forPage($page,$number_2)
                        ->orderBy('browse_times','DESC')
                        ->get(['id','duration','screen_shot','browse_times']);

                    //仅自己可见的动态
                    $number_3 = $number_2 - $friends_tweets_2->count();

                    $self_tweets_1 = Tweet::WhereHas('belongsToUser', function ($q) use ($user) {
                        $q->where('id', $user->id);
                    })
                        ->WhereHas('belongsToManyKeywords',function($q) use ($keyword){
                            $q->where('keyword',$keyword);
                        })
                        ->with(['hasOneContent'=>function($q){
                            $q->select(['tweet_id','content']);
                        },'belongsToManyChannel'=>function($q){
                            $q->select(['name']);
                        }])
                        ->where('visible', 2)
                        ->orderBy('created_at', 'desc')
                        ->forPage($page, $number_3)
                        ->whereIn('active',[0,1])
                        ->get(['id','duration','screen_shot','browse_times']);

                    $number_4 = $number_3 - $self_tweets_1->count();
                    $self_tweets_2 = Tweet::WhereHas('belongsToUser', function ($q) use ($user) {
                        $q->where('id', $user->id);
                    })
                        ->WhereHas('hasOneContent',function ($q) use ($keyword){
                            $q->where('content','like','%'.$keyword.'%');
                        })
                        ->with(['hasOneContent'=>function($q){
                            $q->select(['tweet_id','content']);
                        },'belongsToManyChannel'=>function($q){
                            $q->select(['name']);
                        }])
                        ->where('visible', 2)
                        ->orderBy('created_at', 'desc')
                        ->forPage($page, $number_4)
                        ->whereIn('active',[0,1])
                        ->get(['id','duration','screen_shot','browse_times']);

                    $tweets = array_merge($tweet_1->toArray(),$tweet_2->toArray(),$friends_tweets_1->toArray(),$friends_tweets_2->toArray(),$self_tweets_1->toArray(),$self_tweets_2->toArray());

                    $tweet = mult_unique($tweets);

                }else{          //用户未登录
                    $tweet = array_merge($tweet_1->toArray(),$tweet_2->toArray());
                }

                return response()->json([
                    'data'      =>      $this->newTweetsSearchTransformer->transformCollection($tweet),
                ],200);

            }else{                                               //如果涉及敏感词汇
                return response()->json(['message'=>'Sensitive vocabulary'],403);
            }

        }catch (\Exception $e){
            return response()->json(['message'=>'bad_request'],500);
        }
    }

    /**
     * 话题
     * @param $page
     * @param $keyword
     * @return mixed
     */
    private function topic($page,$keyword)
    {
        $topic = Cache::remember('topic:'.$keyword,'5',function() use ($page,$keyword){
            try{

                $topics = Topic::ofSearch($keyword)
                    ->able()
                    ->orderBy('id','desc')
                    ->forPage($page,$this->paginate)
                    ->get(['id','name','comment','icon']);

                return response()->json([
                    // 数据
                    'data' => count($topics) ? $this->searchTopicsTransformer->transformCollection($topics->all()) : null,
                ]);

            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });
        return $topic;
    }

    /**
     * 全部内容
     * @param $page
     * @param $keyword
     * @return \Illuminate\Http\JsonResponse
     */
    private function allType($page,$keyword)
    {
        try{
            //用户
            $user = Auth::guard('api')->user();

            $res = preg_match("/^1[3,4,5,7,8][0-9]{9}$/",$keyword);

            if (is_numeric($keyword) && strlen($keyword)==11 && $res){

                if ($user) {        //手机号搜索  排除黑名单   且用户登录

                    $user_id = $user->id;

                    $blacklist  = Blacklist::where('from',$user_id)->pluck('to');

                    $user_info = User::WhereHas('hasOneLocalAuth', function ($q) use ($keyword) {
                        $q->where('username', $keyword);
                    })
                        ->where('is_phonenumber', 1)
                        ->where('search_phone', 1)
                        ->where('id','!=',$user_id)
                        ->whereIn('active', [1,2])
                        ->orderBy('fans_count','DESC')
                        ->whereNotIn('id',$blacklist)
                        ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);
                }else{                  //手机号搜索   用户未登录

                    $user_info = User::WhereHas('hasOneLocalAuth', function ($q) use ($keyword) {
                        $q->where('username', $keyword);
                    })
                        ->where('is_phonenumber', 1)
                        ->where('search_phone', 1)
                        ->whereIn('active', [1,2])
                        ->orderBy('fans_count','DESC')
                        ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);

                }

                if (!$user_info->count()){

                    if($user){              //如果手机格式搜索不到  用户登录   排除黑名单
                        $user_id = $user->id;

                        $blacklist  = Blacklist::where('from',$user_id)->pluck('to');

                        $user_info = User::where('id','!=',$user_id)
                            ->orderBy('fans_count','DESC')
                            ->where('nickname','like','%'.$keyword.'%')
                            ->whereNotIn('id',$blacklist)
                            ->take(3)
                            ->whereIn('active', [1,2])
                            ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);

                    }else{          //如果搜索不到   用户未登录

                        $user_info = User::where('nickname','like','%'.$keyword.'%')
                            ->orderBy('fans_count','DESC')
                            ->take(3)
                            ->whereIn('active', [1,2])
                            ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);
                    }
                }

            }else{

                if($user){              //按昵称搜索   用户登录   排除黑名单
                    $user_id = $user->id;

                    $blacklist  = Blacklist::where('from',$user_id)->pluck('to');

                    $user_info = User::where('id','!=',$user_id)
                        ->orderBy('fans_count','DESC')
                        ->where('nickname','like','%'.$keyword.'%')
                        ->whereNotIn('id',$blacklist)
                        ->take(3)
                        ->whereIn('active', [1,2])
                        ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);

                }else{          //昵称搜索   用户未登录

                    $user_info = User::where('nickname','like','%'.$keyword.'%')
                        ->take(3)
                        ->orderBy('fans_count','DESC')
                        ->whereIn('active', [1,2])
                        ->get(['id', 'nickname', 'avatar', 'verify', 'verify_info', 'signature', 'cover']);
                }

            }

            //模板
            $details = MakeTemplateFile::with(['belongsToFolder'=>function($q){
                $q->select(['id','name']);
            }])
                ->where('test_result',1)
                -> where('active',1)
                ->where('name','like','%'.$keyword.'%')
                ->take(3)
                ->orderBy('count','DESC')
                -> get( ['id','folder_id', 'name','preview_address','address','cover','duration','watch_count']);

            //竞赛
            $activity = Activity::with(['belongsToUser','hasManyTweets.belongsToUser' => function ($q){
                $q -> select(['id','avatar']);
            }])
                -> active()
                -> where('comment','like','%'.$keyword.'%')
                ->take(3)
                -> get(['id','user_id','bonus','comment','expires','time_add','icon','users_count']);

            //片段
            $fragment_info = Fragment::WhereHas('keyWord', function ($q) use ($keyword) {
                $q->where('keyword', '=', $keyword);
            })
                ->with(['belongsToManyFragmentType'=>function($q){
                    $q->select(['name']);
                }])
                ->where('test_results',1)
                ->where('active', 1)
                ->orWhere('name', 'like', '%' . $keyword . '%')
                ->forPage($page, $this->paginate)
                ->orderBy('watch_count', 'DESC')
                ->get(['id','name','cover','net_address','duration','watch_count']);

            //动态
            $tweet_1 = Tweet::WhereHas('hasOneContent',function ($q) use ($keyword){
                $q->where('content','like','%'.$keyword.'%');
            })
                ->with(['hasOneContent'=>function($q){
                    $q->select(['tweet_id','content']);
                },'belongsToManyChannel'=>function($q){
                    $q->select(['name']);
                }])
                ->where('active',1)
                ->where('visible',0)
                ->take(3)
                ->orderBy('browse_times','DESC')
                ->get(['id','duration','screen_shot','browse_times']);

            $number = 3 - $tweet_1->count();

            $tweet_2 = Tweet::WhereHas('belongsToManyKeywords',function($q) use ($keyword){
                $q->where('keyword',$keyword);
            })
                ->with(['hasOneContent'=>function($q){
                    $q->select(['tweet_id','content']);
                },'belongsToManyChannel'=>function($q){
                    $q->select(['name']);
                }])
                ->where('active',1)
                ->where('visible',0)
                ->forPage($page,$number)
                ->orderBy('browse_times','DESC')
                ->get(['id','duration','screen_shot','browse_times']);

            if ($user){     //用户已登录
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
                }
                $number_1 = $number - $tweet_2->count();

                //朋友可见的动态
                $friends_tweets_1 = Tweet::WhereHas('belongsToUser', function ($q) use ($friends) {
                    $q->whereIn('id', $friends);
                })
                    ->WhereHas('belongsToManyKeywords',function($q) use ($keyword){
                        $q->where('keyword',$keyword);
                    })
                    ->with(['hasOneContent'=>function($q){
                        $q->select(['tweet_id','content']);
                    },'belongsToManyChannel'=>function($q){
                        $q->select(['name']);
                    }])
                    ->where('visible', 1)
                    ->whereIn('active',[0,1])
                    ->forPage($page, $number_1)
                    ->orderBy('browse_times','DESC')
                    ->get(['id','duration','screen_shot','browse_times']);

                $number_2 = $number_1 - $friends_tweets_1->count();
                $friends_tweets_2 = Tweet::WhereHas('belongsToUser', function ($q) use ($friends) {
                    $q->whereIn('id', $friends);
                })
                    ->WhereHas('hasOneContent',function ($q) use ($keyword){
                        $q->where('content','like','%'.$keyword.'%');
                    })
                    ->with(['hasOneContent'=>function($q){
                        $q->select(['tweet_id','content']);
                    },'belongsToManyChannel'=>function($q){
                        $q->select(['name']);
                    }])
                    ->where('active',1)
                    ->where('visible',0)
                    ->forPage($page,$number_2)
                    ->orderBy('browse_times','DESC')
                    ->get(['id','duration','screen_shot','browse_times']);

                //仅自己可见的动态
                $number_3 = $number_2 - $friends_tweets_2->count();

                $self_tweets_1 = Tweet::WhereHas('belongsToUser', function ($q) use ($user) {
                    $q->where('id', $user->id);
                })
                    ->WhereHas('belongsToManyKeywords',function($q) use ($keyword){
                        $q->where('keyword',$keyword);
                    })
                    ->with(['hasOneContent'=>function($q){
                        $q->select(['tweet_id','content']);
                    },'belongsToManyChannel'=>function($q){
                        $q->select(['name']);
                    }])
                    ->where('visible', 2)
                    ->orderBy('created_at', 'desc')
                    ->forPage($page, $number_3)
                    ->whereIn('active',[0,1])
                    ->get(['id','duration','screen_shot','browse_times']);

                $number_4 = $number_3 - $self_tweets_1->count();
                $self_tweets_2 = Tweet::WhereHas('belongsToUser', function ($q) use ($user) {
                    $q->where('id', $user->id);
                })
                    ->WhereHas('hasOneContent',function ($q) use ($keyword){
                        $q->where('content','like','%'.$keyword.'%');
                    })
                    ->with(['hasOneContent'=>function($q){
                        $q->select(['tweet_id','content']);
                    },'belongsToManyChannel'=>function($q){
                        $q->select(['name']);
                    }])
                    ->where('visible', 2)
                    ->orderBy('created_at', 'desc')
                    ->forPage($page, $number_4)
                    ->whereIn('active',[0,1])
                    ->get(['id','duration','screen_shot','browse_times']);

                $tweets = array_merge($tweet_1->toArray(),$tweet_2->toArray(),$friends_tweets_1->toArray(),$friends_tweets_2->toArray(),$self_tweets_1->toArray(),$self_tweets_2->toArray());

                $tweet = mult_unique($tweets);

            }else{          //用户未登录
                $tweet = array_merge($tweet_1->toArray(),$tweet_2->toArray());
            }

            //话题
            $topics = Topic::ofSearch($keyword)
                ->able()
                ->orderBy('like_count','desc')
                ->take(3)
                ->get(['id','name','comment','icon']);

            return response()->json([
                'user'      =>   $this ->newUserSearchTransformer->transformCollection($user_info->toArray()),
                'template'  =>   $this->newTemplateSearchTransformer->transformCollection($details->toArray()),
                'activity'  =>   $this -> activityTransformer -> transformCollection($activity->all()),
                'fragment'  =>   $this->newFragmentSearchTransformer->transformCollection($fragment_info->toArray()),
                'tweet'     =>   $this->newTweetsSearchTransformer->transformCollection($tweet),
                'topic'     =>   $this->searchTopicsTransformer->transformCollection($topics->all()) ,
            ]);
        }catch (\Exception $e){
            return response()->json(['message'=>'bad_request'],500);
        }
    }
    /**
     * 敏感过滤 -> 累计 -> 收录
     * @param $keyword
     * @return string
     */
    private function sensitivity($keyword)
    {
            //查询
            //过滤关键词为非敏感词
            $sensitivewords = Cache::get('sensitivewords');

            //拆解
            $keyword_arr = getKeywords($keyword);

            //段落匹配
            $same_1 = array_intersect($keyword_arr,$sensitivewords);

            //词汇匹配
            $same_2 = in_array($keyword,$sensitivewords,TRUE);

            //是否涉及敏感词汇
            if( $same_1 || $same_2){        //包含敏感词
                return 'no';
            }else{                          //不包含敏感词
                //如果搜索的为词汇
                if(strlen($keyword)<=12){
                    $this -> word($keyword);
                }else{                                          //如果搜索的段落
                    $arr = getKeywords($keyword);
                    array_map([$this,'word'],$arr);
                }
                return 'yes';
            }
    }

    /**
     * @param $keyword
     */
    private function word($keyword)
    {
        //从缓存中取出关键词
        $keywords = Cache::get('keywords');

        //判断关键词是否存在于系统内
        $is_exit = in_array($keyword,$keywords,TRUE);

        //如果存在则记录pv  ip
        if($is_exit){
            //总搜索次数+1
            DB::table('keywords')->where('keyword','=',$keyword)->increment('count_sum_pv');

            //日搜索+1
            DB::table('keywords')->where('keyword','=',$keyword)->increment('count_day_pv');

            //周搜索 +1
            DB::table('keywords')->where('keyword','=',$keyword)->increment('count_week_pv');

            if( $keyword != Cache::get( getIP().$keyword ) ){
                //总搜索次数+1
                DB::table('keywords')->where('keyword','=',$keyword)->increment('count_sum');

                //日搜索+1
                DB::table('keywords')->where('keyword','=',$keyword)->increment('count_day');

                //周搜索 +1
                DB::table('keywords')->where('keyword','=',$keyword)->increment('count_week');

            }

            //将IP写入缓存
            $ip = getIP();

            Cache::put($ip.$keyword ,$keyword,'1440');

        }else{
            //判断生词表内是否存在   如果存在则累计次数
            $noExitWord = Cache::get('noExitWord');
                //如果该词存在于生词表
                $noexit_word = in_array($keyword,$noExitWord,TRUE);
            if ($noexit_word){
                //生词PV
                DB::table('noexist_word')->where('keyword',$keyword)->increment('count_sum_pv');

                //生词IP
                if( $keyword != Cache::get( getIP().'noExit:'.$keyword ) ){
                    DB::table('noexist_word')->where('keyword',$keyword)->increment('count_sum_ip');
                }

                $ip = getIP();

                Cache::put($ip.'noExit:'.$keyword ,$keyword,'1440');

            }else{              //如果不存在 则写入
                if($keyword != Cache::get('mosheng'.$keyword.getIP())){

                    DB::table('noexist_word')->insert([
                        'keyword'   =>  $keyword,
                        'create_at' =>  time(),
                        'update_at' =>  time(),
                    ]);
                    Cache::put('mosheng'.$keyword.getIP(),$keyword,'60');
                }

            }

            //定时任务  删除搜索次数少的生词 清空生词表
        }
    }


}
