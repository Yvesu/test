<?php

namespace App\Api\Transformer;

use App\Models\TweetLike;
use CloudStorage;
use Auth;

class TweetsNearbyTransformer extends Transformer
{
    private $usersWithSubTransformer;

    public function __construct(
        UsersWithSubTransformer $usersWithSubTransformer
    )
    {
        $this->usersWithSubTransformer = $usersWithSubTransformer;

    }

    public function transform($tweet)
    {
        $user = Auth::guard('api')->user();

        return [
            'id'            =>  $tweet->id,
            'video'         =>  CloudStorage::downloadUrl($tweet->video),
            'already_like'  =>  $user ? (TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user->id)->count() ? '1' : '0') : '0',
            'like_count'    =>  $tweet->like_count,
            'reply_count'   =>  $tweet->reply_count,
            'retweet_count' =>  $tweet->retweet_count,
            'content'       =>  $tweet->hasOneContent->content,
            'browse_times'  =>  $tweet->browse_times,
            'screen_shot'   =>  CloudStorage::downloadUrl($tweet->screen_shot),
            'user'          =>  $this->usersWithSubTransformer->transform($tweet->belongsToUser),
            'created_at'    =>  strtotime($tweet->created_at)
        ];
    }
}