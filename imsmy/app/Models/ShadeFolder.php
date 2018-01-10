<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShadeFolder extends Model
{
    protected $table = 'shade_folder';

    protected $fillable = [
        'name',
        'sort',
        'count',
        'active',
        'create_time',
        'update_time',
    ];

    public $timestamps = false;
}
