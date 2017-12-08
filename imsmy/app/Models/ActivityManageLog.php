<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 记录后台人员对活动状态的操作
 * Class ActivityManageLog
 * @package App\Models
 */
class ActivityManageLog extends Model
{
    protected $table = 'zx_activity_manage_log';

    protected $fillable = [
        'admin_id',
        'data_id',
        'active',
        'time_add',
    ];

    public $timestamps = false;
}