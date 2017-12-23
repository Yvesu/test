<?php

namespace App\Models;

class Activity extends Common
{
    protected  $table = 'activity';

    protected $fillable = [
        'user_id',
        'active',
        'official',
        'bonus',
        'comment',
        'forwarding_time',
        'comment_time',
        'work_count',
        'users_count',
        'like_count',
        'location',
        'icon',
        'status',
        'expires',
        'recommend_start',
        'recommend_expires',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 活动与活动介绍视频扩展表 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hasOneExtension()
    {
        return $this->hasOne('App\Models\ActivityExtension', 'activity_id', 'id');
    }

    /**
     * 活动与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 活动与频道 多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hasManyChannel()
    {
        return $this->belongsToMany('App\Models\FilmfestFilmType', 'filmfest_filmtype', 'filmfest_id', 'type_id');
    }

    /**
     * 活动与动态 多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hasManyTweets()
    {
        return $this->belongsToMany('App\Models\Tweet', 'tweet_activity', 'activity_id', 'tweet_id') -> withPivot('like_count');
    }

    /**
     * 参加该赛事的动态及用户
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyTweetActivity()
    {
        return $this -> hasMany('App\Models\TweetActivity','activity_id','id');
    }

    /**
     * 查询有效期内不同类型的赛事
     * @param $query
     * @return mixed
     */
    public function scopeOfType($query,$type)
    {
        switch($type) {
            // 推荐
            case 1:
                return $query->where('recommend_expires', '>', getTime());
                break;
            // 热门
            case 2:
                return $query->orderBy('forwarding_time', 'DESC');
                break;
            // 最新
            case 3:
                return $query->orderBy('id', 'DESC');
                break;
            default :
                return $query;
        }
    }

    /**
     * 查询有效期内的赛事
     * @param $query
     * @return mixed
     */
    public function scopeOfExpires($query)
    {
        return $query->where('expires', '>', getTime());
    }

    /**
     * 查询已完成的赛事
     * @param $query
     * @return mixed
     */
    public function scopeOfOver($query)
    {
        return $query->where('expires', '<', getTime());
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
        return $query->where('comment', 'LIKE BINARY', $name);
    }

    /**
     * 模糊搜索
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfSearch($query, $name)
    {
        return $query->where('comment', 'LIKE BINARY', '%' . $name . '%')
            ->where('comment', '!=', $name);
    }

    /**
     * 查询推荐
     * @param $query
     * @return mixed
     */
    public function scopeRecommend($query)
    {
        return $query->where('active', 1)
            ->where('recommend_start', '<', getTime())
            ->where('recommend_expires', '>', getTime());
    }
}