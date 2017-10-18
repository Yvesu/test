<?php

namespace App\Api\Transformer;

use Auth;
use App\Models\TweetReplyLike;
use App\Models\User;

class TweetThirdlyRepliesTransformer extends Transformer
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

            // 判断登录用户是否已经关注要查询人的信息
            $already_like = TweetReplyLike::where('user_id',$user_from->id)
                -> where('tweet_reply_id',$reply->id)
                -> get()
                -> first();
        }

        return [
            'id'             => $reply->id,
            'reply_id'       => $reply->reply_id,
            'content'        => $reply->content,
            'created_at'     => strtotime($reply->created_at),
            'user'            => $reply->anonymity === 0 ? $this->usersTransformer->transform($reply->belongsToUser) : (object)NULL,
            'reply_user'     => $reply->reply_user_id ? $this->usersTransformer->transform(User::findOrFail($reply->reply_user_id)) : (object)NULL,
            'anonymity'       => $reply->anonymity,
            'like_count'     => $reply->like_count,
            'already_like'   => $already_like ?  '1' : '0',
        ];
    }
}