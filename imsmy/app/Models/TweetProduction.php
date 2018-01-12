<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetProduction extends Model
{
    //
    protected $table = 'tweet_production';

    protected $primaryKey = 'id';

    protected $fillable = [
        'is_current','status','tweet_id','time_add','time_update','filmfests_id','join_university_id'
    ];

    public $timestamps = false;

    /**
     * 与动态表   1对1关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tweet()
    {
        return $this->hasOne('App\Models\Tweet','id','tweet_id');
    }

    /**
     * 与电影节表   多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function filmfest()
    {
        return $this->belongsToMany('App\Models\Filmfests','filmfests_tweet_production','tweet_production_id','filmfests_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与大学关系 反向一对多
     */
    public function university()
    {
        return $this->belongsTo('App\Models\Filmfest\JoinUniversity','join_university_id','id');
    }

    public function filmfestFilmType()
    {
        return $this->belongsToMany('AppModels\FilmfestFilmType','production_filmtype','production_id','join_type_id');
    }
}
