<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * 用户需求岗位条件
 * Class UserDemandCondition
 * @package App\Models
 */
class UserDemandCondition extends Model
{
    protected  $table = 'zx_user_demand_condition';

    protected $fillable = [
        'job_condition',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}