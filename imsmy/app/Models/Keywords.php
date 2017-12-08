<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keywords extends Model
{
    //
    protected $table = 'keywords';

    protected $primaryKey = 'id';

    protected $fillable = [
        'keyword',
        'keyword_pinyin',
        'count_sum_pv',
        'count_day_pv',
        'count_week_pv',
        'count_prev_week_pv',
        'count_prev_week_ip',
        'count_sum',
        'count_day',
        'count_week',
        'create_at',
        'update_at',
        'type',
        'count_sum_pv',
        'count_day_pv',
        'count_week_pv',
        'count_prev_week_pv',
        'count_prev_week_ip'
    ];

    public $timestamps = false;

    /**
     * 与片段多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongstofragment()
    {
        return $this->belongsToMany('App\Models\Fragment','keyword_fragment','keyword_id','fragment_id');
    }

    /**
     * 关键词与用户多对多
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongtoManyUser()
    {
        return $this->belongsToMany('App\Models\User','user_keywords','keyword_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToManyTweet()
    {
        return $this->belongsToMany('App\Models\Tweet','keywords_tweet','keyword_id','tweet_id');
    }
}
