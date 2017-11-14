<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/29
 * Time: 15:34
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BlurClass extends Model
{
    protected  $table = 'blur_class';

    protected $fillable = [
        'name_zh',
        'name_en',
        'icon_sm',
        'icon_lg',
        'active'
    ];

}