<?php

namespace App\Http\Controllers\NewWeb\User;

use App\Models\AdvertisingRotation;
use App\Models\Filmfests;
use App\Models\Friend;
use App\Models\Subscription;
use App\Models\Tweet;
use App\Models\TweetReply;
use App\Models\User;
use App\Models\User\UserLoginLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //
    private $paginate = 4;

    private $protocol = 'http://';

    public function index(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            if((\Auth::guard('api')->user()->role) == 1){
                $role = [
                    [
                        'name'=>'主页'
                    ],
                    [
                        'name'=>'电影节'
                    ],
                    [
                        'name'=>'云空间'
                    ],
                    [
                        'name'=>'账户'
                    ],
                    [
                        'name'=>'北京大学生电影节'
                    ],
                ];
            }else{
                $role = [
                    [
                        'name'=>'主页'
                    ],
                    [
                        'name'=>'电影节'
                    ],
                    [
                        'name'=>'云空间'
                    ],
                    [
                        'name'=>'账户'
                    ]
                ];
            }
            $loginData = UserLoginLog::select('ip','login_time')
                ->where('user_id',$user)->orderBy('login_time','desc')
                ->offset(1)->limit(1)->first();
            if($loginData){
                $prevLoginData = [
                    'ip'=>$loginData->ip,
                    'time'=> date('Y/m/d H:i',$loginData->login_time)
                ];
            }else{
                $prevLoginData = '这是您的第一次登录!';
            }

            return response()->json(['role'=>$role,'login'=>$prevLoginData],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found']);
        }
    }


    public function tweet(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            $layout = $request->get('layout',1);
            $orderBy = $request->get('orderBy',1);
            $page = $request->get('page',1);
            switch ($orderBy){
                case 1:
                    $orderBy = 'created_at';
                    break;
                case 2:
                    $orderBy = 'browse_times';
                    break;
                case 3:
                    $orderBy = 'like_count';
                    break;
                case 4:
                    $orderBy = 'reply_count';
                    break;
                default :
                    $orderBy = 'created_at';
                    break;
            }
            if($layout == 1){
                    $data = [];
                    // 获取所有关注的用户id
                    $subscriptions = Subscription::where('from',$user)->pluck('to');

                    // 获取所有好友的用户id
                    $friends = Friend::where('from',$user)->get(['to'])->pluck('to')->all();
//                    $maindata = Tweet::where('active','=',1)
//                        ->orderBy($orderBy,'desc')->limit($page*($this->paginate))->get();
                    $maindata = Tweet::
                    ofAttention($subscriptions,$friends, $user)
                        ->where('active','=',1)
                        ->orderBy($orderBy,'desc')
                        ->limit($page*($this->paginate))
                        ->get();
                    foreach ($maindata as $k => $v)
                    {

                        $userName = $v->belongsToUser->nickname;
                        $time = $v->created_at;
                        $content = $v->hasOneContent->content;
                        $reply = [];
                        $replys = TweetReply::where('tweet_id',$v->id)->where('reply_id','=',null)
                            ->orderBy('like_count','desc')->limit(3)->get();
                        $reply_count = TweetReply::select('id')->where('tweet_id',$v->id)->where('reply_id','=',null)
                            ->orderBy('like_count','desc')->get()->count();
                        $i = 1;
                        $grade = 0;
                        if($replys)
                        {
                            foreach ($replys as $kk => $vv)
                            {
                                if($v->anonymity==0){
                                    $replyName = '匿名';
                                }else{
                                    $replyName = $vv->belongsToUser->nickname;
                                }
                                $reply_child = [
                                    'reply_name'=>$replyName,
                                    'grade'=>$vv->grade >= 9.8 ? 9.8:$vv->grade,
                                    'content'=>$vv->content,
                                ];
                                $i = $i+1;
                                $grade = $grade+$vv->grade;
                                array_push($reply,$reply_child);
                            }
                            $grade = ($grade/$i) >= 9.8 ? 9.8 : round($grade/$i,1);
                        }
                        if($v->browse_times>10000){
                            $browse_times = round(($v->browse_times)/10000,1).' 万次';
                        }else{
                            $browse_times = $v->browse_times.' 次';
                        }
                        $like_count = $v->like_count;
                        $retweet_count = $v->retweet_count;
                        if($v->original==0){
                            $is_original = false;
                        }else{
                            $is_original = true;
                        }
                        if($is_original){
                            $original_tweet = $v->hasOneOriginal->hasOneContent->content;
                            $original_tweet_id = $v->hasOneOriginal->id;
                            $original_avatar = $this->protocol.$v->hasOneOriginal->hasOneContent->avatar;
                            $original_name = $v->hasOneOriginal->hasOneContent->nickname;
                            $original_id = $v->original;
                            $prefix_tweet = [
                                'original_tweet'=>$original_tweet,
                                'original_avatar'=>$original_avatar,
                                'original_name'=>$original_name,
                                'original_id'=>$original_id,
                                'original_tweet_id'=>$original_tweet_id,
                            ];
                        }else{
                            $prefix_tweet = '';
                        }
                        $avatar = $v->belongsToUser->avatar?$this->protocol.$v->belongsToUser->avatar:'';
                        $cover = $v->screen_shot?$this->protocol.$v->screen_shot:'';
                        $duration = floor((($v->duration)/60));
                        $duration .= ':';
                        $duration .= (($v->duration)%60)<10?'0'.($v->duration)%60:($v->duration)%60;
                        $video = $v->type==3?$this->protocol.$v->transcoding_video:$this->protocol.$v->video;
                        $tempData = [
                            'grade' => $grade,
                            'userName'=>$userName,
                            'time'=>$time,
                            'content'=>$content,
                            'reply'=>$reply,
                            'browse_times'=>$browse_times,
                            'like_count'=>$like_count,
                            'reply_count'=>$reply_count,
                            'retweet_count'=>$retweet_count,
                            'is_original'=>$is_original,
                            'prefix_tweet'=>$prefix_tweet,
                            'cover'=>$cover,
                            'duration'=>$duration,
                            'id'=>$v->id,
                            'video'=>$video,
                            'avatar'=>$avatar,
                        ];

                        array_push($data,$tempData);

                    }

            }else{
                $data = [];
                // 获取所有关注的用户id
                $subscriptions = Subscription::where('from',$user)->pluck('to');

                // 获取所有好友的用户id
                $friends = Friend::where('from',$user)->get(['to'])->pluck('to')->all();
                $maindata = Tweet::
                ofAttention($subscriptions,$friends, $user)
                    ->where('active','=',1)
                    ->orderBy($orderBy,'desc')
                    ->limit($page*($this->paginate))
                    ->get();
                foreach ($maindata as $k => $v)
                {
                    $userName = $v->belongsToUser->nickname;
                    $time = $v->created_at;
                    $content = $v->hasOneContent->content;
                    if($v->browse_times>10000){
                        $browse_times = round(($v->browse_times)/10000,1).' 万次';
                    }else{
                        $browse_times = $v->browse_times.' 次';
                    }
                    if($v->original==0){
                        $is_original = false;
                    }else{
                        $is_original = true;
                    }

                    $cover = $v->screen_shot?$this->protocol.$v->screen_shot:'';
                    $duration = floor((($v->duration)/60));
                    $duration .= ':';
                    $duration .= (($v->duration)%60)<10?'0'.($v->duration)%60:($v->duration)%60;
                    $video = $v->type==3?$this->protocol.$v->transcoding_video:$this->protocol.$v->video;
                    $avatar = $this->protocol.$v->belongsToUser->avatar;
                    $tempData = [
                        'userName'=>$userName,
                        'time'=>$time,
                        'content'=>$content,
                        'browse_times'=>$browse_times,
                        'is_original'=>$is_original,
                        'cover'=>$cover,
                        'duration'=>$duration,
                        'id'=>$v->id,
                        'video'=>$video,
                        'avatar'=>$avatar,
                    ];

                    array_push($data,$tempData);

                }
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function myVideo(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            $layout = $request->get('layout',1);
            $orderBy = $request->get('orderBy',1);
            $page = $request->get('page',1);
            switch ($orderBy){
                case 1:
                    $orderBy = 'created_at';
                    break;
                case 2:
                    $orderBy = 'browse_times';
                    break;
                case 3:
                    $orderBy = 'like_count';
                    break;
                case 4:
                    $orderBy = 'reply_count';
                    break;
                default :
                    $orderBy = 'created_at';
                    break;
            }
            if($layout == 1){
                $data = [];
                $my_id = \Auth::guard('api')->user()->id;
                $maindata = Tweet::where('active','=',1)
                    ->where('user_id',$my_id)->where([['type','=','video'],['type','=','production']])
                    ->orderBy($orderBy,'desc')->limit($page*($this->paginate))->get();
                foreach ($maindata as $k => $v)
                {
                    $userName = $v->belongsToUser->nickname;
                    $time = $v->created_at;
                    $content = $v->hasOneContent->content;
                    $reply = [];
                    $replys = TweetReply::where('tweet_id',$v->id)->where('reply_id','=',null)
                        ->orderBy('like_count','desc')->limit(3)->get();
                    $reply_count = TweetReply::select('id')->where('tweet_id',$v->id)->where('reply_id','=',null)
                        ->orderBy('like_count','desc')->get()->count();
                    $i = 1;
                    $grade = 0;
                    foreach ($replys as $kk => $vv)
                    {
                        if($v->anonymity==0){
                            $replyName = '匿名';
                        }else{
                            $replyName = $vv->belongsToUser->nickname;
                        }
                        $reply_child = [
                            'reply_name'=>$replyName,
                            'grade'=>$vv->grade >= 9.8 ? 9.8:$vv->grade,
                            'content'=>$vv->content,
                        ];
                        $i = $i+1;
                        $grade = $grade+$vv->grade;
                        array_push($reply,$reply_child);
                    }
                    $grade = ($grade/$i) >= 9.8 ? 9.8 : round($grade/$i,1);
                    if($v->browse_times>10000){
                        $browse_times = round(($v->browse_times)/10000,1).' 万次';
                    }else{
                        $browse_times = $v->browse_times.' 次';
                    }
                    $like_count = $v->like_count;
                    $retweet_count = $v->retweet_count;
                    if($v->original==0){
                        $is_original = false;
                    }else{
                        $is_original = true;
                    }
                    if($is_original){
                        $original_tweet = $v->hasOneOriginal->hasOneContent->content;
                        $original_tweet_id = $v->hasOneOriginal->id;
                        $original_avatar = $this->protocol.$v->hasOneOriginal->hasOneContent->avatar;
                        $original_name = $v->hasOneOriginal->hasOneContent->nickname;
                        $original_id = $v->original;
                        $prefix_tweet = [
                            'original_tweet'=>$original_tweet,
                            'original_avatar'=>$original_avatar,
                            'original_name'=>$original_name,
                            'original_id'=>$original_id,
                            'original_tweet_id'=>$original_tweet_id,
                        ];
                    }else{
                        $prefix_tweet = '';
                    }
                    $cover = $v->screen_shot?$this->protocol.$v->screen_shot:'';
                    $duration = floor((($v->duration)/60));
                    $duration .= ':';
                    $duration .= (($v->duration)%60)<10?'0'.($v->duration)%60:($v->duration)%60;
                    $video = $v->type==3?$this->protocol.$v->transcoding_video:$this->protocol.$v->video;
                    $avatar = $this->protocol.$v->belongsToUser->avatar;
                    $tempData = [
                        'grade'=>$grade,
                        'userName'=>$userName,
                        'time'=>$time,
                        'content'=>$content,
                        'reply'=>$reply,
                        'browse_times'=>$browse_times,
                        'like_count'=>$like_count,
                        'reply_count'=>$reply_count,
                        'retweet_count'=>$retweet_count,
                        'is_original'=>$is_original,
                        'prefix_tweet'=>$prefix_tweet,
                        'cover'=>$cover,
                        'duration'=>$duration,
                        'id'=>$v->id,
                        'video'=>$video,
                        'avatar'=>$avatar,
                    ];

                    array_push($data,$tempData);

                }
            }else{
                $data = [];
                $my_id = \Auth::guard('api')->user()->id;
                $maindata = Tweet::where('active','=',1)
                    ->where('user_id',$my_id)->where([['type','=','video'],['type','=','production']])
                    ->orderBy($orderBy,'desc')->limit($page*20)->get();
                foreach ($maindata as $k => $v)
                {
                    $userName = $v->belongsToUser->nickname;
                    $time = $v->created_at;
                    $content = $v->hasOneContent->content;
                    if($v->browse_times>10000){
                        $browse_times = round(($v->browse_times)/10000,1).' 万次';
                    }else{
                        $browse_times = $v->browse_times.' 次';
                    }
                    if($v->original==0){
                        $is_original = false;
                    }else{
                        $is_original = true;
                    }
                    $cover = $v->screen_shot?$this->protocol.$v->screen_shot:'';
                    $duration = floor((($v->duration)/60));
                    $duration .= ':';
                    $duration .= (($v->duration)%60)<10?'0'.($v->duration)%60:($v->duration)%60;
                    $video = $v->type==3?$this->protocol.$v->transcoding_video:$this->protocol.$v->video;
                    $avatar = $this->protocol.$v->belongsToUser->avatar;
                    $tempData = [
                        'userName'=>$userName,
                        'time'=>$time,
                        'content'=>$content,
                        'browse_times'=>$browse_times,
                        'is_original'=>$is_original,
                        'cover'=>$cover,
                        'duration'=>$duration,
                        'id'=>$v->id,
                        'video'=>$video,
                        'avatar'=>$avatar,
                    ];

                    array_push($data,$tempData);

                }
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function discover(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            $layout = $request->get('layout',1);
            $orderBy = $request->get('orderBy',1);
            $page = $request->get('page',1);
            switch ($orderBy){
                case 1:
                    $orderBy = 'created_at';
                    break;
                case 2:
                    $orderBy = 'browse_times';
                    break;
                case 3:
                    $orderBy = 'like_count';
                    break;
                case 4:
                    $orderBy = 'reply_count';
                    break;
                default :
                    $orderBy = 'created_at';
                    break;
            }
            if($layout == 1){
                $advertising = AdvertisingRotation::where('from_time','<',time())
                    ->where('end_time','>',time())
                    ->where('active','=','1')
                    ->wherehas('advert_category',function ($q){
                        $q->where('id',2);
                    })
                    ->orderBy('time_update')->limit(5)->get();
                $advert = [];
                foreach ($advertising as $K => $v)
                {
                    $address = $v->url;
                    $image = $v->image;
                    $user = $v->belongsToUser()->first()?$v->belongsToUser->nickname:"";
                    $tempAdvertisingData = [
                        'address' => $address,
                        'image' => $image,
                        'user' => $user,
                    ];
                    array_push($advert,$tempAdvertisingData);
                }
                $tweet = Tweet::where('active','=',1)->orderBy($orderBy,'desc')->limit($page*($this->paginate))->get();
                $data = [];
                foreach ($tweet as $k => $v)
                {
                    $userName = $v->belongsToUser()->first()?$v->belongsToUser->nickname:"";
                    $time = $v->created_at;
                    $content = $v->hasOneContent()->first()?$v->hasOneContent->content:"";
                    $reply = [];
                    $replys = TweetReply::where('tweet_id',$v->id)->where('reply_id','=',null)
                        ->orderBy('like_count','desc')->limit(3)->get();
                    $reply_count = TweetReply::select('id')->where('tweet_id',$v->id)->where('reply_id','=',null)
                        ->orderBy('like_count','desc')->get()->count();
                    $i = 1;
                    $grade = 0;
                    if($replys)
                    {
                        foreach ($replys as $kk => $vv)
                        {
                            if($v->anonymity==0){
                                $replyName = '匿名';
                            }else{
                                $replyName = $vv->belongsToUser()->first()?$vv->belongsToUser->nickname:"";
                            }
                            $reply_child = [
                                'reply_name'=>$replyName,
                                'grade'=>$vv->grade >= 9.8 ? 9.8:$vv->grade,
                                'content'=>$vv->content,
                            ];
                            $i = $i+1;
                            $grade = $grade+$vv->grade;
                            array_push($reply,$reply_child);
                        }
                        $grade = ($grade/$i) >= 9.8 ? 9.8 : round($grade/$i,1);
                    }
                    if($v->browse_times>10000){
                        $browse_times = round(($v->browse_times)/10000,1).' 万次';
                    }else{
                        $browse_times = $v->browse_times.' 次';
                    }
                    $like_count = $v->like_count;
                    $retweet_count = $v->retweet_count;
                    if($v->original==0){
                        $is_original = false;
                    }else{
                        $is_original = true;
                    }
                    if($is_original){
                        $original_tweet = $v->hasOneOriginal->hasOneContent->content;
                        $original_tweet_id = $v->hasOneOriginal->id;
                        $original_avatar = $this->protocol.$v->hasOneOriginal->hasOneContent->avatar;
                        $original_name = $v->hasOneOriginal->hasOneContent()->first()?$v->hasOneOriginal->hasOneContent->nickname:"";
                        $original_id = $v->original;
                        $prefix_tweet = [
                            'original_tweet'=>$original_tweet,
                            'original_avatar'=>$original_avatar,
                            'original_name'=>$original_name,
                            'original_id'=>$original_id,
                            'original_tweet_id'=>$original_tweet_id,
                        ];
                    }else{
                        $prefix_tweet = '';
                    }
                    $cover = $v->screen_shot?$this->protocol.$v->screen_shot:'';
                    $duration = floor((($v->duration)/60));
                    $duration .= ':';
                    $duration .= (($v->duration)%60)<10?'0'.($v->duration)%60:($v->duration)%60;
                    $video = $v->type==3?$this->protocol.$v->transcoding_video:$this->protocol.$v->video;
                    $avatar = $this->protocol.$v->belongsToUser->avatar;
                    $tempData = [
                        'grade' => $grade,
                        'userName'=>$userName,
                        'time'=>$time,
                        'content'=>$content,
                        'reply'=>$reply,
                        'browse_times'=>$browse_times,
                        'like_count'=>$like_count,
                        'reply_count'=>$reply_count,
                        'retweet_count'=>$retweet_count,
                        'is_original'=>$is_original,
                        'prefix_tweet'=>$prefix_tweet,
                        'cover'=>$cover,
                        'duration'=>$duration,
                        'id'=>$v->id,
                        'video'=>$video,
                        'avatar'=>$avatar,
                    ];

                    array_push($data,$tempData);

                }
                return response()->json(['data'=>$data,'advert'=>$advert],200);
            }else{
                $advertising = AdvertisingRotation::where('from_time','<',time())
                    ->where('end_time','>',time())
                    ->where('active','=','1')
                    ->wherehas('advert_category',function ($q){
                        $q->where('id',2);
                    })
                    ->orderBy('time_update')->limit(5)->get();
                $advert = [];
                foreach ($advertising as $K => $v)
                {
                    $address = $v->url;
                    $image = $v->image;
                    $user = $v->belongsToUser()->first()?$v->belongsToUser->nickname:"";
                    $tempAdvertisingData = [
                        'address' => $address,
                        'image' => $image,
                        'user' => $user,
                    ];
                    array_push($advert,$tempAdvertisingData);
                }
                $data = [];
                $maindata = Tweet::where('active','=',1)->orderBy($orderBy,'desc')->limit($page*20)->get();
                foreach ($maindata as $k => $v) {
                    $userName = $v->belongsToUser()->first()?$v->belongsToUser->nickname:"";
                    $time = $v->created_at;
                    $content = $v->hasOneContent()->first()?$v->hasOneContent->content:'';
                    if ($v->browse_times > 10000) {
                        $browse_times = round(($v->browse_times) / 10000, 1) . ' 万次';
                    } else {
                        $browse_times = $v->browse_times . ' 次';
                    }
                    if ($v->original == 0) {
                        $is_original = false;
                    } else {
                        $is_original = true;
                    }
                    $cover = $v->screen_shot ? $this->protocol . $v->screen_shot : '';
                    $duration = floor((($v->duration) / 60));
                    $duration .= ':';
                    $duration .= (($v->duration) % 60) < 10 ? '0' . ($v->duration) % 60 : ($v->duration) % 60;
                    $video = $v->type==3?$this->protocol.$v->transcoding_video:$this->protocol.$v->video;
                    $avatar = $this->protocol.$v->belongsToUser->avatar;
                    $tempData = [
                        'userName' => $userName,
                        'time' => $time,
                        'content' => $content,
                        'browse_times' => $browse_times,
                        'is_original' => $is_original,
                        'cover' => $cover,
                        'duration' => $duration,
                        'id' => $v->id,
                        'video'=>$video,
                        'avatar'=>$avatar,
                    ];

                    array_push($data, $tempData);
                }
                return response()->json(['data'=>$data,'advert'=>$advert],200);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function match(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            $type = $request->get('type',0);    //  类别  0 全部    999 我的
            $time = $request->get('time',0);    //  时间条件   0 全部  1 最新发布 2 即将开始 3 即将结束 4 进行中  5 已结束
            $page = $request->get('page',1);
            if($time == 0){
               $timeST = 0;
               $timeET= 0;
               $order = 'desc';
               $by = 'id';
               $symbolST = '>';
               $symbolET = '>';
            }elseif($time == 1){
               $timeST = 0;
               $timeET = 0;
               $order = 'desc';
               $by = 'time_add';
               $symbolST = '>';
               $symbolET = '>';
            }elseif ($time == 2){
                $timeST = time();
                $timeET = time();
                $order = 'desc';
                $by = 'time_start';
                $symbolST = '>';
                $symbolET = '>';
            }elseif ($time == 3){
                $timeST = time();
                $timeET = time();
                $order = 'asc';
                $by = 'time_end';
                $symbolST = '<';
                $symbolET = '>';
            }elseif ($time == 4){
                $timeST = time();
                $timeET = time();
                $order = 'desc';
                $by = 'id';
                $symbolST = '<';
                $symbolET = '>';
            }elseif ($time == 5){
                $timeST = time();
                $timeET = time();
                $order = 'desc';
                $by = 'time_end';
                $symbolST = '<';
                $symbolET = '<';
            }else{
                $timeST = 0;
                $timeET= 0;
                $order = 'desc';
                $by = 'id';
                $symbolST = '>';
                $symbolET = '>';
            }
            if($type == 0){
                $mainData = Filmfests::where('is_open',1)->StartTime($symbolST,$timeST)->EndTime($symbolET,$timeET)
                    ->orderBy($by,$order)->limit($page*4)->get();
            }elseif ($type == 999){
                $mainData = Filmfests::whereHas('user',function ($q) use($user){
                    $q->where('user.id',$user);
                })->orWhereHas('application',function ($q) use($user){
                    $q->where('application_form.user_id','=',$user);
                })->StartTime($symbolST,$timeST)->EndTime($symbolET,$timeET)
                    ->orderBy($by,$order)->limit($page*4)->get();
            }else{
                $mainData = Filmfests::where('is_open',1)->whereHas('category',function ($q) use($type){
                    $q->where('filmfest_category.id',$type);
                })->StartTime($symbolST,$timeST)->EndTime($symbolET,$timeET)
                    ->orderBy($by,$order)->limit($page*4)->get();
            }
            $data = [];
            foreach ($mainData as $k => $v)
            {
                if($v->time_start>time()){
                    $status = '未开始';
                }else{
                    if($v->time_end>time()){
                        $status = '进行中';
                    }else{
                        $status = '已结束';
                    }
                }
                $label = '';
                foreach ($v->application as $kk => $vv)
                {
                    if($vv->user_id == $user){
                        $label = '参与';
                        break;
                    }else{
                        continue;
                    }
                }
                foreach($v->user as $kk => $vv)
                {
                    if($vv->id == $user){
                        $label = '管理';
                        break;
                    }else{
                        continue;
                    }
                }
                $tempData  = [
                    'label'=>$label,
                    'id'=>$v->id,
                    'cover'=>$v->cover,
                    'name'=>$v->name,
                    'detail'=>$v->detail,
                    'status'=>$status,
                    'submit_time'=>date('Y.m.m H:i',$v->submit_start_time).' - '.date('Y.m.d H:i',$v->submit_end_time),
                ];
                array_push($data,$tempData);
            }
            $advertising = AdvertisingRotation::where('from_time','<',time())
                ->where('end_time','>',time())
                ->where('active','=','1')
                ->wherehas('advert_category',function ($q){
                    $q->where('id',1);
                })
                ->orderBy('time_update')->limit(5)->get();
            $advert = [];
            foreach ($advertising as $K => $v)
            {
                $address = $v->url;
                $image = $v->image;
                $user = $v->belongsToUser()->first()?$v->belongsToUser->nickname:"";
                $tempAdvertisingData = [
                    'address' => $address,
                    'image' => $image,
                    'user' => $user,
                ];
                array_push($advert,$tempAdvertisingData);
            }
            return response()->json(['data'=>$data,'advert'=>$advert],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param $id
     * @param string $data
     * @return string
     * 返回所有转发人
     */
    public function searchOriginalTweet($id,$data='')
    {
        $people = Tweet::where('id',$id)->first();
        if($people->retweet !=0){
            $nextid = $people->prexTweet>first()?$people->prexTweet>first()->reply_id:false;
            if($nextid){
                $name = Tweet::find($nextid)->belongsToUser->nickname;
                $data .='//:'.$name;
                $data = $this->searchOriginalTweet($nextid,$data);
            }else{
                return $data;
            }
        }
    }

}
