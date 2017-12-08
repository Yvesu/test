<?php

namespace App\Models\Project;

use App\Models\Common;
use DB;

/**
 * 用户发布项目介绍
 * Class UserProjectConditions
 * @package App\Models
 */
class UserProjectIntro extends Common
{
    protected  $table = 'user_project_intro';

    protected $fillable = [
        'project_id',
        'intro',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}