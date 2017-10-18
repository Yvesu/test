<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Qiniu\Http\Request;

class TweetTrophyConfig extends Model
{
    // 奖杯配置表
    protected  $table = 'tweet_trophy_config';

    protected $fillable = [
        'name',
        'num',
        'status',
        'picture',
        'time_active_start',
        'time_active_end',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 状态为在用
     */
    public function scopeStatus($query)
    {
        return $query -> where('status','1') -> where('time_active_start','<=',getTime()) -> where('time_active_end','>=',getTime());
    }

    /**
     * 过期
     * @param $query
     * @return mixed
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 1)->where('time_active_end', '<', getTime());
    }

    /**
     * 查询可用
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 查询未审批
     * @param $query
     * @return mixed
     */
    public function scopeWait($query)
    {
        return $query->where('status', 0);
    }

    /**
     * 查询屏蔽
     * @param $query
     * @return mixed
     */
    public function scopeForbid($query)
    {
        return $query->where('status', 2);
    }

}