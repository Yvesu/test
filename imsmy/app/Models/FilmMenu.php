<?php

namespace App\Models;


/**
 * 影片种类
 * Class UserDemandMenu
 * @package App\Models
 */
class FilmMenu extends Common
{
    protected  $table = 'zx_film_menu';

    protected $fillable = [
        'name',
        'sort',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}