<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserSearchLog 用户搜索日志
 * @package App\Models
 */
class UserSearchLog extends Model
{
    protected $table = 'zx_user_search_log';

    protected $fillable = [
        'user_id',
        'search',
        'time_add',
    ];

    public $timestamps = false;

    /**
     * 搜索日志与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }


}