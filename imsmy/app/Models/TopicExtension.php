<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TopicExtension extends Model
{
    protected  $table = 'topic_extension';

    protected $fillable = [
        'topic_id',
        'screen_shot',
        'video',
        'photo'
    ];

}