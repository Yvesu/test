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
        'bgm',
        'volume',
        'official',
        'address_country',
        'address_province',
        'address_city',
        'address_county',
        'address_street',
        'lat',
        'lng',
        'integral',
        'cost',
        'size',
        'count',
        'active',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;

    /**
     * 与分镜暂存表StoryboardTemporary 一对多关系
     *
     */
    public function hasManyStoryboardTemporary()
    {
        return $this->hasMany('App\Models\StoryboardTemporary','fragment_id','id');
    }

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
}
