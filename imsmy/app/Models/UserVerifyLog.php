<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVerifyLog extends Model
{
    protected $table = 'zx_user_verify_log';

    protected $fillable = [
        'admin_id',
        'verify_id',
        'type',
        'time_add',
    ];

    public $timestamps = false;
}