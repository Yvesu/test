<?php

namespace App\Models\Lease;

use App\Models\Common;

/**
 * 用户租赁
 * Class UserDemand
 * @package App\Models
 */
class UserLease extends Common
{
    protected  $table = 'zx_user_lease';

    protected $fillable = [
        'user_id',
        'type_id',
        'cost',
        'cost_type',
        'ad',
        'ad_details',
        'accessory',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    public function hasOneType()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasOne('App\Models\Lease\UserLeaseType','id','type_id');
    }

    public function hasOneIntro()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasOne('App\Models\Lease\UserLeaseIntro','lease_id','id');
    }

}