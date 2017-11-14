<?php
namespace App\Models;

/**
 * 公共设置的一些参数存储
 * Class CommonConfig
 * @package App\Models
 */
class Config extends Common
{
    protected $table = 'config';

    protected $fillable = [
        'gold_proportion',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;
}