<?php
namespace App\Models;

class PayType extends Common
{
    protected  $table = 'pay_type';

    protected $fillable = [
        'name',
        'pay_type',
        'active',
        'status',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}