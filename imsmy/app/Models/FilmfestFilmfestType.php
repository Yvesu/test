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

    public function filmfests()
    {
        return $this->belongsToMany('App\Models\Filmfests','filmfest_filmtype','type_id','filmfest_id');
    }

    public function filmfestUserFilmfestFilmtypeAwards()
    {
        return $this->hasMany('App\Models\FilmfestUser\FilmfestUserFilmfestFilmtypeAwards','filmfest_filmtype_id','id');
    }

}
