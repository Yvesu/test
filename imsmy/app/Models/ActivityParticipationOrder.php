<?php
namespace App\Models;

/**
 * 赛事奖金分配订单
 * Class ActivityBonusAllocation
 * @package App\Models
 */
class ActivityParticipationOrder extends Common
{
    protected $table = 'zx_activity_participation_order';

    protected $fillable = [
        'activity_id',
        'user_id',
        'level',
        'bonus',
        'status',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}