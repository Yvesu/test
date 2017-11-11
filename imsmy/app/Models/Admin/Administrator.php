<?php

namespace App\Models\Admin;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class Administrator extends Model implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword,SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'administrator_b';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'g_r_ids',
        'avatar',
        'sex',
        'position_id',
        'permissions',
        'annexURL',
        'ID_card_URL',
        'phone',
        'name',
        'secondary_contact_name',
        'secondary_contact_phone',
        'secondary_contact_relationship',
        'user_id',
        'remember_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * 软删除 属性
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
     *  Auth 密码
     */
    public function getAuthPassword()
    {
        // TODO: Implement getAuthPassword() method.
        return $this->password;
    }

    /**
     * 多对一关系 多个管理员对应一个职位
     */
    public function belongsToPosition()
    {
        return $this->belongsTo('App\Models\Admin\Position','position_id','id');
    }

    /**
     * 管理员登录日志
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hasManyLoginLog()
    {
        return $this->hasMany('App\Models\Admin\AdminLoginLog','aid','id');
    }

    /**
     * 一对一关系，绑定管理员在APP端注册的用户
     */
    public function hasOneUser()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }


}
