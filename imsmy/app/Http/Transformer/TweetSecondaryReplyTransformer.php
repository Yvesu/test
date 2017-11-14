<?php

namespace App\Http\Transformer;

class TweetSecondaryReplyTransformer extends Transformer
{

    protected $usersTransformer;

    public function __construct(UsersTransformer $usersTransformer)
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($reply)
    {

        return [
            'id'             => $reply->id,
//            'reply_id'       => $reply->reply_id,
            'content'        => $reply->content,
            'created_at'     => strtotime($reply->created_at),
            'user'           => $this->usersTransformer->transform($reply->belongsToUser),
            'like_count'     => $reply->like_count,
        ];
    }
}