<?php

namespace App\Models\Project;

use App\Models\Common;
use DB;

/**
 * 用户发布项目补充条件
 * Class UserProjectConditionsSupplement
 * @package App\Models
 */
class UserProjectConditionsSupplement extends Common
{
    protected  $table = 'user_project_conditions_supplement';

    protected $fillable = [
        'project_id',
        'amount',
        'content',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 条件与项目 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToProject()
    {
        return $this->belongsTo('App\Models\Project\UserProject','project_id','id');
    }

}