<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Adcode extends Model
{
    protected $table = 'zx_adcode';

    protected $fillable = [
        'citycode',
        'adcode',
        'street',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;

}