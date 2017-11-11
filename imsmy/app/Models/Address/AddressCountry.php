<?php

namespace App\Models\Address;

use Illuminate\Database\Eloquent\Model;

class AddressCountry extends Model
{
    //
    protected $table = 'address_country';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id','Name','Code','time_add','time_update'
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与省份的关系 一对多
     */
    public function state()
    {
        return $this->hasMany('App\Models\Address\AddressProvince','Pid','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与城市的关系  一对多
     */
    public function city()
    {
        return $this->hasMany('App\Models\Address\AddressCity','Tid','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与县区关系 一对多
     */
    public function county()
    {
        return $this->hasMany('App\Models\Address\AddressCounty','Cid','id');
    }
}
