<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 记录后台人员对网站配置信息的操作
 * Class TopicManageLog
 * @package App\Models
 */
class SitesConfigManageLog extends Model
{
    protected $table = 'zx_sites_config_manage_log';

    protected $fillable = [
        'admin_id',
        'data_id',
        'active',
        'title',
        'icon',
        'hash_icon',
        'keywords',
        'time_add',
    ];

    public $timestamps = false;
}