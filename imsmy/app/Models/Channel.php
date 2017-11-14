<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/19
 * Time: 16:40
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected  $table = 'channel';

    protected $fillable = [
        'name',
        'ename',
        'icon',
        'hash_icon',
        'active',
        'forwarding_time',
        'comment_time',
        'work_count',
        'sort',
    ];

    /**
     * 频道与用户的 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyUserChannel()
    {
        return $this->hasMany('App\Models\UserChannel', 'channel_id', 'id');
    }

    /**
     * 查询可用的频道
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active',1);
    }

    /**
     * 频道与动态的 多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToManyTweet()
    {
        return $this->belongsToMany('App\Models\Tweet', 'channel_tweet', 'channel_id', 'tweet_id');
    }
}