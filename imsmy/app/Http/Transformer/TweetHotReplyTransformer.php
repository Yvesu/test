<?php

namespace App\Http\Transformer;

class TweetHotReplyTransformer extends Transformer
{

    protected $usersTransformer;

    // 二级回复
    protected $tweetSecondaryRepliesTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        TweetSecondaryReplyTransformer $tweetSecondaryRepliesTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->tweetSecondaryRepliesTransformer = $tweetSecondaryRepliesTransformer;
    }

    public function transform($reply)
    {
        return [
            'id'              => $reply->id,
//            'reply_id'        => $reply->reply_id,
            'content'         => $reply->content,
            'created_at'      => strtotime($reply->created_at),
            'user_nickname'   => $reply->belongsToUser()->first()?$reply->belongsToUser()->first()->nickname:'',
            'like_count'      => $reply->like_count,
            'anonymity'       => $reply->anonymity,
            'grade'           => $reply->grade,
//            'father_reply'    => isset($reply->belongsToReply) ? $this->transform($reply->belongsToReply) : [],
            'father_reply'    => isset($reply->belongsToReply) ? [
                'user_id' => $reply->belongsToReply()->first()?($reply->belongsToReply()->first()->belongsToReply()->first()?$reply->belongsToReply()->first()->belongsToReply()->first()->id:''):'',
                'user_nickname' => $reply->belongsToReply()-first()?($reply->belongsToReply()-first()->belongsToUser()->first()?$reply->belongsToReply()-first()->belongsToUser()->first()->nickname:''):'',
            ] : [],
        ];
    }
}