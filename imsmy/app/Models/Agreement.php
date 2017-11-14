<?php

namespace App\Models;

class Agreement extends Common
{
    protected $table = 'zx_agreement';

    protected $fillable = [
        'type',
        'title',
        'content',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

}
