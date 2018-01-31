<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserFilmfestUser extends Model
{
    //
    protected $table = 'filmfest_user_filmfest_user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'filmfest_id','user_id','time_add','time_update',
    ];

    public $timestamps = false;
}
