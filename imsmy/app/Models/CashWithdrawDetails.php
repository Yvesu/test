<?php

namespace App\Models;

/**
 * 现金提现表
 * Class CashRecharge
 * @package App\Models
 */
class CashWithdrawDetails extends Common
{

    public $table = 'cash_withdraw_details';

    public $fillable = [
        'user_id',
        'money',
        'gold',
        'order_id',
        'status',
        'cause',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}
