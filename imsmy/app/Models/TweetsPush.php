<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetsPush extends Model
{
    protected $table = 'tweets_push';

    protected $fillable = ['tweet_id', 'date','time_add','time_update'];

    public $timestamps = false;
}