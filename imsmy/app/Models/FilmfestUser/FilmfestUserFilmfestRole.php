<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserFilmfestRole extends Model
{
    //
    protected $table = 'filmfest_user_filmfest_role';

    protected $primaryKey = 'id';

    protected $fillable = [
        'role_id','filmfest_id','time_add','time_update',
    ];

    public $timestamps = false;
}
