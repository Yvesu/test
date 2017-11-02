<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/22
 * Time: 17:33
 */

namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class CloudStorage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cloud_storage';
    }
}