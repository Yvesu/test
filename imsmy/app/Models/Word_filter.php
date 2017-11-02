<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word_filter extends Model
{
    //
    protected $table = 'word_filter';

    protected $primaryKey = 'id';

    protected $fillable = [
        'keyword',
        'count_sum_ip',
        'create_at',
        'update_at',
        'count_sum_pv',
        
    ];

    public $timestamps = false;

}
