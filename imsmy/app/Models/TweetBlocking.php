<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TweetBlocking extends Common
{
    protected  $table = 'tweet_blocking';

    protected $fillable=['reason_id', 'reason', 'tweet_id', 'admin_id', 'time_add', 'time_update'];

    public $timestamps = false;

}