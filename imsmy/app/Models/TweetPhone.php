<?php

namespace App\Models;

/**
 * 动态的手机发布系统
 * Class TweetPhone
 * @package App\Models
 */
class TweetPhone extends Common
{
    protected $table = 'tweet_phone';

    protected $fillable = [
        'phone_type',
        'phone_os',
        'camera_type',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

}