<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TweetTop extends Model
{
    protected $table = 'zx_tweet_top';

    protected $fillable = ['tweet_id','top_expires','recommend_expires','time_add','time_update'];

    public $timestamps = false;

    // 关系 一对一关系
    public function hasOneTweet()
    {
        return $this->hasOne('App\Models\Tweet','id','tweet_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与管理员表多对一关系    置顶操作员
     */
    public function belongstoToper()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','toper_id','id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与管理员表多对一关系    推荐操作员
     */
    public function belongsToRecommender()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','recommender_id','id');
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