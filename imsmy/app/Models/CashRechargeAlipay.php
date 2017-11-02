<?php

namespace App\Models;

class CashRechargeAlipay extends Common
{
    protected  $table = 'cash_recharge_alipay';

    protected $fillable = [
        'body',
        'buyer_email',
        'buyer_id',
        'notify_id',
        'notify_time',
        'notify_type',
        'out_trade_no',
        'seller_id',
        'subject',
        'total_fee',
        'trade_no',
        'trade_status',
        'sign',
        'sign_type',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}
