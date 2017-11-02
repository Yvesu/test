<?php

namespace App\Models;

class HelpContent extends Common
{
    protected  $table = 'zx_help_content';

    protected $fillable = [
        'content',
    ];

    public $timestamps = false;

}