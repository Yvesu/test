<?php

namespace App\Models\Filmfest;

use Illuminate\Database\Eloquent\Model;

class FilmfestCategory extends Model
{
    //
    protected $table = 'filmfest_category';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','time_add','time_update',
    ];

    public $timestamps =  false;

    public function filmfests()
    {
        return $this->hasMany('App\Models\Filmfests','category_id','id');
    }
}
