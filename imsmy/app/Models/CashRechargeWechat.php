<?php

namespace App\Models;

class CashRechargeWechat extends Common
{
    protected  $table = 'cash_recharge_wechat';

    protected $fillable = [
        'appid',
        'bank_type',
        'cash_fee',
        'fee_type',
        'is_subscribe',
        'mch_id',
        'nonce_str',
        'openid',
        'out_trade_no',
        'sign',
        'time_end',
        'total_fee',
        'trade_type',
        'transaction_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}
