<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserIntegralExpend extends Model
{
    protected $table = 'user_integral_expend_log';

    protected  $fillable = [
        'user_id',
        'pay_number',
        'pay_count',
        'type_id',
        'pay_reason',
        'status',
        'create_at',
        'type',
    ];

    public  $timestamps = false;

    /**
     * 积分支出与用户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }
}
