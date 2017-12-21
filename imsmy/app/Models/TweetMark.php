<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetMark extends Model
{
    protected $table='tweet_mark';

    protected $fillable = [
        'tweet_id',
        'active',
        'create_time',
        'mark_id',
    ];

    public $timestamps=false;
}
