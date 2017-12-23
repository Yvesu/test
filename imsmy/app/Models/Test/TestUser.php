<?php

namespace App\Models\Test;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class TestUser extends Model implements AuthenticatableContract
{
    use Authenticatable,CanResetPassword, Authorizable;
    //
    protected $table = 'test_user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'password',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与作品对应关系  一对多
     */
    public function production()
    {
        return $this->hasMany('App\Models\Tweet','user_id','id');
    }
}
