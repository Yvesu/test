<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keywords extends Model
{
    //
    protected $table = 'keywords';

    protected $primaryKey = 'id';

    protected $fillable = [
        'keyword',
        'count_sum',
        'count_day',
        'count_week',
        'create_at',
        'update_at',
        'type',
        'count_sum_pv',
        'count_day_pv',
        'count_week_pv',
        'count_prev_week_pv',
        'count_prev_week_ip'
    ];

    public $timestamps = false;

}
