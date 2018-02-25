<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinVideo extends Model
{
    protected $table = 'join_video';

    protected $fillable = [
        'name',
        'intro',
        'image',
        'head_video',
        'tail_video',
        'active',
        'recommend',
        'down_count',
        'weight_height',
        'duration',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与动态表关系 多对多
     */
    public function tweet()
    {
        return $this->belongsToMany('App\Models\Tweet','join_video_tweet','join_video_id','tweet_id');
    }
}
