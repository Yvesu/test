<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 奖杯收支表 数据类
 * Class TweetTrophyLog
 * @package App\Models
 */
class TweetTrophyLog extends Common
{
    protected $table = 'tweet_trophy_log';

    protected $fillable = [
        'to',
        'from',
        'tweet_id',
        'trophy_id',
        'num',
        'date',
        'time_add',
        'anonymity',
    ];

    public $timestamps = false;

    /**
     * 奖杯日志与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','from','id');
    }

    /**
     * 奖杯日志与奖杯 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToTrophy()
    {
        return $this->belongsTo('App\Models\TweetTrophyConfig','trophy_id','id');
    }

    /**
     * 奖杯日志与动态 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToTweet()
    {
        return $this->belongsTo('App\Models\Tweet','tweet_id','id');
    }

}