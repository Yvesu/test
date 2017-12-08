<?php

namespace App\Models;

class AdvertisingRotation extends Common
{
    protected  $table = 'advertising_rotation';

    protected $fillable = [
        'name',
        'active',
        'from_time',
        'end_time',
        'type_id',
        'type',
        'url',
        'image',
        'user_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 在有效期内
     * @param $query
     * @return mixed
     */
    public function scopeRecommend($query)
    {
        return $query->where('active', 1)->where('from_time', '<=', getTime())->where('end_time', '>', getTime());
    }

    /**
     * 过期
     * @param $query
     * @return mixed
     */
    public function scopeOverdue($query)
    {
        return $query->where('active', 1)->where('end_time', '<', getTime());
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

    /**
     * 查询未审批
     * @param $query
     * @return mixed
     */
    public function scopeWait($query)
    {
        return $query->where('active', 0);
    }

    /**
     * 查询屏蔽
     * @param $query
     * @return mixed
     */
    public function scopeForbid($query)
    {
        return $query->where('active', 2);
    }

    /**
     * 类型查询
     * @param $query
     * @param $type 类型，0为视频，1为图片，2为话题，3为活动，4为网页
     * @return mixed
     */
    public function scopeOfType($query,$type)
    {
        return $query->where('type', $type);
    }


}