<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MixType extends Model
{
    protected $table = 'mix_type';

    protected $fillable = [
        'ename',
        'name',
        'code',
    ];
}
