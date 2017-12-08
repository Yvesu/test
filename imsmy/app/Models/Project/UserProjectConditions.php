<?php

namespace App\Models\Project;

use App\Models\Common;
use DB;

/**
 * 用户发布项目条件
 * Class UserProjectConditions
 * @package App\Models
 */
class UserProjectConditions extends Common
{
    protected  $table = 'user_project_conditions';

    protected $fillable = [
        'amount',
        'content',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 补充条件 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasManySupplement()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasMany('App\Models\Project\UserProjectConditionsSupplement','project_id','id');
    }



}