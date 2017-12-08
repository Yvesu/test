<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected  $table = 'location';

    protected $fillable = [
        'formattedAddress',
        'country',
        'province',
        'city',
        'district',
        'township',
        'neighborhood',
        'building',
        'citycode',
        'adcode',
        'street',
        'number',
        'POIName',
        'AOIName',
        'longitude',
        'latitude',
        'lng',
        'lat',
    ];

    /**
     * 位置与动态 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hasManyTweets()
    {
        return $this->hasMany('App\Models\Tweet', 'location_id', 'id');
    }

    public function hasManyUser()
    {
        return $this->hasMany('App\Models\User', 'location_id', 'id');
    }
}