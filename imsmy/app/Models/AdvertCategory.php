<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertCategory extends Model
{
    //
    protected $table = 'advert_category';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','time_add','time_update'
    ];

    protected $timestamp =false;

    public function advert()
    {
        return $this->hasMany('App\Models\AdvertisingRotation','category_id','id');
    }
}
