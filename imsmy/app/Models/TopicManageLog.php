<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 记录后台人员对话题状态的操作
 * Class TopicManageLog
 * @package App\Models
 */
class TopicManageLog extends Model
{
    protected $table = 'zx_topic_manage_log';

    protected $fillable = [
        'admin_id',
        'data_id',
        'active',
        'time_add',
    ];

    public $timestamps = false;
}