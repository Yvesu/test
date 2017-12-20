<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    protected $table='mark';

    protected $fillable=[
        'mark_name',
        'mark_type',
        'mark_content',
        'active',
        'create_time',
    ];

    public $timestamps=false;
}
