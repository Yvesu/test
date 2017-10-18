<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateLetter extends Model
{
    protected  $table = 'private_letter';

    /**
     * @var array
     */
    protected $fillable = [
        'from',
        'to',
        'content',
        'type'
    ];

    /**
     * 私信与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        // 第一个参数为本类所关联的模型,第二个参数为他们关联的ID是本类的字段值(不填默认为id),第三个参数表示第一个参数的字段值(不填默认为id)
        return $this->belongsTo('App\Models\User','from','id');
    }

    /**
     * 按日期查看 $type 状态的消息 时间倒序
     * @param $query
     * @param $type 信息状态  0=>未读 1=>已读
     * @param $date 时间戳
     * @return mixed
     */
    public function scopeOfData($query, $type, $date)
    {
        return $query->where('type', $type)->where('created_at', '<', $date);
    }


}