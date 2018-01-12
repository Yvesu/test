<?php
namespace App\Api\Transformer;

use CloudStorage;

class ActivityTweetsTransformer extends Transformer
{
    protected $usersTransformer;

    public function __construct(UsersTransformer $usersTransformer)
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($tweet)
    {

        return [
            'id'            => $tweet->tweet_id,
            'bonus'         => $tweet->bonus ?? 0,
            'created_at'    => strtotime($tweet->hasOneTweet->created_at),
            'style'         => 1,  // 待删除字段，等安卓删除后删除
            'content'       => $tweet->hasOneTweet->content,
            'screen_shot'   => CloudStorage::downloadUrl($tweet->hasOneTweet->screen_shot),
            'video'         => CloudStorage::downloadUrl($tweet->hasOneTweet->video),
            'user'          => $this->usersTransformer->transform($tweet->hasOneUser),
        ];
    }
}