<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/21
 * Time: 15:58
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Subscription extends Common
{
    protected $table = 'subscription';

    protected $fillable = ['from', 'to', 'unread'];

    /**
     * 被订阅者与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User', 'to', 'id');
    }

    /**
     * 查询两个ID是否为关注关系
     * @param $query
     * @param $from
     * @param $to
     * @return mixed
     */
    public function scopeOfAttention($query, $from, $to)
    {
        return $query->where('from', $from)->where('to', $to);
    }
}