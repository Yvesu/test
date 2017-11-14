<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AspectRadio extends Model
{
    //
    protected $table = 'aspect_radio';

    protected $primaryKey = 'id';

    protected $fillable = [
        'aspect_radio',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;
}
