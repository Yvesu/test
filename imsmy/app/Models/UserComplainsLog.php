<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户投诉处理记录日志
 * Class UserComplainsLog
 * @package App\Models
 */
class UserComplainsLog extends Model
{
    protected $table = 'zx_user_complains_log';

    protected $fillable = [
        'admin_id',
        'complain_id',
        'type',
        'time_add',
    ];

    public $timestamps = false;
}