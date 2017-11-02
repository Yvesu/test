<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/21
 * Time: 16:01
 */

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';

    protected $fillable = [
        'user_id',
        'notice_user_id',
        'type',
        'type_id',
        'intro',
        'status'
    ];

    /**
     * 提醒与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}