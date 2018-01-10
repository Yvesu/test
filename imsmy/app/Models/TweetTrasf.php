<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetTrasf extends Model
{
    protected $table = 'tweet_trans';

    protected $fillable = [
        'tweet_id',
        'active',
    ];

}
