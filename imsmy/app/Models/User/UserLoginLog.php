<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    //
    protected $table = 'user_login_log';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'login_time',
    ];

    public $timestamps = false;
}
