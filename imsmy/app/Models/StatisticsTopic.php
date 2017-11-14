<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StatisticsTopic extends Model
{
    protected  $table = 'statistics_topic';

    protected $fillable = [
        'topic_id',
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
    public function hasOneTopic()
    {
        return $this->hasOne('App\Models\Topic', 'id', 'topic_id');
    }

    public function hasManyTweetTopic()
    {
        return $this -> hasMany('App\Models\TweetTopic', 'topic_id' ,'topic_id');
    }



}