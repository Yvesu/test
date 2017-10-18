<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 员工审批视频暂存表，防止冲突
 * Class TweetTempCheck
 * @package App\Models
 */
class TweetTempCheck extends Model
{
    protected $table = 'zx_tweet_temp_check';

    protected $fillable = [
        'admin_id',
        'data_id',
        'time_add',
    ];

    public $timestamps = false;
}