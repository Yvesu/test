<?php

namespace App\Models;

/**
 * 金币流通记录表
 * Class GoldTransaction
 * @package App\Models
 */
class GoldTransaction extends Common
{
    protected  $table = 'gold_transaction';

    protected $fillable = [
        'user_from',
        'user_to',
        'num',
        'style',
        'style_id',
        'intro',
        'type',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}
