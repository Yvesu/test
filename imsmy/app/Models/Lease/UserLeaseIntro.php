<?php

namespace App\Models\Lease;

use App\Models\Common;

/**
 * 用户租赁商品介绍
 * Class UserDemand
 * @package App\Models
 */
class UserLeaseIntro extends Common
{
    protected  $table = 'zx_user_lease_intro';

    protected $fillable = [
        'lease_id',
        'intro',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}