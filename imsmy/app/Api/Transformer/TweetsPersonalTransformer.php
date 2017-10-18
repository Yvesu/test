<?php

namespace App\Api\Transformer;

use CloudStorage;
use Auth;
use App\Models\TweetLike;
use App\Api\Controllers\TweetPlayController;

class TweetsPersonalTransformer extends Transformer
{
    protected $usersTransformer;
    protected $tweetSimplyRepliesTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        TweetSimplyRepliesTransformer $tweetSimplyRepliesTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->tweetSimplyRepliesTransformer = $tweetSimplyRepliesTransformer;
    }

    public function transform($tweet)
    {
        // 获取前三张图片信息
//        if($tweet->photo){
//
//            // 解析成数组
//            $photo = json_decode($tweet->photo,true);
//
//            // 筛选图片的信息，最多三张
//            if(count($photo) <= 3){
//
//                $new_photo = $photo;
//            }else{
//
//                // 取前三张图片
//                $new_photo = array_slice($photo,0,3);
//            }
//        }

        // 判断用户是否为登录状态
        $user = Auth::guard('api')->user();

        // 判断动态类型，如果是图片，观看次数自动＋1
        if(1 === $tweet->type){
            $tweetPlay = new TweetPlayController();
            $tweetPlay -> countIncrement($tweet->id,$user);
        }

        // 原创
        if(!$tweet->hasOneOriginal){

            return [
                'id'            =>  $tweet->id,
                'style'         =>  1,
                'type'          =>  $tweet->type,
                'browse_times'  =>  $tweet->browse_times,
                'like_count'    =>  $tweet->like_count,
                'reply_count'   =>  $tweet->reply_count,
                'already_like'  =>  $user ? (TweetLike::where('user_id',$user->id)->where('tweet_id',$tweet->id)->first() ? 1 : 0) : 0,
                'reply'         =>  $tweet->hasManyTweetReply ? $this->tweetSimplyRepliesTransformer->transformCollection($tweet->hasManyTweetReply->take(3)->all()) : [],
                'location'      =>  $tweet->location ?? '',
                'created_at'    =>  strtotime($tweet->created_at),
                'user_top'      =>  $tweet->user_top,
                'photo'         =>  $tweet->photo === null ? null : CloudStorage::downloadUrl(json_decode($tweet->photo,true)),
                'content'       =>  $tweet->hasOneContent->content,
                'screen_shot'   =>  $tweet->screen_shot === null ? null : CloudStorage::downloadUrl($tweet->screen_shot),
                'video'         =>  $tweet->video === null ? null : CloudStorage::downloadUrl($tweet->video),
                'user'          =>  $this->usersTransformer->transform($tweet->belongsToUser),
                'original'      =>  $tweet->hasOneOriginal == null ? null : $this->transform($tweet->hasOneOriginal),
            ];
        }else{

            return [
                'id'            =>  $tweet->hasOneOriginal->id,
                'style'         =>  1,
                'type'          =>  $tweet->hasOneOriginal->type,
                'browse_times'  =>  $tweet->browse_times,
                'like_count'    =>  $tweet->like_count,
                'reply_count'   =>  $tweet->reply_count,
                'already_like'  =>  $user ? (TweetLike::where('user_id',$user->id)->where('tweet_id',$tweet->id)->first() ? 1 : 0) : 0,
                'reply'         =>  $tweet->hasManyTweetReply ? $this->tweetSimplyRepliesTransformer->transformCollection($tweet->hasManyTweetReply->take(3)->all()) : [],
                'location'      =>  $tweet->location ?? '',
                'created_at'    =>  strtotime($tweet->created_at),
                'user_top'      =>  $tweet->user_top,
                'photo'         =>  $tweet->hasOneOriginal->photo === null ? null : CloudStorage::downloadUrl(json_decode($tweet->hasOneOriginal->photo,true)),
                'content'       =>  $tweet->hasOneContent->content,
                'screen_shot'   =>  $tweet->hasOneOriginal->screen_shot === null ? null : CloudStorage::downloadUrl($tweet->hasOneOriginal->screen_shot),
                'video'         =>  $tweet->hasOneOriginal->video === null ? null : CloudStorage::downloadUrl($tweet->hasOneOriginal->video),
                'user'          =>  $this->usersTransformer->transform($tweet->belongsToUser),
                'original'      =>  $tweet->hasOneOriginal == null ? null : $this->transform($tweet->hasOneOriginal),
            ];
        }
    }
}