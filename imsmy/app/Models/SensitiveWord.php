<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensitiveWord extends Model
{
    protected $table = 'sensitive_word';

    protected $fillable = [
        'sensitive_word',
        'create_at',
    ];

    public $timestamps = false;
}
