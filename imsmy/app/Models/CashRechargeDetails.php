<?php

namespace App\Models;

/**
 * 现金充值主表
 * Class CashRecharge
 * @package App\Models
 */
class CashRechargeDetails extends Common
{

    public $table = 'cash_recharge_details';

    public $fillable = [
        'user_id',
        'money',
        'gold',
        'order_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required'
    ];
}
