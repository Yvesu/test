<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StatisticsActivity extends Model
{
    protected  $table = 'statistics_activity';

    protected $fillable = [
        'activity_id',
        'forwarding_times',
        'comment_times',
        'work_count',
        'like_count',
        'users_count',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;

    /**
     * 话题统计表与 话题一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hasOneActivity()
    {
        return $this->hasOne('App\Models\Activity', 'id', 'activity_id');
    }

}