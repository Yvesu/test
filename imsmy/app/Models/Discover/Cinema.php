<?php

namespace App\Models\Discover;

use App\Models\Common;

/**
 * 发现页面 积分院线表
 * Class Cinema
 * @package App\Models
 */
class Cinema extends Common
{
    protected  $table = 'cinema';

    protected $fillable = [
        'name',
        'intro',
        'background_image',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    public function hasManyPicture()
    {
        return $this -> hasMany('App\Models\Discover\CinemaPicture','film_id','id');
    }


}