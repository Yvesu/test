<?php

namespace App\Models\Project;

use App\Models\Common;
use DB;

/**
 * 用户发布项目
 * 
 * Class UserProject
 * @package App\Models\Project
 */
class UserProject extends Common
{
    protected  $table = 'user_project';

    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'contacts',
        'phone',
        'film_id',
        'city',
        'cover',
        'from_time',
        'end_time',
        'video',
        'scheme',
        'users_count',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 动态与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 项目内容 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneIntro()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasOne('App\Models\Project\UserProjectIntro','project_id','id');
    }

    /**
     * 影片种类 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneFilm()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasOne('App\Models\FilmMenu','id','film_id');
    }

    /**
     * 投资人员 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasManyInvestor()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasMany('App\Models\Project\UserProjectInvestor','project_id','id');
    }

    /**
     * 支持人员 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasManySupport()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasMany('App\Models\Project\UserProjectSupport','project_id','id');
    }

    /**
     * 项目团队 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasManyTeam()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasMany('App\Models\Project\UserProjectTeam','project_id','id');
    }

    /**
     * 项目进展 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasManyProgress()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasMany('App\Models\Project\UserProjectProgress','project_id','id');
    }

    /**
     * 补充条件 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasManySupplement()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasMany('App\Models\Project\UserProjectConditionsSupplement','project_id','id');
    }

}