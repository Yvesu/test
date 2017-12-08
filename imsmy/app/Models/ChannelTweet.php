<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/7
 * Time: 11:59
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ChannelTweet extends Model
{
    protected  $table = 'channel_tweet';

    protected $fillable = ['channel_id', 'tweet_id'];

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