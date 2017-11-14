<?php

namespace App\Http\Transformer\Mobile;

use App\Http\Transformer\Transformer;
use App\Http\Transformer\UsersTransformer;
use CloudStorage;

class TweetRepliesMobileTransformer extends Transformer
{

    protected $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($reply)
    {

        return [
            'id'              => $reply->id,
            'content'         => $reply->content,
            'created_at'      => $reply->created_at,
            'user'            => $reply->anonymity === 0 ? $this->usersTransformer->transform($reply->belongsToUser) : (object)NULL,
            'like_count'      => $reply->like_count,
            'grade'           => $reply->grade,
        ];
    }
}