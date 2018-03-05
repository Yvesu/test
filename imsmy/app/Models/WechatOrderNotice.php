<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WechatOrderNotice extends Model
{
    protected $table = 'wechat_order_notice';

    protected $fillable = [
        'order_id',
        'appid',
        'bank_type',
        'cash_fee',
        'fee_type',
        'is_subscribe',
        'mch_id',
        'nonce_str',
        'openid',
        'out_trade_no',
        'result_code',
        'return_code',
        'sign',
        'time_end',
        'total_fee',
        'trade_type',
        'transaction_id',
    ];

    protected $primaryKey = 'id';

    public $timestamps = false;
}
