<?php

namespace App\Models\Project;

use App\Models\Common;
use DB;

/**
 * 用户发布项目投资明细
 * Class UserProjectInvestor
 * @package App\Models
 */
class UserProjectInvestor extends Common
{
    protected  $table = 'user_project_investor';

    protected $fillable = [
        'user_id',
        'project_id',
        'amount',
        'comment',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}