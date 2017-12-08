<?php

namespace App\Models\Role;

use App\Models\Common;

/**
 * 角色 用户角色明细表
 */
class UserRoleDetails extends Common
{
    protected  $table = 'zx_user_role_details';

    protected $fillable = [
        'role_id',
        'name',
        'age',
        'type_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    public function hasOneBiography()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasOne('App\Models\Role\UserRoleBiography','details_id','id');
    }

    // 角色类型
    public function belongsToType()
    {
        return $this->belongsTo('App\Models\Role\UserRoleType','type_id','id');
    }


}