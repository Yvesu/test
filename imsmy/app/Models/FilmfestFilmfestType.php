<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmfestFilmfestType extends Model
{
    //
    protected $table = 'filmfest_filmtype';

    protected $primaryKey = 'id';

    protected $fillable = [
        'filmfest_id','type_id','time_add','time_update',
    ];

    public $timestamps = false;

}
