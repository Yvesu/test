<?php

namespace App\Api\Transformer;

use CloudStorage;
use Auth;
use App\Models\TweetReplyLike;

class TweetSecondaryRepliesTransformer extends Transformer
{

    protected $usersTransformer;

    public function __construct(UsersTransformer $usersTransformer)
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($reply)
    {
        // 判断用户是否为登录状态
        $user_from = Auth::guard('api')->user();

        $already_like = '';

        if($user_from) {

            // 判断登录用户是否已点赞
            $already_like = TweetReplyLike::where('user_id',$user_from->id)
                -> where('tweet_reply_id',$reply->id)
                -> get()
                -> first();
        }

        return [
            'id'             => $reply->id,
//            'reply_id'       => $reply->reply_id,
            'content'        => $reply->content,
            'created_at'     => strtotime($reply->created_at),
            'user'           => $this->usersTransformer->transform($reply->belongsToUser),
            'like_count'     => $reply->like_count,
            'already_like'   => $already_like ?  '1' : '0',
        ];
    }
}