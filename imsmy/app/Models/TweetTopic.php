<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/2
 * Time: 16:02
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TweetTopic extends Model
{
    protected $table = 'tweet_topic';

    protected $fillable = ['topic_id', 'tweet_id'];

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

}