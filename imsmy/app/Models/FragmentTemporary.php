<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FragmentTemporary extends Model
{
    //
    protected $table = 'fragment_temporary';

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
        'integral',
        'cost',
        'count',
        'active',
        'time_add',
        'time_update',
        'vipfree',
        'recommend',
        'watch_count',
        'praise',
        'size',
        'test_results',
    ];

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
        return $this->belongsToMany('App\Models\Keywords','keyword_fragment','fragment_temporary_id','keyword_id');
    }

    /**
     * 与频道_片段中间表  FragmentTypeFragment
     */
    public function Channel()
    {
        return $this->belongsToMany('App\Models\FragmentType','fragmenttype_fragment','fragment_temporary_id','fragmentType_id');
    }

    public $timestamps = false;
}
