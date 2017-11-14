<?php
namespace App\Models;

class TopicCompereApply extends Common
{
    protected $table = 'topic_compere_apply';

    protected $fillable = [
        'icon',
        'comment',
        'user_id',
        'topic_id',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}