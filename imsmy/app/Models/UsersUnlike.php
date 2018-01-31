<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersUnlike extends Model
{
    protected $table = 'users_unlike';

    protected $fillable = [
        'user_id',
        'type',
        'work_id',
    ];
}
