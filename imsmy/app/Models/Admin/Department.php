<?php
/**
 * Created by PhpStorm.
 * User: mabiao
 * Date: 2016/3/15
 * Time: 14:28
 */

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected  $table = 'department_b';

    protected $fillable = ['name', 'description', 'active', 'permissions'];

    /**
     * 一对多关系  部门 -> 职位
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyPosition()
    {
        return $this->hasMany('App\Models\Admin\Position','dept_id','id');
    }
}