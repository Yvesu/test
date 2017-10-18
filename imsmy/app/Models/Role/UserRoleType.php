<?php

namespace App\Models\Role;

use App\Models\Common;

/**
 * 角色 角色类型
 */
class UserRoleType extends Common
{
    protected  $table = 'zx_user_role_type';

    protected $fillable = [
        'name',
        'sort',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    


}