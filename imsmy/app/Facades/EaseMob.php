<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/4
 * Time: 12:15
 */

namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class EaseMob extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ease_mob';
    }
}