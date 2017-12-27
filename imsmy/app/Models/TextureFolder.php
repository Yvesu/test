<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TextureFolder extends Model
{
    protected $table = 'texture_folder';

    protected $fillable = [
        'name',
        'count',
        'sort',
        'active',
        'time_add',
        'time_update',
    ];
    public $timestamps= false;
}
