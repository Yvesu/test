<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $table = 'user_token';

    protected $fillable = [
        'user_id',
        'token',
        'create_time',
    ];

    public $timestamps = false;
}