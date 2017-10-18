<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * 用户需求记录
 * Class UserDemand
 * @package App\Models
 */
class UserDemand extends Common
{
    protected  $table = 'zx_user_demand';

    protected $fillable = [
        'user_id',
        'job_id',
        'condition_id',
        'film_id',
        'cost',
        'cost_type',
        'cost_unit',
        'city',
        'from_time',
        'end_time',
        'accessory',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 需求岗位种类 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneJob()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasOne('App\Models\UserDemandJob','id','film_id');
    }

    /**
     * 电影种类 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneFilm()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasOne('App\Models\FilmMenu','id','film_id');
    }

    /**
     * 岗位条件 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneCondition()
    {
        // 第二个参数是第一个参数的关联字段，第三个参数是本类的关联字段
        return $this->hasOne('App\Models\UserDemandCondition','id','condition_id');
    }

}