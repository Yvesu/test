<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionFilmType extends Model
{
    //
    protected $table = 'production_filmtype';

    protected $primaryKey = 'id';

    protected $fillable = [
        'join_type_id','production_id','time_add','time_update'
    ];

    public $timestamps = false;
}
