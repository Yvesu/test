<?php

namespace App\Models\Project;

use App\Models\Common;

/**
 * 项目用户支持明细
 * Class UserProjectInvestor
 * @package App\Models
 */
class UserProjectSupport extends Common
{
    protected  $table = 'user_project_support';

    protected $fillable = [
        'user_id',
        'project_id',
        'type',
        'amount',
        'comment',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}