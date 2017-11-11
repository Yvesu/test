<?php

namespace App\Models\Address;

use Illuminate\Database\Eloquent\Model;

class AddressProvince extends Model
{
    //
    protected $table = 'address_province';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','Name','Code','Pid','time_add','time_update'
    ];

    public $timestamps = false;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与城市关系 一对多
     */
    public function city()
    {
        return $this->hasMany('App\Models\Address\AddressCity','Pid','Code');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与区县关系 一对多
     */
    public function county()
    {
        return $this->hasMany('App\Models\Address\AddressCity','Tid','id');
    }
}
