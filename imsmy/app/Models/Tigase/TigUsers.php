<?php

namespace App\Models\Tigase;

use Illuminate\Database\Eloquent\Model;


class TigUsers extends Model
{

    protected $table = 'tig_users';

//    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'uid',
        'user_id',
        'sha1_user_id',
        'user_pw',
        'acc_create_time',
        'last_login',
        'last_logout',
        'online_status',
        'failed_logins',
        'account_status'
    ];

    protected $hidden = ['password'];

}
