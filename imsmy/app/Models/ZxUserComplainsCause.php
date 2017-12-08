<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZxUserComplainsCause extends Model
{
    // 用户举报原因表
    protected  $table = 'zx_user_complains_cause';

    protected $fillable = [
        'content',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 查询在用
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

}