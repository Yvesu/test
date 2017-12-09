<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 音频文件详情表
 * Class MakeAudioFile
 * @package App\Models
 */
class MakeAudioFile extends Common
{
    protected  $table = 'make_audio_file';

    protected $fillable = [
        'user_id',
        'name',
        'intro',
        'address',
        'audition_address',
        'folder_id',
        'integral',
        'count',
        'duration',
        'active',
        'time_add',
        'time_update',
        'test_result',
        'vipfree',
    ];

    public $timestamps = false;

    /**
     * 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function belongsToFolder()
    {
        return $this -> belongsTo('App\Models\Make\MakeAudioFolder','folder_id','id');
    }

    /**
     * 下载记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyDownload()
    {
        return $this -> hasMany('App\Models\Make\MakeAudioDownloadLog', 'file_id', 'id');
    }

    public function scopeOfSearch($query,$search,$type=2)
    {
        if(!$search) return $query;

        switch($type){
            // id
            case 1:
                return $query -> where('id',(int)$search);
                break;
            // name
            case 2:
                return $query -> where('name','like',"%$search%");
                break;
            // intro
            case 3:
                return $query -> where('intro','like',"%$search%");
                break;
            // 目录
            case 4:
                return $query -> where('folder_id',$search);
                break;
            default:
                return $query;
        }
    }

}