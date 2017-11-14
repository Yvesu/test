<?php

namespace App\Models;

class UserJpush extends Common
{
    protected $table = 'zx_user_jpush';

    protected $fillable = [
        'user_id',
        'jpush_id',
        'active',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;
}