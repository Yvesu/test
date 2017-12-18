<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmfestFilmType extends Model
{
    //
    protected $table = 'filmfest_film_type';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','time_add','time_update',
    ];

    public $timestamps = false;
}
