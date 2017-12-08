<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/7
 * Time: 17:24
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ChannelTopic extends Model
{
    protected  $table = 'channel_topic';

    protected $fillable = ['channel_id', 'topic_id'];

    /**
     * 通过topic_id查询
     * @param $query
     * @param $topic_id
     * @return mixed
     */
    public function scopeOfTopicID($query, $topic_id)
    {
        return $query->where('topic_id', $topic_id);
    }
}