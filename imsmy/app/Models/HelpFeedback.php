<?php

namespace App\Models;

class HelpFeedback extends Common
{
    protected  $table = 'zx_help_feedback';

    protected $fillable = [
        'content',
        'connect',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;
    

}