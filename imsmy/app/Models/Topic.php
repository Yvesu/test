<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Topic extends Common
{
    protected  $table = 'topic';

    protected $fillable = [
        'name',
        'active',
        'type',
        'forwarding_time',
        'comment_time',
        'work_count',
        'users_count',
        'like_count',
        'official',
        'comment',
        'icon',
        'hash_icon',
        'user_id',
        'hash_icon',
        'recommend_expires',
        'size',
        'color',
        'compere_id',
    ];

    /**
     * 话题与话题统计表 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hasOneStatistics()
    {
        return $this->hasOne('App\Models\StatisticsTopic', 'topic_id', 'id');
    }

    /**
     * 话题与频道 多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hasManyChannel()
    {
        return $this->belongsToMany('App\Models\Channel', 'channel_topic', 'topic_id', 'channel_id');
    }

    /**
     * 话题与参与者id 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hasManyTopicUser()
    {
        return $this->hasMany('App\Models\TopicUser', 'topic_id', 'id');
    }

    public function hasManyTweetTopic()
    {
        return $this -> hasMany('App\Models\TweetTopic', 'topic_id' ,'id');
    }

    /**
     * 收藏
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToCollection()
    {
        return $this->belongsTo('App\Models\UserCollections', 'id', 'type_id');
    }

    /**
     * 话题的主持人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToCompere()
    {
        return $this -> belongsTo('App\Models\TopicCompere', 'id', 'topic_id');
    }

    public function hasManyTweet()
    {
        return $this -> belongsToMany('App\Models\Tweet','tweet_topic','topic_id','tweet_id');
    }

    /**
     * 按日期查看 刷新与加载
     * @param $query
     * @param $style 1为刷新 2为加载
     * @param $date
     * @return mixed
     */
    public function scopeOfFlushDate($query, $style, $date)
    {

        // 刷新，大于某一时间
        if($style == 1) return $query->where('created_at', '>', $date);

        // 加载，小于某一时间
        return $query->where('created_at', '<', $date);
    }

    /**
     * 时间
     * @param $query
     * @param $date
     * @return mixed
     */
    public function scopeOfDate($query,$date)
    {
        return $query->where('created_at', '<', $date);
    }

    /**
     * 查询审批通过
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * 查询屏蔽
     * @param $query
     * @return mixed
     */
    public function scopeUnable($query)
    {
        return $query->where('active', 2);
    }

    /**
     * 按名称查询
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfName($query, $name)
    {
        // binary 二进制格式
        return $query->where('name', 'LIKE BINARY', $name);
    }

    /**
     * 模糊搜索
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfSearch($query, $name)
    {
        return $query->where('name', 'LIKE BINARY', '%' . $name . '%')
            ->where('name', '!=', $name);
    }

    /**
     * 查询官方发布的话题
     * @param $query
     * @return mixed
     */
    public function scopeOfficial($query)
    {
        return $query->where('official',1);
    }

    /**
     * 话题与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 查询推荐
     * @param $query
     * @return mixed
     */
    public function scopeRecommend($query)
    {
        $now = Carbon::now()->toDateTimeString();
        return $query->where('active', 1)
            ->where('recommend_expires', '>', $now);
    }

    /**
     * 按id 刷新与加载
     * @param $query
     * @param $style 1为刷新 2为加载
     * @param $last_id
     * @return mixed
     */
    public function scopeOfNearbyDate($query, $style, $last_id)
    {
        if($last_id){

            // 刷新，大于某一id
            if($style == 1) return $query->where('id', '>', $last_id);

            // 加载，小于某一id
            return $query->where('id', '<', $last_id);
        }else{

            return $query;
        }
    }

    public function hasOneCompere()
    {
        return $this->hasOne('App\Models\User','id','compere_id');
    }

}