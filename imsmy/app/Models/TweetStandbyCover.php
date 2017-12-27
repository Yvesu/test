<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetStandbyCover extends Model
{
    //
    protected $table = 'tweet_standby_cover';

    protected $primaryKey = 'id';

    protected $fillable = [
        'tweet_id','screen_shot','time_add',
    ];

    public $timestamps = false;
}
