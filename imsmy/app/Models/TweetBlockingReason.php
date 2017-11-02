<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TweetBlockingReason extends Common
{
    protected  $table = 'tweet_blocking_reason';

    protected $fillable=['reason', 'active', 'time_add', 'time_update'];

    public $timestamps = false;


}