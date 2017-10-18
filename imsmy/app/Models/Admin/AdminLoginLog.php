<?php
namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

/**
 * 管理员登录日志
 *
 * Class AdminLoginLog
 * @package App\Models\Admin
 */
class AdminLoginLog extends Model
{

    protected $table = 'admin_login_log';

    protected $fillable = [
        'aid',
        'ip',
        'time',
    ];

}