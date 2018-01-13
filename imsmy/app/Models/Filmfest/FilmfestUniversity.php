<?php

namespace App\Models\Filmfest;

use Illuminate\Database\Eloquent\Model;

class FilmfestUniversity extends Model
{
    //
    protected $table = 'filmfest_university';

    protected $primaryKey = 'id';

    protected $fillable = [
        'filmfest_id','university_id','time_add','time_update'
    ];

    public $timestamps = false;
}
