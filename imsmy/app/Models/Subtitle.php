<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
{
    //
    protected $table = 'subtitle';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'content',
        'start_time',
        'end_time',
        'time_add',
        'time_update',
        'font_id',
        'fragment_id'
    ];

    public $timestamps = false;
}
