<?php

namespace App\Models\Project;

use App\Models\Common;
use DB;

/**
 * 项目进展
 * Class UserProjectConditions
 * @package App\Models
 */
class UserProjectProgress extends Common
{
    protected  $table = 'user_project_progress';

    protected $fillable = [
        'project_id',
        'name',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}