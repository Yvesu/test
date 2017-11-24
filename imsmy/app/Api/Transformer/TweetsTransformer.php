<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/21
 * Time: 16:10
 */

namespace App\Api\Transformer;

use App\Models\TweetLike;
use App\Models\TweetReply;
use CloudStorage;
use Auth;
class TweetsTransformer extends Transformer
{
    private $usersTransformer;

    private $channelsTransformer;

    private $tweetRepliesTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        ChannelsTransformer $channelsTransformer,
        TweetRepliesTransformer $tweetRepliesTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->channelsTransformer = $channelsTransformer;
        $this->tweetRepliesTransformer = $tweetRepliesTransformer;
    }

    public function transform($tweet)
    {
        $user = Auth::guard('api')->user();
        $replies = TweetReply::where('tweet_id', $tweet->id)->orderBy('created_at', 'desc')->take(3)->get();
        return [
            'id'            =>  $tweet->id,
            'type'          =>  $tweet->type,
            'video'         =>  CloudStorage::downloadUrl($tweet->video),
            'photo'         =>  $tweet->photo == null ? [] : CloudStorage::downloadUrl(json_decode($tweet->photo,true)),
            'already_like'  =>  $user ? TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user->id)->count() : 0,
            'like_count'    =>  $tweet->like_count,
            'reply_count'   =>  $tweet->reply_count,
            'retweet_count' =>  $tweet->retweet_count,
            'reply'         =>  $this->tweetRepliesTransformer->transformCollection($replies->all()),
            'content'       =>  $tweet->hasOneContent->content,
            'location'      =>  $tweet->location,
            'shot_width_height' =>  $tweet->shot_width_height,
            'screen_shot'   =>  CloudStorage::downloadUrl($tweet->screen_shot),
            'channel'       =>  $this->channelsTransformer->transformCollection($tweet->belongsToManyChannel->all()),
            'user'          =>  $this->usersTransformer->transform($tweet->belongsToUser),
            'original'      =>  $tweet->hasOneOriginal == null ? null : $this->transform($tweet->hasOneOriginal),
            'retweet'       =>  $tweet->retweet,
            'visible'       =>  $tweet->visible,
            'created_at'    =>  strtotime($tweet->created_at)
        ];
    }
}