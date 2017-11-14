<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TweetReplyLike extends  Model
{
    protected $table = 'tweet_reply_like';

    protected $fillable = ['user_id', 'tweet_reply_id'];

    /**
     * 动态点赞与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function belongsToTweetReply()
    {
        return $this->belongsTo('App\Models\TweetReply', 'tweet_reply_id', 'id');
    }
}

