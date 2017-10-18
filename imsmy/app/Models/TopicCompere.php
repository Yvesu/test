<?php
namespace App\Models;

class TopicCompere extends Common
{
    protected $table = 'topic_compere';

    protected $fillable = [
        'topic_id',
        'user_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    public function belongsToUser()
    {
        return $this -> belongsTo('App\Models\User', 'user_id', 'id');
    }


}