<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filmfests extends Model
{
    //
    protected $table = 'filmfests';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','time_add','time_update','time_start','time_end',
        'logo','cover','address','cost','submit_end_time','submit_start_time',
        'period','file_address'
    ];

    public $timestamps = false;

    public function productions()
    {
        return $this->belongsToMany('App\Models\Productions');
    }

    public function filmFestType()
    {
        return $this->belongsToMany('App\Models\FilmfestFilmType','filmfest_filmtype','filmfest_id','type_id');
    }
}