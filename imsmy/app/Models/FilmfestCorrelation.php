<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmfestCorrelation extends Model
{
    //
    protected $table = 'filmfest_correlation';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','url','filmfest_id','time_add','time_update'
    ];

    public $timestamps = false;

    public function filmfest()
    {
        return $this->belongsTo('App\Models\Filmfests','filmfest_id','id');
    }
}
