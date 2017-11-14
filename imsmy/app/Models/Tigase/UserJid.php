<?php

namespace App\Models\Tigase;

use Illuminate\Database\Eloquent\Model;


class UserJid extends Model
{

    protected $table = 'user_jid';

//    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'jid_id',
        'jid_sha',
        'jid',
    ];

}
