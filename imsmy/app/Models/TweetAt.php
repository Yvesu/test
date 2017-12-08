<?php

namespace App\Models;

class TweetAt extends Common
{
    protected $table = 'zx_tweet_at';

    protected $fillable = [
        'tweet_id',
        'user_id',
        'nickname',
        'time_add'
    ];

    public $timestamps = false;

    // 关系 多对一关系
    public function belongsToTweet()
    {
        return $this->belongsTo('App\Models\Tweet','tweet_id','id');
    }
}