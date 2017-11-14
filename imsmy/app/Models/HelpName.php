<?php

namespace App\Models;

class HelpName extends Common
{
    protected  $table = 'zx_help_name';

    protected $fillable = [
        'name',
        'content_id',
        'url',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 内容
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneContent()
    {
        return $this -> hasOne('App\Models\HelpContent', 'id', 'content_id');
    }

}