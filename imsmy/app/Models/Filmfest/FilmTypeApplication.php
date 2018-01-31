<?php

namespace App\Models\Filmfest;

use Illuminate\Database\Eloquent\Model;

class FilmTypeApplication extends Model
{
    //
    protected $table = 'film_type_application';

    protected $primaryKey = 'id';

    protected $fillable = [
        'application_id','type_id','time_add','time_update'
    ];

    public $timestamps = false;
}
