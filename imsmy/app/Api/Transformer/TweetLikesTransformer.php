<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/31
 * Time: 14:24
 */

namespace App\Api\Transformer;


class TweetLikesTransformer extends Transformer
{
    protected $usersTransformer;

    public function __construct(UsersTransformer $usersTransformer)
    {
        $this->usersTransformer = $usersTransformer;
    }
    public function transform($tweet_like)
    {
        return [
            'id'            => $tweet_like->id,
            'user'          => $this->usersTransformer->transform($tweet_like->belongsToManyUser),
            'created_at'    => strtotime($tweet_like->created_at)
        ];
    }
}