<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserTweetsHistory extends Model
{
    protected  $table = 'zx_user_tweets_history';

    /**
     * @var array
     */
    protected $fillable = [
        'tweet_id',
        'user_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

}