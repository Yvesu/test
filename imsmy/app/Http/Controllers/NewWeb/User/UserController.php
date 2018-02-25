<?php

namespace App\Http\Controllers\NewWeb\User;

use App\Models\AdvertisingRotation;
use App\Models\Filmfests;
use App\Models\Friend;
use App\Models\PrivateLetter;
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
                $maindata = Tweet::where([['active','=',1],['user_id',$my_id],['type','=',0]])
                    ->orWhere([['active','=',1],['user_id',$my_id],['type','=',3]])
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
                $maindata = Tweet::where([['active','=',1],['user_id',$my_id],['type','=',0]])
                    ->orWhere([['active','=',1],['user_id',$my_id],['type','=',3]])
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
                    'submit_time'=>date('Y.m.d H:i',$v->submit_end_time),
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


    /**
     * @return \Illuminate\Http\JsonResponse
     * 新消息数量
     */
    public function privateLetterNewNum()
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            $num = PrivateLetter::select('id')->where('type',0)
                ->where('delete_from',0)
                ->where('delete_to',0)
                ->where('to',$user)
                ->get()->count();
            return response()->json(['data'=>$num],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function privateLetterNew()
    {
        $user = \Auth::guard('api')->user()->id;
        $data = DB::select('select *,count(id) as num FROM private_letter AS pl WHERE pl.to=? AND pl.type=? AND pl.delete_to=? AND pl.delete_from=? GROUP BY pl.from ORDER BY created_at DESC',[$user,0,0,0]);
        if(is_null($data)){
            return response()->json(['data'=>'']);
        }else{
            $letter = [];
            foreach ($data as $k => $v)
            {
                $from_id = $v->from;
                $from = User::find($from_id);
                $from = $from?$from->nickname:'';
                $time = $v->created_at;
                $contetn = $v->content;
                $num = $v->num;
                $tempData = [
                    'from'=>$from,
                    'time'=>$time,
                    'contet'=>$contetn,
                    'num'=>$num,
                ];
                array_push($letter,$tempData);
            }
        }
        return response()->json(['data'=>$letter],200);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 竞赛详情主页
     */
    public function matchIndex(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $webUser = \Auth::guard('api')->user()->id;
            $filmfest = Filmfests::find($filmfest_id);
            if($filmfest){
                $cover = $filmfest->cover;
                $user_id = $filmfest->issue_id;
                $user = User::find($user_id);
                $avatar = $user->avatar;
                $nikcname = $user->nickname;
                $verify_info = $user->verify_info;
                $time = date('Y年m月d日',$filmfest->time_start).'-'.date('Y年m月d日',$filmfest->time_end);
                $address = $filmfest->address;
                $nowTime = time();
                $stopSubmitTime = $filmfest->submit_end_time;
                $stopSelectTime = $filmfest->check_time;
                $stopCheckTime = $filmfest->check_again_time;
                $stopCheckAgainTime = $filmfest->enter_time;
                $endTime = $filmfest->time_end;
                if ($nowTime<$stopSubmitTime){
                    $countDownTime = $stopSubmitTime-$nowTime;
                    $des = '距离投片截止倒计时';
                }else{
                    if($nowTime<$stopSelectTime){
                        $countDownTime = $stopSelectTime-$nowTime;
                        $des = '距离海选截止倒计时';
                    }else{
                        if($nowTime<$stopCheckTime){
                            $countDownTime = $stopCheckTime-$nowTime;
                            $des = '距离复选截止倒计时';
                        }else{
                            if($nowTime<$stopCheckAgainTime){
                                $countDownTime = $stopCheckAgainTime-$nowTime;
                                $des = '距离入围名单公示倒计时';
                            }else{
                                if($nowTime<$endTime){
                                    $countDownTime = $endTime-$nowTime;
                                    $des = '距离颁奖倒计时';
                                }else{
                                    $countDownTime = null;
                                    $des = '已结束';
                                }
                            }
                        }
                    }
                }
                if(is_null($countDownTime)){
                    $countDownTime = '--:--:--';
                }else{
                    $days = floor($countDownTime/86400);
                    $hours = floor(($countDownTime-86400*$days)/3600);
                    $minutes = floor((($countDownTime-86400*$days)-3600*$hours)/60);
                    $seconds = floor((($countDownTime-86400*$days)-3600*$hours)-60*$minutes);
                    $countDownTime = $days.'天  '.$hours.':'.$minutes.':'.$seconds;
                }
                $countDown = [
                    'countDownTime'=>$countDownTime,
                    'des'=>$des,
                ];
                if($webUser == $user_id){
                    if($filmfest->is_open==1){
                        $notice = '';
                        $submitName = '启动竞赛';
                        $submitStatus = 2;
                        $timeAndAddressStatus = 0;
                        $textStatus = 0;
                        $coverStatus =  0;
                    }else{
                        $notice = '请再三确认内容无错误，启动后将无法修改';
                        $submitName = '启动竞赛';
                        $submitStatus = 1;
                        $countDown = [
                            'countDownTime'=>'--:--:--',
                            'des'=>'未启动倒计时',
                        ];
                        $timeAndAddressStatus = 1;
                        $textStatus = 1;
                        $coverStatus =  1;
                    }
                    $operation = [
                        [
                            'name'=>'后台管理中心',
                        ]
                    ];
                }else{
                    if($filmfest->submit_start_time>=time()){
                        $notice = '';
                        $submitName = '提交作品';
                        $submitStatus = 0;
                    }elseif($filmfest->submit_end_time>time()){
                        $notice = '';
                        $submitName = '提交作品';
                        $submitStatus = 1;
                    }else{
                        $notice = '';
                        $submitName = '提交作品';
                        $submitStatus = 0;
                    }
                    $subscription = Subscription::where('from',$webUser)->where('to',$user_id)->first();
                    if($subscription){
                        $subscriptionStatus = '取消关注';
                    }else{
                        $subscriptionStatus = '关注';
                    }
                    $operation = [
                        [
                            'name'=>$subscriptionStatus,
                        ],
                        [
                            'name'=>'发私信'
                        ]
                    ];
                    $timeAndAddressStatus = 0;
                    $textStatus = 0;
                    $coverStatus =  0;
                }

                $correlationData = $filmfest->correlation()->get();
                $correlation = [];
                if($correlationData->count()>0){
                    foreach ($correlationData as $k => $v)
                    {
                        $tempData = [
                            'name'=>$v->name,
                            'url'=>$v->url,
                        ];
                        array_push($correlation,$tempData);
                    }
                }

                //  相关暂时没有写
                $data = [
                    'user_id'=>$user_id,
                    'cover'=>$cover,
                    'avatar'=>$avatar,
                    'nickname'=>$nikcname,
                    'verify_info'=>$verify_info,
                    'operation'=>$operation,
                    'time'=>$time,
                    'address'=>$address,
                    'timeAndAddressStatus'=>$timeAndAddressStatus,
                    'notice'=>$notice,
                    'countDown'=>$countDown,
                    'submit'=>[
                        'name'=>$submitName,
                        'status'=>$submitStatus,
                    ],
                    'correlation'=>$correlation,
                    'filmfestName'=>'第'.$filmfest->period.'届'.$filmfest->name,
                    'issueTime'=>'发布于 '.date('Y-m-d H:i',$filmfest->time_add),
                    'textStatus'=>$textStatus,
                    'coverStatus'=>$coverStatus,
                ];
                return response()->json(['data'=>$data],200);
            }else{
                return response()->json(['message'=>'该竞赛不存在'],200);
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 竞赛详情页关注/取消关注按钮
     */
    public function matchIndexSubscription(Request $request)
    {
        try{
            $user_id = \Auth::guard('api')->user()->id;
            $issue_id = $request->get('user_id');
            if($issue_id == $user_id){
                return response()->json(['message'=>'自己不能关注自己'],200);
            }
            $subscription = Subscription::where('from',$user_id)->where('to',$issue_id)->first();
            if($subscription){
                Subscription::where('from',$user_id)->where('to',$issue_id)->delete();
                return response()->json(['message'=>'取消关注成功'],200);
            }else{
                $newSubscription = new Subscription;
                $newSubscription->from = $user_id;
                $newSubscription->to = $issue_id;
                $newSubscription->created_at = time();
                $newSubscription->updated_at =time();
                $newSubscription->save();
                return response()->json(['message'=>'关注成功'],200);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 发送私信
     */
    public function sendPrivateLetter(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            $user_id = $request->get('user_id',null);
            $content = $request->get('content',null);
            if(is_null($content)){
                return response()->json(['message'=>'私信不能为空'],200);
            }
            DB::beginTransaction();
            if(is_null($user_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $user_id = rtrim($user_id,'|');
            $user_id = explode('|',$user_id);
            foreach ($user_id as $k => $v)
            {
                $newPrivaterLetter = new PrivateLetter;
                $newPrivaterLetter -> from = $user;
                $newPrivaterLetter -> to = $v;
                $newPrivaterLetter -> content = $content;
                $newPrivaterLetter -> created_at = time();
                $newPrivaterLetter -> updated_at = time();
                $newPrivaterLetter -> save();
            }
            DB::commit();
            return response()->json(['message'=>'发送成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function oneUserLetter(Request $request)
    {
        try{
            $from_id = $request->get('from_id');
            $to_id = \Auth::guard('api')->user()->id;
            $mainData = PrivateLetter::where('from',$from_id)->where('to',$to_id)
                ->where('delete_to',0)
                ->where('delete_from',0)
                ->get();
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

}
