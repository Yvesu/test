<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/4
 * Time: 17:57
 */

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    protected  $table = 'friend';

    protected $fillable=['from', 'to', 'remark', 'top'];

    /**
     * 好友与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User', 'to', 'id');
    }

    /**
     * 查询两个ID是否为好友
     * @param $query
     * @param $from
     * @param $to
     * @return mixed
     */
    public function scopeOfIsFriend($query, $from, $to)
    {
        return $query->where('from', $from)->where('to', $to);
    }
}