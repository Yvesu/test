<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserLoginLogSumIp extends Model
{
    //
    protected $table = 'user_login_log_sum_ip';

    protected $primaryKey = 'id';

    protected $fillable = [
        'month',
        'year',
        '0:00',
        '2:00',
        '4:00',
        '6:00',
        '8:00',
        '10:00',
        '12:00',
        '14:00',
        '16:00',
        '18:00',
        '20:00',
        '22:00',
    ];

    public $timestamps = false;
}
