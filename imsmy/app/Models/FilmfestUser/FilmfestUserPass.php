<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserPass extends Model
{
    //
    protected $table = 'filmfest_user_pass';

    protected $primaryKey = 'id';

    protected $fillable = [
        'pass','role_des','key','time_add','time_update'
    ];

    public $timestamps =false;
}
