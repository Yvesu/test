<?php

namespace App\Models\Make;

use Illuminate\Database\Eloquent\Model;

class TextureMixType extends Model
{
    //
    protected $table = 'texture_mix_type';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;
}
