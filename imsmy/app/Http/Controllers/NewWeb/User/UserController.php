<?php

namespace App\Http\Controllers\NewWeb\User;

use App\Models\Tweet;
use App\Models\TweetReply;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
                    $maindata = Tweet::where('active','=',1)->orderBy($orderBy,'desc')->limit($page*($this->paginate))->get();
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
                        $browse_times = round(($v->browse_times)/10000,1);
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
                            $original_avatar = $this->protocol.$v->hasOneOriginal->hasOneContent->avatar;
                            $original_name = $v->hasOneOriginal->hasOneContent->nickname;
                            $original_id = $v->original;
                            $prefix_tweet = [
                                'original_tweet'=>$original_tweet,
                                'original_avatar'=>$original_avatar,
                                'original_name'=>$original_name,
                                'original_id'=>$original_id,
                            ];
                        }else{
                            $prefix_tweet = '';
                        }
                        $cover = $v->screen_shot?$this->protocol.$v->screen_shot:'';
                        $duration = floor((($v->duration)/60));
                        $duration .= ':';
                        $duration .= (($v->duration)%60)<10?'0'.($v->duration)%60:($v->duration)%60;
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
                        $browse_times = round(($v->browse_times)/10000,1);
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
                            $original_avatar = $this->protocol.$v->hasOneOriginal->hasOneContent->avatar;
                            $original_name = $v->hasOneOriginal->hasOneContent->nickname;
                            $original_id = $v->original;
                            $prefix_tweet = [
                                'original_tweet'=>$original_tweet,
                                'original_avatar'=>$original_avatar,
                                'original_name'=>$original_name,
                                'original_id'=>$original_id,
                            ];
                        }else{
                            $prefix_tweet = '';
                        }
                        $cover = $v->screen_shot?$this->protocol.$v->screen_shot:'';
                        $duration = floor((($v->duration)/60));
                        $duration .= ':';
                        $duration .= (($v->duration)%60)<10?'0'.($v->duration)%60:($v->duration)%60;
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
                        ];

                        array_push($data,$tempData);

                    }
                }else{
                    return response()->json(['data'=>'敬请期待'],200);
                }
            }else{
                if($type == 1){
                    $data = [];
                    $maindata = Tweet::where('active','=',1)->orderBy($orderBy,'desc')->limit($page*($this->paginate))->get();
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
                        $browse_times = round(($v->browse_times)/10000,1);
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
                        $browse_times = round(($v->browse_times)/10000,1);
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
                        ];

                        array_push($data,$tempData);

                    }
                }else{
                    return response()->json(['data'=>'敬请期待'],200);
                }
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found']);
        }
    }


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
