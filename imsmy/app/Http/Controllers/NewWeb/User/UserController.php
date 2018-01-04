<?php

namespace App\Http\Controllers\NewWeb\User;

use App\Models\AdvertisingRotation;
use App\Models\Tweet;
use App\Models\TweetReply;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //
    private $paginate = 5;

    private $protocol = 'http://';

    public function index(Request $request)
    {
        try{
            $layout = $request->get('layout',1);
            $type = $request->get('type',1);
            $orderBy = $request->get('orderBy',1);
            $page = $request->get('page',1);
            $user = \Auth::guard('api')->user()->id;
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
                if($type == 1){
                    $data = [];
//                    $maindata = Tweet::where('active','=',1)->orderBy($orderBy,'desc')->limit($page*($this->paginate))->get();
                    $maindata1 = Tweet::where('active','=',1)->orderBy($orderBy,'desc');
                    $maindata = $maindata1->where('user_id','=',$user)->orWhereHas('belongsToUser',function ($q) use($user){
                        $q->whereHas('hasManySubscriptionsFrom',function ($qq) use($user){
                            $qq -> where('to','=',$user);
                        });
                    })->limit($page*($this->paginate))->get();
                    foreach ($maindata as $k => $v)
                    {
                        $userName = $v->belongsToUser->nickname;
                        $time = $v->created_at;
                        $content = $v->hasOneContent->content;
                        $reply = [];
                        $replys = TweetReply::where('tweet_id',$v->id)->where('reply_id','=',null)
                            ->orderBy('like_count','desc')->limit(3)->get();
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
                                    'grade'=>$vv->grade,
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
                        $reply_count = $v->reply_count;
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
                        $avatar = $this->protocol.$v->belongsToUser->avatar;
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
                }elseif ($type == 2){
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
                                'grade'=>$vv->grade,
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
                        $reply_count = $v->reply_count;
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
                    $advertising = AdvertisingRotation::where('from_time','<',time())
                        ->where('end_time','>',time())
                        ->where('active','=','1')
                        ->orderBy('time_update')->limit(5)->get();
                    $advert = [];
                    foreach ($advertising as $K => $v)
                    {
                        $address = $v->url;
                        $image = $v->image;
                        $user = $v->belongsToUser()->nickname;
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
                        $userName = $v->belongsToUser->nickname;
                        $time = $v->created_at;
                        $content = $v->hasOneContent->content;
                        $reply = [];
                        $replys = TweetReply::where('tweet_id',$v->id)->where('reply_id','=',null)
                            ->orderBy('like_count','desc')->limit(3)->get();
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
                                    'grade'=>$vv->grade,
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
                        $reply_count = $v->reply_count;
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
                }
            }else{
                if($type == 1){
                    $data = [];
//                    $maindata = Tweet::where('active','=',1)->orderBy($orderBy,'desc')->limit($page*20)->get();
                    $maindata1 = Tweet::where('active','=',1)->orderBy($orderBy,'desc');
                    $maindata = $maindata1->where('user_id','=',$user)->orWhereHas('belongsToUser',function ($q) use($user){
                        $q->whereHas('hasManySubscriptionsFrom',function ($qq) use($user){
                            $qq -> where('to','=',$user);
                        });
                    })->limit($page*($this->paginate))->get();
                    foreach ($maindata as $k => $v)
                    {
                        $userName = $v->belongsToUser->nickname;
                        $time = $v->created_at;
                        $content = $v->hasOneContent->content;
//                        $reply = [];
//                        $replys = TweetReply::where('tweet_id',$v->id)->where('reply_id','=',null)
//                            ->orderBy('like_count','desc')->limit(3)->get();
//                        $i = 1;
//                        $grade = 0;
//                        foreach ($replys as $kk => $vv)
//                        {
//                            if($v->anonymity==0){
//                                $replyName = '匿名';
//                            }else{
//                                $replyName = $vv->belongsToUser->nickname;
//                            }
//                            $reply_child = [
//                                'reply_name'=>$replyName,
//                                'grade'=>$vv->grade,
//                                'content'=>$vv->content,
//                            ];
//                            $i = $i+1;
//                            $grade = $grade+$vv->grade;
//                            array_push($reply,$reply_child);
//                        }
//                        $grade = ($grade/$i) >= 9.8 ? 9.8 : round($grade/$i,1);
                        if($v->browse_times>10000){
                            $browse_times = round(($v->browse_times)/10000,1).' 万次';
                        }else{
                            $browse_times = $v->browse_times.' 次';
                        }
//                        $like_count = $v->like_count;
//                        $reply_count = $v->reply_count;
//                        $retweet_count = $v->retweet_count;
                        if($v->original==0){
                            $is_original = false;
                        }else{
                            $is_original = true;
                        }
//                        if($is_original){
//                            $original_tweet = $v->hasOneOriginal->hasOneContent->content;
//                            $original_avatar = $this->protocol.$v->hasOneOriginal->hasOneContent->avatar;
//                            $original_name = $v->hasOneOriginal->hasOneContent->nickname;
//                            $original_id = $v->original;
//                            $prefix_tweet = [
//                                'original_tweet'=>$original_tweet,
//                                'original_avatar'=>$original_avatar,
//                                'original_name'=>$original_name,
//                                'original_id'=>$original_id,
//                            ];
//                        }else{
//                            $prefix_tweet = '';
//                        }
                        $cover = $v->screen_shot?$this->protocol.$v->screen_shot:'';
                        $duration = floor((($v->duration)/60));
                        $duration .= ':';
                        $duration .= (($v->duration)%60)<10?'0'.($v->duration)%60:($v->duration)%60;
                        $video = $v->type==3?$this->protocol.$v->transcoding_video:$this->protocol.$v->video;
                        $avatar = $this->protocol.$v->belongsToUser->avatar;
                        $tempData = [
//                            'grade' => $grade,
                            'userName'=>$userName,
                            'time'=>$time,
                            'content'=>$content,
//                            'reply'=>$reply,
                            'browse_times'=>$browse_times,
//                            'like_count'=>$like_count,
//                            'reply_count'=>$reply_count,
//                            'retweet_count'=>$retweet_count,
                            'is_original'=>$is_original,
//                            'prefix_tweet'=>$prefix_tweet,
                            'cover'=>$cover,
                            'duration'=>$duration,
                            'id'=>$v->id,
                            'video'=>$video,
                            'avatar'=>$avatar,
                        ];

                        array_push($data,$tempData);

                    }
                }elseif ($type == 2){
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
//                        $reply = [];
//                        $replys = TweetReply::where('tweet_id',$v->id)->where('reply_id','=',null)
//                            ->orderBy('like_count','desc')->limit(3)->get();
//                        $i = 1;
//                        $grade = 0;
//                        foreach ($replys as $kk => $vv)
//                        {
//                            if($v->anonymity==0){
//                                $replyName = '匿名';
//                            }else{
//                                $replyName = $vv->belongsToUser->nickname;
//                            }
//                            $reply_child = [
//                                'reply_name'=>$replyName,
//                                'grade'=>$vv->grade,
//                                'content'=>$vv->content,
//                            ];
//                            $i = $i+1;
//                            $grade = $grade+$vv->grade;
//                            array_push($reply,$reply_child);
//                        }
//                        $grade = ($grade/$i) >= 9.8 ? 9.8 : round($grade/$i,1);
                        if($v->browse_times>10000){
                            $browse_times = round(($v->browse_times)/10000,1).' 万次';
                        }else{
                            $browse_times = $v->browse_times.' 次';
                        }
//                        $like_count = $v->like_count;
//                        $reply_count = $v->reply_count;
//                        $retweet_count = $v->retweet_count;
                        if($v->original==0){
                            $is_original = false;
                        }else{
                            $is_original = true;
                        }
//                        if($is_original){
//                            $original_tweet = $v->hasOneOriginal->hasOneContent->content;
//                            $original_avatar = $this->protocol.$v->hasOneOriginal->hasOneContent->avatar;
//                            $original_name = $v->hasOneOriginal->hasOneContent->nickname;
//                            $original_id = $v->original;
//                            $prefix_tweet = [
//                                'original_tweet'=>$original_tweet,
//                                'original_avatar'=>$original_avatar,
//                                'original_name'=>$original_name,
//                                'original_id'=>$original_id,
//                            ];
//                        }else{
//                            $prefix_tweet = '';
//                        }
                        $cover = $v->screen_shot?$this->protocol.$v->screen_shot:'';
                        $duration = floor((($v->duration)/60));
                        $duration .= ':';
                        $duration .= (($v->duration)%60)<10?'0'.($v->duration)%60:($v->duration)%60;
                        $video = $v->type==3?$this->protocol.$v->transcoding_video:$this->protocol.$v->video;
                        $avatar = $this->protocol.$v->belongsToUser->avatar;
                        $tempData = [
//                            'grade'=>$grade,
                            'userName'=>$userName,
                            'time'=>$time,
                            'content'=>$content,
//                            'reply'=>$reply,
                            'browse_times'=>$browse_times,
//                            'like_count'=>$like_count,
//                            'reply_count'=>$reply_count,
//                            'retweet_count'=>$retweet_count,
                            'is_original'=>$is_original,
//                            'prefix_tweet'=>$prefix_tweet,
                            'cover'=>$cover,
                            'duration'=>$duration,
                            'id'=>$v->id,
                            'video'=>$video,
                            'avatar'=>$avatar,
                        ];

                        array_push($data,$tempData);

                    }
                }else{
                    $advertising = AdvertisingRotation::where('from_time','<',time())
                        ->where('end_time','>',time())
                        ->where('active','=','1')
                        ->orderBy('time_update')->limit(5)->get();
                    $advert = [];
                    foreach ($advertising as $K => $v)
                    {
                        $address = $v->url;
                        $image = $v->image;
                        $user = $v->belongsToUser()->nickname;
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
                        $userName = $v->belongsToUser->nickname;
                        $time = $v->created_at;
                        $content = $v->hasOneContent->content;
//                        $reply = [];
//                        $replys = TweetReply::where('tweet_id',$v->id)->where('reply_id','=',null)
//                            ->orderBy('like_count','desc')->limit(3)->get();
//                        $i = 1;
//                        $grade = 0;
//                        foreach ($replys as $kk => $vv)
//                        {
//                            if($v->anonymity==0){
//                                $replyName = '匿名';
//                            }else{
//                                $replyName = $vv->belongsToUser->nickname;
//                            }
//                            $reply_child = [
//                                'reply_name'=>$replyName,
//                                'grade'=>$vv->grade,
//                                'content'=>$vv->content,
//                            ];
//                            $i = $i+1;
//                            $grade = $grade+$vv->grade;
//                            array_push($reply,$reply_child);
//                        }
//                        $grade = ($grade/$i) >= 9.8 ? 9.8 : round($grade/$i,1);
                        if ($v->browse_times > 10000) {
                            $browse_times = round(($v->browse_times) / 10000, 1) . ' 万次';
                        } else {
                            $browse_times = $v->browse_times . ' 次';
                        }
//                        $like_count = $v->like_count;
//                        $reply_count = $v->reply_count;
//                        $retweet_count = $v->retweet_count;
                        if ($v->original == 0) {
                            $is_original = false;
                        } else {
                            $is_original = true;
                        }
//                        if($is_original){
//                            $original_tweet = $v->hasOneOriginal->hasOneContent->content;
//                            $original_avatar = $this->protocol.$v->hasOneOriginal->hasOneContent->avatar;
//                            $original_name = $v->hasOneOriginal->hasOneContent->nickname;
//                            $original_id = $v->original;
//                            $prefix_tweet = [
//                                'original_tweet'=>$original_tweet,
//                                'original_avatar'=>$original_avatar,
//                                'original_name'=>$original_name,
//                                'original_id'=>$original_id,
//                            ];
//                        }else{
//                            $prefix_tweet = '';
//                        }
                        $cover = $v->screen_shot ? $this->protocol . $v->screen_shot : '';
                        $duration = floor((($v->duration) / 60));
                        $duration .= ':';
                        $duration .= (($v->duration) % 60) < 10 ? '0' . ($v->duration) % 60 : ($v->duration) % 60;
                        $video = $v->type==3?$this->protocol.$v->transcoding_video:$this->protocol.$v->video;
                        $avatar = $this->protocol.$v->belongsToUser->avatar;
                        $tempData = [
//                            'grade' => $grade,
                            'userName' => $userName,
                            'time' => $time,
                            'content' => $content,
//                            'reply'=>$reply,
                            'browse_times' => $browse_times,
//                            'like_count'=>$like_count,
//                            'reply_count'=>$reply_count,
//                            'retweet_count'=>$retweet_count,
                            'is_original' => $is_original,
//                            'prefix_tweet'=>$prefix_tweet,
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
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found']);
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
