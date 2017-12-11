<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 效果文件详情表
 * Class MakeAudioFile
 * @package App\Models
 */
class MakeEffectsFile extends Common
{
    protected  $table = 'make_effects_file';

    protected $fillable = [
        'user_id',
        'name',
        'intro',
        'address',
        'high_address',
        'super_address',
        'preview_address',
        'cover',
        'folder_id',
        'duration',
        'size',
        'count',
        'attention',
        'integral',
        'sort',
        'recommend',
        'active',
        'time_add',
        'time_update',
        'test_result',
    ];

    public $timestamps = false;

    /**
     * 多对一关系,所属目录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function belongsToFolder()
    {
        return $this -> belongsTo('App\Models\Make\MakeEffectsFolder','folder_id','id');
    }

    /**
     * 上传者
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this -> belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyUserFile()
    {
        return $this -> hasMany('App\Models\Make\MakeEffectsUser','file_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 审核者
     */
    public function belongsToChecker()
    {
        return $this -> belongsTo('App\Models\User','checker_id','id');
    }

    public function belongsToRecommend()
    {
        return $this -> belongsTo('App\Models\User','dorecommend_id','id');
    }

    public function belongsToShield()
    {
        return $this -> belongsTo('App\Models\User','doshield_id','id');
    }

    /**
     * 下载记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyDownload()
    {
        return $this -> hasMany('App\Models\Make\MakeEffectsDownloadLog', 'file_id', 'id');
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

    /**
     * 类型
     * @param $query
     * @param $type
     * @param $folder_id
     * @return mixed
     */
    public function scopeOfType($query,$type,$folder_id=1)
    {
        switch($type){
            // 普通目录
            case 2:
                return $query -> where('folder_id',$folder_id) -> orderBy('count','DESC');
                break;
            // 最热
            case 3:
                return $query -> orderBy('count','DESC');
                break;
            // 最新
            case 4:
                return $query -> orderBy('id','DESC');
                break;
            // 推荐
            case 5:
                return $query -> where('recommend',1) -> orderBy('sort','DESC');
                break;
            // 搜索
            case 6:
                return $query;
                break;
            default:
                return $query;
        }
    }


    /**
     * @param $query
     * @param $type
     * @return mixed
     * 搜索类别
     */
    public function scopeType($query,$type)
    {
        if($type == null){
            return $query;
        }else{
            return $query->where('folder_id','=',$type);
        }
    }

    /**
     * @param $query
     * @param $name
     * @return mixed
     * 搜索关键字
     */
    public function scopeName($query,$name)
    {
        return $query->where('name','like',"%$name%")->orWhereHas('belongsToUser',function ($q) use($name){
            $q->orWhere('nickname','like',"%$name%")->orWhere('id','like',"%$name%");
        });
    }

    /**
     * @param $query
     * @param $integreal
     * @return mixed
     * 搜索下载费用
     */

    /**
     * @param $query
     * @param $time
     * @return mixed
     * 搜索发布时间
     */
    public function scopeTime($query,$time)
    {
        return $query->where('time_add','>=',$time);
    }

    /**
     * @param $query
     * @param $duration
     * @return mixed
     * 搜索时长
     */
    public function scopeDuration($query,$duration)
    {
        return $query->where('duration','>=',$duration);
    }

    /**
     * @param $query
     * @param $count
     * @return mixed
     * 搜索下载量
     */
    public function scopeCounta($query,$count)
    {
        return $query->where('count','>=',$count);
    }

}