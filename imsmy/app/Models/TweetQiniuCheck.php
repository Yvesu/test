<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetQiniuCheck extends Model
{
    protected $table = 'tweet_qiniu_check';

    protected $fillable = [
        'tweet_id',
        'user_id',
        'image_qpulp',
        'qpolitician',
        'tupu_video',
        'create_time',
        'update_time',
    ];

    public $timestamps = false;
}
