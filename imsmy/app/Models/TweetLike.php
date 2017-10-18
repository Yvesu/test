<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/21
 * Time: 15:54
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TweetLike extends Common
{
    protected  $table = 'tweet_like';

    protected $fillable = ['user_id','tweet_id'];

    /**
     * 动态点赞与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToManyUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function belongsToManyTweet()
    {
        return $this->belongsTo('App\Models\Tweet', 'tweet_id', 'id');
    }
}