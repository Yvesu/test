<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TweetHot extends Common
{
    protected $table = 'zx_tweet_hot';

    protected $fillable = ['tweet_id','top_expires','recommend_expires','time_add','time_updated'];

    public $timestamps = false;

    // 关系 一对一关系
    public function hasOneTweet()
    {
        return $this->hasOne('App\Models\Tweet','id','tweet_id');
    }

    /**
     * 查询置顶热门动态
     * @param $query
     * @return mixed
     */
    public function scopeTop($query)
    {
        return $query->where('top_expires', '>', getTime());
    }

    /**
     * 查询置推荐热门动态
     * @param $query
     * @return mixed
     */
    public function scopeRecommend($query)
    {
        return $query->where('recommend_expires', '>', getTime());
    }
}