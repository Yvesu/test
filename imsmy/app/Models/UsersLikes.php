<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersLikes extends Model
{
    protected $table = 'users_likes';

    protected $fillable = [
        'user_id',
        'channel_id',
    ];
}
