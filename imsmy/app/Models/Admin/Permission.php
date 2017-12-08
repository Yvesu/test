<?php
/**
 * Created by PhpStorm.
 * User: Mabiao
 * Date: 2016/3/16
 * Time: 14:31
 */

namespace app\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected  $table = 'permission_b';

    protected $fillable = ['name', 'description'];

}