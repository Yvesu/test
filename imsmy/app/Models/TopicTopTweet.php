<?php
namespace App\Models;

class TopicTopTweet extends Common
{
    protected $table = 'topic_top_tweet';

    protected $fillable = [
        'topic_id',
        'tweet_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}