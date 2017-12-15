<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoExitWord extends Model
{
    protected $table = 'noexist_word';

    protected $fillable = [
        'keyword',
        'count_sum_ip',
        'create_at',
        'update_at',
        'count_sum_pv',
    ];

    public $timestamps = false;
}
