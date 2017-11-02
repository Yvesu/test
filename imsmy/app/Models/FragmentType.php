<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FragmentType extends Model
{
    //
    protected $table = 'fragmenttype';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'ename',
        'icon',
        'hash_icon',
        'active',
        'forwarding_time',
        'comment_time',
        'work_count',
        'sort',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;
}
