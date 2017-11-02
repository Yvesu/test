<?php

namespace App\Models\Role;

use App\Models\Common;

/**
 * 角色 用户角色小传表
 */
class UserRoleBiography extends Common
{
    protected  $table = 'zx_user_role_biography';

    protected $fillable = [
        'details_id',
        'intro',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    


}