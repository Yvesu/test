<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TopicUser extends Model
{
    protected $table = 'topic_user';

    protected $fillable = ['user_id', 'topic_id','status'];

    /**
     * 话题与参与者 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToTopic()
    {
        return $this->belongsTo('App\Models\Topic','topic_id','id');
    }

    /**
     * 话题与参与者 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 未删除所参与话题动态的用户
     * @param $query
     * @return mixed
     */
    public function scopeStatus($query)
    {
        return $query -> where('status',1);
    }

    /**
     * 按id 如果非第一次请求，将请求小于某一id的值
     * @param $query
     * @param $last_id
     * @return mixed
     */
    public function scopeOfSecond($query, $last_id)
    {
        if($last_id){

            // 加载，小于某一id
            return $query->where('id', '<', $last_id);
        }else{

            return $query;
        }

    }
}