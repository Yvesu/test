<?php

namespace App\Api\Transformer;

use CloudStorage;
use Auth;
use App\Models\TweetReplyLike;
use App\Models\TweetReply;

class TweetHotRepliesTransformer extends Transformer
{

    protected $usersTransformer;

    // 二级回复
    protected $tweetSecondaryRepliesTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        TweetSecondaryRepliesTransformer $tweetSecondaryRepliesTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->tweetSecondaryRepliesTransformer = $tweetSecondaryRepliesTransformer;
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

        // 二级回复
        $tweetSecondaryReplies = TweetReply::with('belongsToUser')
            -> where('reply_id',$reply->id)
            -> orderBy('id','desc')
            -> status()
            -> get();

        return [
            'id'              => $reply->id,
//            'reply_id'        => $reply->reply_id,
            'content'         => $reply->content,
            'created_at'      => strtotime($reply->created_at),
            'user'            => $reply->anonymity === 0 ? $this->usersTransformer->transform($reply->belongsToUser) : (object)NULL,
            'like_count'      => $reply->like_count,
            'anonymity'       => $reply->anonymity,
            'grade'           => $reply->grade,
            'already_like'    => $already_like ?  '1' : '0',
            'secondary'       => count($tweetSecondaryReplies) ? $this->tweetSecondaryRepliesTransformer->transform($tweetSecondaryReplies->first()) : (object)NULL,
            'secondary_count' => count($tweetSecondaryReplies),
        ];
    }
}