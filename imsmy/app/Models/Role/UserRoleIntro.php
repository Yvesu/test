<?php

namespace App\Models\Role;

use App\Models\Common;

/**
 * 角色 剧情介绍
 */
class UserRoleIntro extends Common
{
    protected  $table = 'zx_user_role_intro';

    protected $fillable = [
        'role_id',
        'intro',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    


}