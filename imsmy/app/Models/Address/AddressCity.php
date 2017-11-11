<?php

namespace App\Models\Address;

use Illuminate\Database\Eloquent\Model;

class AddressCity extends Model
{
    //
    protected $table = 'address_city';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','Name','Code','Pid','Tid','time_add','time_update'
    ];

    public $timestamps = false;

    public function county()
    {
        return $this->hasMany('App\Models\Address\AddressCounty','Pid','Code');
    }
}
