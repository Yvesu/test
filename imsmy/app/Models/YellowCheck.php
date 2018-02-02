<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YellowCheck extends Model
{
    protected $table = 'tweet_to_qiniu';

    protected $fillable = [
        'tweet_id',
        'create_time',
        'active',
    ];

    public $timestamps = false;
}
