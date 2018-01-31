<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserUserGroupRoleGroup extends Model
{
    //
    protected $table = 'filmfest_user_user_group_role_group';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_group_id','role_group_id',
        'time_add','time_update',
    ];

    public $timestamps = false;
}
