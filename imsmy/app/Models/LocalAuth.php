<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/6
 * Time: 14:16
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class LocalAuth extends Model
{
    protected $table = 'local_auth';

    protected $fillable = ['user_id', 'username', 'password', 'reset_password_at','status'];

    protected $hidden = ['password'];

    /**
     * 与 user 表 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hasOneUser()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }
}