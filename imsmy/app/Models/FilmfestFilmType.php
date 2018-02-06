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

    public function filmFests()
    {
        return $this->belongsToMany('App\Models\Filmfests','filmfest_filmtype','type_id','filmfest_id');
    }

    public function application()
    {
        return $this->belongsToMany('App\Models\Filmfest\Application','film_type_application','type_id','application_id');
    }
}
