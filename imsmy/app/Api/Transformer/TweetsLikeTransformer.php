<?php

namespace App\Api\Transformer;

use CloudStorage;

/**
 * 用户点赞的动态
 * Class TweetsLikeTransformer
 * @package App\Api\Transformer
 */
class TweetsLikeTransformer extends Transformer
{

    public function transform($tweet)
    {
        return [
            'id'            =>  $tweet->belongsToManyTweet->id,
            'type'          =>  $tweet->belongsToManyTweet->type,
            'nickname'      =>  $tweet->belongsToManyTweet->belongsToUser->nickname,     // 发表动态的用户昵称
            'content'       =>  $tweet->belongsToManyTweet->hasOneContent->content,
            'picture'       =>  CloudStorage::downloadUrl($tweet->belongsToManyTweet->type == 0 ? $tweet->belongsToManyTweet->screen_shot : json_decode($tweet->belongsToManyTweet->photo,true)[0]),
            'created_at'    =>  strtotime($tweet->belongsToManyTweet->created_at),  // 发布时间
        ];
    }
}