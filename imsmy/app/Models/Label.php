<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/31
 * Time: 10:30
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $table = 'label';

    protected $fillable = ['name'];

    /**
     * 查询可用标签
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}