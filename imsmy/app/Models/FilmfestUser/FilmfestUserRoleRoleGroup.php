<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserRoleRoleGroup extends Model
{
    //
    protected $table = 'filmfest_user_role_role_group';

    protected $primaryKey = 'id';

    protected $fillabel = [
        'role_id','group_id','time_add','time_update'
    ];

    public $timestamps = false;
}
