<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 记录后台人员对用户状态的操作
 * Class ActivityManageLog
 * @package App\Models
 */
class UserManageLog extends Model
{
    protected $table = 'zx_user_manage_log';

    protected $fillable = [
        'admin_id',
        'data_id',
        'active',
        'time_add',
    ];

    public $timestamps = false;
}