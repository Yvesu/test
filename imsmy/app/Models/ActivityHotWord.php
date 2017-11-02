<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityHotWord extends Model
{
    protected  $table = 'activity_hot_word';

    protected $fillable = [
        'activity_id',
        'hot_word',
        'hot_count',
        'last_time',
    ];

    public $timestamps = false;
}
