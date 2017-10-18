<?php

namespace App\Models\Project;

use App\Models\Common;
use DB;

/**
 * 项目团队成员
 * Class UserProjectTeam
 * @package App\Models
 */
class UserProjectTeam extends Common
{
    protected  $table = 'user_project_team';

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}