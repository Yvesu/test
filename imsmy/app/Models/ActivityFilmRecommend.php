<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityFilmRecommend extends Model
{
    protected $table = 'activity_film_recommend';

    protected $fillable = [
        'work_id',
        'type',
        'expires',
    ];

}
