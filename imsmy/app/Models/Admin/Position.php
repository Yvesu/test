<?php
/**
 * Created by PhpStorm.
 * User: Mabiao
 * Date: 2016/3/15
 * Time: 14:34
 */

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected  $table = 'position_b';

    protected $fillable = ['name', 'description', 'dept_id', 'permissions'];

    /**
     * 多对一关系 多个职位对应一个部门
     */
    public function belongsToDepartment()
    {
        return $this->belongsTo('App\Models\Admin\Department', 'dept_id', 'id');
    }

    /**
     * 一对多关系  部门 -> 职位
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyAdmin()
    {
        return $this->hasMany('App\Models\Admin\Administrator', 'position_id', 'id');
    }

}