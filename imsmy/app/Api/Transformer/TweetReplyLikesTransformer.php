<?php

namespace App\Api\Transformer;


class TweetReplyLikesTransformer extends Transformer
{
    protected $usersTransformer;

    public function __construct(UsersTransformer $usersTransformer)
    {
        $this->usersTransformer = $usersTransformer;
    }
    public function transform($reply_like)
    {
        return [
            'id'            => $reply_like->id,
            'user'          => $this->usersTransformer->transform($reply_like->belongsToUser),
            'created_at'    => strtotime($reply_like->created_at)
        ];
    }
}