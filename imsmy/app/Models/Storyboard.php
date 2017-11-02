<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storyboard extends Model
{
    //
    protected $table = 'storyboard';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'speed',
        'time_add',
        'time_update',
        'address',
        'fragment_id'
    ];

    public $timestamps = false;
}
