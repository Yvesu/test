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
        return $this->belongsToMany('App\Models\TweetProduction','filmfests_tweet_production','filmfests_id','tweet_productions_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与电影节单元关系  多对多关系
     */
    public function filmFestType()
    {
        return $this->belongsToMany('App\Models\FilmfestFilmType','filmfest_filmtype','filmfest_id','type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与竞赛作品关系表对应  一对多
     */
    public function filmefestProduction()
    {
        return $this->hasMany('App\Models\FilmfestsProduction','filmfests_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与报名表对应，一对多
     */
    public function application()
    {
        return $this->hasMany('App\Models\Filmfest\Application','filmfests_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与竞赛类别表关系  反向一对多
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Filmfest\FilmfestCategory','category_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与电影节角色
     */
    public function role()
    {
        return $this->belongsToMany('App\Models\FilmfestUser\FilmfestUserRole','filmfest_user_filmfest_role','filmfest_id','role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与可以管理电影节或竞赛的用户的关系  多对多
     */
    public function user()
    {
        return $this->belongsToMany('App\Models\User','filmfest_user_filmfest_user','filmfest_id','user_id');
    }

    /**
     * @param $query
     * @param $symbo
     * @param $time
     * @return mixed
     * 筛选开始时间
     */
    public function scopeStartTime($query,$symbo,$time)
    {
        return $query->where('time_start',$symbo,$time);
    }

    /**
     * @param $query
     * @param $symbo
     * @param $time
     * @return mixed
     * 筛选结束时间
     */
    public function scopeEndTime($query,$symbo,$time)
    {
        return $query->where('time_end',$symbo,$time);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与角色组关系 多对多
     */
    public function role_group()
    {
        return $this->belongsToMany('App\Models\FilmfestUser\FilmfestUserRoleGroupFilmfest','filmfest_user_role_group_filmfest','filmfest_id','role_group_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与竞赛管理员管理日志关系  1对多
     */
    public function filmfestUserReviewLog()
    {
        return $this->hasMany('App\Models\FilmfestUser\FilmfestUserReviewLog','filmfest_id','id');
    }

    public function user_group()
    {
        return $this->hasMany('App\Models\FilmfestUser\FilmfestUserUserGroup','filmfest_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * 与总竞赛表id
     */
    public function activity()
    {
        return $this->hasOne('App\Models\Activity','id','active_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与竞赛相关网站关系
     */
    public function correlation()
    {
        return $this->hasMany('App\Models\FilmfestCorrelation','filmfest_id','id');
    }
    
}
