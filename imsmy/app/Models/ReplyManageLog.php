<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 记录后台人员对评论状态的操作
 * Class ReplyManageLog
 * @package App\Models
 */
class ReplyManageLog extends Model
{
    protected $table = 'zx_reply_manage_log';

    protected $fillable = [
        'admin_id',
        'data_id',
        'active',
        'time_add',
    ];

    public $timestamps = false;
}