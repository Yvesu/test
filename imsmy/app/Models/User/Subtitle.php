<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
{
    protected  $table = 'subtitle';

    protected $fillable = [
        'name',
        'content',
        'start_time',
        'end_time',
        'time_add',
        'time_update',
        'font_id',
        'fragment_id',
    ];

    public  $timestamps = false;

}
