<?php

namespace App\Api\Transformer;


class TweetRepliesTransformer extends Transformer
{
    public function transform($reply)
    {
        return [
            'id'             => $reply->id,
            'user_id'        => $reply->user_id,
            'tweet_id'       => $reply->tweet_id,
            'reply_id'       => $reply->reply_id,
            'content'        => $reply->content,
            'created_at'     => strtotime($reply->created_at)
        ];
    }
}