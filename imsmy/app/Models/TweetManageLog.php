<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 记录后台人员对动态状态的操作
 * Class TweetManageLog
 * @package App\Models
 */
class TweetManageLog extends Model
{
    protected $table = 'zx_tweet_manage_log';

    protected $fillable = [
        'admin_id',
        'data_id',
        'active',
        'channel_ids',
        'top_expires',
        'recommend_expires',
        'topic_ids',
        'activity_ids',
        'push_date',
        'hot',
        'time_add',
    ];

    public $timestamps = false;
}