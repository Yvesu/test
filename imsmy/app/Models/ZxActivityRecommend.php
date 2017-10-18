<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZxActivityRecommend extends Model
{
    protected  $table = 'zx_activity_recommend';

    protected $fillable = [
        'activity_id',
        'image',
        'recommend_expires',
        'active',
        'user_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 时间
     * @param $query
     * @return mixed
     */
    public function scopeRecommend($query)
    {
        return $query->where('recommend_expires', '>', getTime());
    }

    /**
     * 查询可用
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }


}