<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserFilmfestFilmtypeAwards extends Model
{
    //
    protected $table = 'filmfest_user_filmfest_filmtype_awards';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','quota','lt_time','gt_time','filmfest_filmtype_id','time_add','time_update'
    ];

    public $timestamps = false;

    public function filmfestFilmtype()
    {
        return $this->belongsTo('App\Models\FilmfestFilmfestType','filmfest_filmtype_id','id');
    }
}
