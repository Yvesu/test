<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fragment extends Model
{
    //
    protected $table = 'fragment';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'aspect_radio',
        'duration',
        'net_address',
        'cover',
        'name',
        'official',
        'address_country',
        'address_province',
        'address_city',
        'address_county',
        'address_street',
        'integral',
        'cost',
        'size',
        'count',
        'active',
        'time_add',
        'time_update',
        'recommend',
        'vipfree',
        'watch_count',
        'praise',
        'test_results',
        'operator_id',
        'ishot',
        'zip_address',
        'ishottime',
    ];

    public $timestamps = false;

    /**
     * 与字幕暂存表SubtitleTemporary 一对多关系
     */
    public function hasManySubtitleTemporary()
    {
        return $this->hasMany('App\Models\SubtitleTemporary','fragment_id','id');
    }

    /**
     *与关键词_片段表中间表 KeywordFragment 多对多关系
     */
    public function keyWord()
    {
        return $this->belongsToMany('App\Models\Keywords','keyword_fragment','fragment_id','keyword_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 片段与分类
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToManyFragmentType()
    {
        return $this->belongsToMany('App\Models\FragmentType','fragmenttype_fragment','fragment_id','fragmentType_id');
    }

    /**
     * 片段与分镜的一对多
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyStoryboard()
    {
        return $this->hasMany('App\Models\Storyboard','fragment_id','id');
    }

    /**
     * 片段与字幕  一对多
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManySubtitle()
    {
        return $this->hasMany('App\Models\Subtitle','fragment_id','id');
    }

    /**
     * @param $query
     * @param $name
     * @return mixed
     * 多条件搜索-名称
     */
    public function scopeName($query,$name)
    {
        return $query->where('name','like',"%$name%");
    }

    /**
     * @param $query
     * @param $operator
     * @return mixed
     * 多条件搜索-操作员
     */
    public function scopeOperator($query,$operator)
    {
        if(!empty($operator))
        {
            return $query->where('operator_id','=',$operator);
        }else{
            return $query;
        }
    }

    /**
     * @param $query
     * @param $integral
     * @return mixed
     * 多条件搜索-下载费用
     */
    public function scopeIntegral($query,$integral)
    {
        if(!empty($integral)){
            return $query->where('integral','=',$integral);
        }else{
            return $query;
        }

    }

    /**
     * @param $query
     * @param $count
     * @return mixed
     * 多条件搜索-下载量
     */
    public function scopeCounta($query,$count)
    {
        return $query->where('count','>=',$count);
    }

    /**
     * @param $query
     * @param $time
     * @return mixed
     * 多条件搜索-时间
     */
    public function scopeTime($query,$time)
    {
        return $query->where('fragment.time_add','>=',$time);
    }

    /**
     * @param $query
     * @param $duration
     * @return mixed
     * 多条件搜索-时长
     */
    public function scopeDuration($query,$duration)
    {
        $duration = explode(':',$duration);
        $sumduration = $duration[0]*60 + $duration[1];
        return $query->where('duration','>=',$sumduration);
    }

}
