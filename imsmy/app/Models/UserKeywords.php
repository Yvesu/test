<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserKeywords extends Model
{
    protected $table = 'user_keywords';

    protected $fillable = [
        'user_id',
        'keyword_id',
        'create_time',
        'update_time',
    ];

    public $timestamps = false;

}
