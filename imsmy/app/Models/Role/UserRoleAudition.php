<?php

namespace App\Models\Role;

use App\Models\Common;

/**
 * 角色 用户角色试镜表
 */
class UserRoleAudition extends Common
{
    protected  $table = 'zx_user_role_audition';

    protected $fillable = [
        'role_id',
        'content',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    


}