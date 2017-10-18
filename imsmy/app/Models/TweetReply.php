<?php

namespace App\Models;

class TweetReply extends  Common
{
    protected $table = 'tweet_reply';

    protected $fillable = [
        'user_id',
        'tweet_id',
        'reply_id',
        'reply_user_id',
        'barrage',
        'content',
        'like_count',
        'grade',
        'anonymity',
        'status'
    ];

    /**
     * 动态与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 评论与父级评论
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToReply()
    {
        return $this->belongsTo('App\Models\TweetReply','reply_id','id');
    }

    /**
     * 查询正常
     * @param $query
     * @return mixed
     */
    public function scopeStatus($query)
    {
        return $query->where('status', 0);
    }

    /**
     * 查询非匿名
     * @param $query
     * @return mixed
     */
    public function scopeOpen($query)
    {
        return $query->where('anonymity', 0);
    }
}

