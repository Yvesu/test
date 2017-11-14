<?php

namespace App\Models\Discover;

use App\Models\Common;

/**
 * 发现页面 积分院线图片表
 * Class CinemaPicture
 * @package App\Models
 */
class CinemaPicture extends Common
{
    protected  $table = 'cinema_picture';

    protected $fillable = [
        'film_id',
        'picture',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    public function hasOneCinema()
    {
        return $this -> hasOne('App\Models\Discover\Cinema','id','film_id');
    }


}