<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 滤镜文件详情表
 * Class MakeAudioFile
 * @package App\Models
 */
class MakeFilterFile extends Common
{
    protected  $table = 'make_filter_file';

    protected $fillable = [
        'user_id',
        'name',
        'cover',
        'content',
        'folder_id',
        'count',
        'integral',
        'sort',
        'recommend',
        'active',
        'time_add',
        'time_update',
        'operator_id',
        'texture_id',
        'ishot',
    ];

    public $timestamps = false;

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
        return $this -> hasMany('App\Models\Make\MakeFilterUser','file_id','id');
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
            // 目录
            case 3:
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
            default:
                return $query;
        }
    }

    /**
     * 多条件搜索-名称
     */
    /**
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeName($query,$name)
    {
        return $query->where('name','like',"%$name%");
    }

    /**
     * @param $query
     * @param $folder_id
     * @return mixed
     * 多条件搜索-类别
     */
    public function scopeFolderId($query,$folder_id)
    {
        if(!empty($folder_id)){

            return $query->where('folder_id','=',$folder_id);

        }else{
            return$query;
        }
    }

    /**
     * @param $query
     * @param $operator_id
     * @return mixed
     * 多条件搜索-操作员
     */
    public function scopeOperatorId($query,$operator_id)
    {
        if(!empty($operator_id))
        {
            return $query->where('operator_id','=',$operator_id);
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
        return $query->where('time_add','>=',$time);
    }

    public function hasOneAdministrator()
    {
        return $this->hasOne('App\Models\Admin\Administrator','id','operator_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与类别的关系 多对多
     */
    public function belongsToManyFolder()
    {
        return $this->belongsToMany('App\Models\Make\MakeFilterFolder','filter_folder','filter_id','folder_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与滤镜混合纹理类型表关系    反向1对多
     */
    public function belongsToTextureMixType()
    {
        return $this->belongsTo('App\Models\Make\TextureMixType','texture_mix_type_id','id');
    }


    public function belongsToManyKeyword()
    {
        return $this->belongsToMany('App\Models\Keywords','filter_keyword','filter_id','keyword_id');
    }

}