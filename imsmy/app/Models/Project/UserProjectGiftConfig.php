<?php

namespace App\Models\Project;

use App\Models\Common;
use DB;

/**
 * 用户发布项目礼物配置
 * Class UserProjectConditions
 * @package App\Models
 */
class UserProjectGiftConfig extends Common
{
    protected  $table = 'user_project_gift_config';

    protected $fillable = [
        'amount',
        'content',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}