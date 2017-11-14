<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StatisticsUsers extends  Model
{
    protected $table = 'statistics_users';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'fans_count',
        'new_fans_count',
        'follow_count',
        'work_count',
        'retweet_count',
        'trophy_count',
        'collection_count',
        'like_count',
        'topics_count',
        'time_add',
        'time_update',
    ];

    /**
     * 用户统计与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }


}

