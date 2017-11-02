<?php

namespace App\Models;

class ActivityBonusSet extends Common
{
    protected  $table = 'activity_bonus_set';

    protected $fillable = [
        'level',
        'count_user',
        'amount',
        'prorata',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

}