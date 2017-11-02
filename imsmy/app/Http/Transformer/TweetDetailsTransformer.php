<?php

namespace App\Http\Transformer;

use App\Models\TweetActivity;
use App\Models\TweetLike;
use App\Api\Transformer\UsersTransformer;
use CloudStorage;
use Auth;

class TweetDetailsTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($tweet)
    {

        // 判断是否参与竞赛
        $activity = TweetActivity::with('belongsToActivity')
            -> where('tweet_id',$tweet->id)
            -> first();

        return [
            'id'            =>  $tweet->id,
            'type'          =>  $tweet->type,
            'active'        =>  $tweet->active,
            'activity'      =>  $activity ? $activity -> belongsToActivity -> theme : '',
            'browse_times'  =>  $tweet->browse_times,
            'video'         =>  CloudStorage::downloadUrl($tweet->video),
            'photo'         =>  $tweet->photo === null ? [] : CloudStorage::downloadUrl(json_decode($tweet->photo,true)),
            'like_count'    =>  $tweet->like_count,
            'reply_count'   =>  $tweet->reply_count,
//            'retweet_count' =>  $tweet->retweet_count,
            'content'       =>  $tweet->hasOneContent->content,
            'shot_width_height' =>  $tweet->shot_width_height,  // TODO 待删除
            'screen_shot'   =>  CloudStorage::downloadUrl($tweet->screen_shot),
            'user'          =>  $this->usersTransformer->transform($tweet->belongsToUser),
            'created_at'    =>  strtotime($tweet->created_at),
            'phone_type'    =>  $tweet->belongsToPhone ? $tweet->belongsToPhone->phone_type : '',
            'phone_os'    =>  $tweet->belongsToPhone ? $tweet->belongsToPhone->phone_os : '',
        ];
    }
}