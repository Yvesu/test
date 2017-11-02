<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/10
 * Time: 9:44
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    protected  $table = 'blacklist';

    protected $fillable = ['from', 'to'];

    public function hasOneFrom()
    {
        return $this -> hasOne('App\Models\User','id','from');
    }

    public function hasOneTo()
    {
        return $this -> hasOne('App\Models\User','id','to');
    }

    // 验证黑名单中的对应id
    public function scopeOfBlackIds($query,$from,$to)
    {
        return $query -> where('from',$from)->where('to',$to);
    }

}