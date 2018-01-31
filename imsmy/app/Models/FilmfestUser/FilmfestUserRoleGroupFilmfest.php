<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserRoleGroupFilmfest extends Model
{
    //
    protected $table = 'filmfest_user_role_group_filmfest';

    protected $primaruKey = 'id';

    protected $fillable = [
        'role_group_id','filmfest_id','time_add','time_update'
    ];

    public $timestamps = false;
}
