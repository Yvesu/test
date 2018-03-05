<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserIntegralInCome extends Model
{
    protected $table = 'user_integral_income_log';

    protected $fillable = [
        'user_id',
        'up_number',
        'up_count',
        'up_type',
        'from_id',
        'status',
        'create_at',
    ];

    public $timestamps = false;
}
