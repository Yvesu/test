<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinVideo extends Model
{
    protected $table = 'join_video';

    protected $fillable = [
        'name',
        'intro',
        'image',
        'head_video',
        'tail_video',
        'active',
        'recommend',
        'down_count',
        'weight_height',
        'duration',
    ];
}
