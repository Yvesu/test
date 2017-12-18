<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmfestsProductions extends Model
{
    //
    protected $table = 'filmfests_productions';

    protected $primaryKey = 'id';

    protected $fillable = [
        'filmfests_id','productions_id','time_add','time_update',
    ];

    public $timestamps = false;
}
