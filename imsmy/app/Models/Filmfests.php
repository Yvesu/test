<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filmfests extends Model
{
    //
    protected $table = 'filmfests';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','time_add','time_update','time_start','time_end',
        'logo','cover','address','cost','submit_end_time','submit_start_time',
        'period','file_address','url','count',
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与作品表 多对多关系
     */
    public function tweetProduction()
    {
        return $this->belongsToMany('App\Models\TweetProduction','filmfests_tweet_production','filmfests_id','tweet_production_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与电影节单元关系  多对多关系
     */
    public function filmFestType()
    {
        return $this->belongsToMany('App\Models\FilmfestFilmType','filmfest_filmtype','filmfest_id','type_id');
    }

    public function filmefestProduction()
    {
        return $this->hasMany('App\Models\FilmfestsProduction','filmfests_id','id');
    }
    
}
