<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetsPushStatistics extends Model
{
    protected $table = 'tweets_push_statistics';

    protected $fillable = ['user_id', 'tweet_push_date','time_add'];

    public $timestamps = false;
}