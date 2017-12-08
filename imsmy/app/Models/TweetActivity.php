<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetActivity extends Model
{
    protected $table = 'tweet_activity';

    protected $fillable = [
        'activity_id',
        'tweet_id',
        'user_id',
        'like_count',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;

    /**
     * 通过tweet_id查询
     * @param $query
     * @param $tweet_id
     * @return mixed
     */
    public function scopeOfTweetID($query, $tweet_id)
    {
        return $query->where('tweet_id', $tweet_id);
    }

    /**
     * 参加该赛事的用户
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasOneUser()
    {
        return $this -> hasOne('App\Models\User','id','user_id');
    }

    /**
     * 参加该赛事的动态
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasOneTweet()
    {
        return $this -> hasOne('App\Models\Tweet','id','tweet_id');
    }

    public function belongsToActivity()
    {
        return $this -> belongsTo('App\Models\Activity','activity_id','id');
    }

    /**
     * 求不同排列方式下的向上或向下
     *
     * @param $query
     * @param string $order 排序方式 1->'like_count', 2->'id'
     * @param object $tweet  动态集合
     * @param int $sort  1上一个或2下一个
     * @return mixed
     */
    public function scopeOfTypeSort($query, $order, $tweet, $sort)
    {
        switch($sort) {
            // 上
            case 1:
                return $query -> orderBy($order) -> where($order, '>', $tweet->$order);
                break;
            // 下
            case 2:
                return $query -> orderBy($order,'desc') -> where($order, '<', $tweet->$order);
                break;
        }
    }

}