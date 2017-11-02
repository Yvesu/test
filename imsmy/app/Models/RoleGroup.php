<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleGroup extends Model
{
    protected  $table = 'role_group';

    protected $fillable = [
        'name',
        'intro',
        'r_m_ids',
        'admin_id',
        'audit_admin_id',
        'pid',
        'status',
        'path',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

}