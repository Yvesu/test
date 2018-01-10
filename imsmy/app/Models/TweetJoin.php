<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetJoin extends Model
{
    protected $table = 'tweet_join';

    protected $fillable = [
        'tweet_id',
        'active',
        'join_id',
    ];
}
