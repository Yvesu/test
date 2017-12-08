<?php

namespace App\Models\Project;

use App\Models\Common;
use DB;

/**
 * 用户发布项目礼物日志
 * Class UserProjectConditions
 * @package App\Models
 */
class UserProjectGiftLog extends Common
{
    protected  $table = 'user_project_gift_log';

    protected $fillable = [
        'project_id',
        'user_id',
        'gift_id',
        'type',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}