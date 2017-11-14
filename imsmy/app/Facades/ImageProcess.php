<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/20
 * Time: 14:28
 */

namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class ImageProcess extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'image_process';
    }
}