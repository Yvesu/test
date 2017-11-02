<?php

namespace App\Models\Role;

use App\Models\Common;

/**
 * 角色 用户角色表
 *
 * Class UserRole
 * @package App\Models\Role
 */
class UserRole extends Common
{
    protected  $table = 'zx_user_role';

    protected $fillable = [
        'user_id',
        'title',
        'director',
        'film_id',
        'time_from',
        'time_end',
        'period',
        'site',
        'active',
        'cover',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    public function hasOneIntro()
    {
        return $this -> hasOne('App\Models\Role\UserRoleIntro','role_id','id');
    }

    public function hasOneFilm()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasOne('App\Models\FilmMenu','id','film_id');
    }

    public function hasManyDetails()
    {
        return $this->hasMany('App\Models\Role\UserRoleDetails','role_id','id');
    }

    public function hasManyAudition()
    {
        return $this->hasMany('App\Models\Role\UserRoleAudition','role_id','id');
    }

}