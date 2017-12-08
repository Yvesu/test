<?php

namespace App\Models;

/**
 * 金币账户表
 * Class GoldAccount
 * @package App\Models
 */
class GoldAccount extends Common
{
    protected  $table = 'gold_account';

    protected $fillable = [
        'user_id',
        'gold_total',
        'gold_avaiable',
        'gold_used',
        'gold_frozen',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}
