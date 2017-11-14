<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVerify extends Model
{
    protected $table = 'zx_user_verify';

    protected $fillable = [
        'user_id',
        'verify',
        'verify_info',
        'verify_status',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;
}