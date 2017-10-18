<?php

namespace App\Models;

/**
 * 现金金币兑换比例表
 * Class CashGoldConversion
 * @package App\Models
 */
class CashGoldConversion extends Common
{

    public $table = 'cash_gold_conversion';

    public $fillable = [
        'gold_num',
        'money',
        'status',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}
