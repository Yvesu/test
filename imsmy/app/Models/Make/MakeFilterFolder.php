<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 滤镜文件夹名
 * Class MakeEffectsFolder
 * @package App\Models
 */
class MakeFilterFolder extends Common
{
    protected  $table = 'make_filter_folder';

    protected $fillable = [
        'name',
        'count',
        'sort',
        'active',
        'time_add',
        'time_update',
        'operator_id',
    ];

    public $timestamps = false;

    /**
     * 一对多关系 多个文件
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyFiles()
    {
        return $this -> hasMany('App\Models\Make\MakeFilterFile','folder_id','id');
    }

    public function scopeOfSearch($query,$search,$condition)
    {
        // 条件
        switch($condition){
            case 1:
                return $query -> where('id','like',(int)$search);
                break;
            case 2:
                return $query -> where('name','like','%'.$search.'%');
                break;
            default:
                return $query;
        }
    }

    public function belongsToManyFilter()
    {
        return $this->belongsToMany('App\Models\Make\MakeFilterFile','filter_folder','folder_id','filter_id');
    }

}