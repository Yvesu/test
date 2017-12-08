<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topper extends Model
{
    protected $table = 'topper';

    protected $primaryKey = 'id';

    protected $fillable = [
        'works_id',
        'icon',
        'city',
        'province',
        'addr',
        'watch_count',
        'length',
        'describe',
        'life_length',
        'create_at',
        'update_at'
    ];

    public $timestamps = false;
}
