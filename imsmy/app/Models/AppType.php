<?php
namespace App\Models;

class AppType extends Common
{
    protected  $table = 'app_type';

    protected $fillable = [
        'name',
        'is_encryption',
        'key',
        'status',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

}