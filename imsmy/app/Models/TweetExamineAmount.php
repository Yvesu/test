<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 员工每日审批视频数量统计表
 * Class TweetExamineAmount
 * @package App\Models
 */
class TweetExamineAmount extends Model
{
    protected $table = 'zx_tweet_examine_amount';

    protected $fillable = [
        'admin_id',
        'amount',
        'date',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;
}