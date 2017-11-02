<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * 用户需求发布的热门城市
 * Class UserDemandCities
 * @package App\Models
 */
class UserDemandCities extends Common
{
    protected  $table = 'zx_user_demand_cities';

    protected $fillable = [
        'name',
        'count',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

}