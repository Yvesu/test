<?php

namespace App\Models\Address;

use Illuminate\Database\Eloquent\Model;

class AddressCounty extends Model
{
    //
    protected $table = 'address_county';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','Name','Code','Pid','Tid','Cid','time_add','time_update'
    ];

    public $timestamps = false;
}
