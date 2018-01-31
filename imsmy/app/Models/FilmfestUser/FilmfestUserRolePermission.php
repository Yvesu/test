<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserRolePermission extends Model
{
    //
    protected $table = 'filmfest_user_role_permission';

    protected $primaryKey = 'id';

    protected $fillable = [
        'role_id','permission_id','time_add','time_update',
    ];

    public $timestamps = false;
}
