<?php
namespace App\Models;

class XmppUserFiles extends Common
{
    protected $table = 'xmpp_user_files';

    protected $fillable = [
        'user_from',
        'user_to',
        'address',
        'type',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}