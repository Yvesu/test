<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TweetContent extends Model
{
    protected  $table = 'zx_tweet_content';

    /**
     * @var array
     */
    protected $fillable = [
        'tweet_id',
        'content',

    ];

    /**
     * 内容与动态 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToTweet()
    {
        return $this->belongsTo('App\Models\Tweet','tweet_id','id');
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
        return $query->where('content', 'LIKE BINARY', $name);
    }

    /**
     * 模糊搜索
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfSearch($query, $name)
    {
        return $query->where('content', 'LIKE BINARY', '%' . $name . '%')
            ->where('content', '!=', $name);
    }


}