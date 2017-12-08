<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 文件夹名
 * Class MakeAudioFolder
 * @package App\Models
 */
class MakeAudioFolder extends Common
{
    protected  $table = 'make_audio_folder';

    protected $fillable = [
        'name',
        'count',
        'sort',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 一对多关系 多个文件
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyFiles()
    {
        return $this -> hasMany('App\Models\Make\MakeAudioFile','folder_id','id');
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

}