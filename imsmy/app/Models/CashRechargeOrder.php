<?php

namespace App\Models;

/**
 * 现金充值订单
 * Class CashRecharge
 * @package App\Models
 */
class CashRechargeOrder extends Common
{

    public $table = 'cash_recharge_order';

    public $fillable = [
        'user_id',
        'order_number',
        'money',
        'gold_num',
        'pay_type',
        'status',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}
