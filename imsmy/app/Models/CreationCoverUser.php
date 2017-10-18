<?php
namespace App\Models;

class CreationCoverUser extends Common
{
    protected  $table = 'creation_cover_user';

    protected $fillable = [
        'cover_id',
        'user_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}