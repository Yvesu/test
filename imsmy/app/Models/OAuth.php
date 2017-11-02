<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/6
 * Time: 14:19
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class OAuth extends Model
{
    protected $table = 'oauth';

    protected $fillable = [
        'user_id',
        'oauth_name',
        'oauth_nickname',
        'oauth_id',
        'oauth_access_token',
        'oauth_expires'
    ];

    protected $hidden = ['oauth_access_token', 'oauth_expires'];

    /**
     * 与 user 表 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hasOneUser()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }
}