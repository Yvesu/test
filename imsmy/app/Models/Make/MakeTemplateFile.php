<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 模板
 * Class MakeTemplate
 * @package App\Models
 */
class MakeTemplateFile extends Common
{
    protected  $table = 'make_template_file';

    protected $fillable = [
        'user_id',
        'name',
        'intro',
        'address',
        'preview_address',
        'integral',
        'cover',
        'official',
        'recommend',
        'count',
        'active',
        'status',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 下载记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyDownload()
    {
        return $this -> hasMany('App\Models\Make\MakeTemplateDownloadLog', 'file_id', 'id');
    }

    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 搜索
     *
     * @param $query
     * @param $search
     * @param int $type
     * @return mixed
     */
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

    public function scopeOfFolder($query,$folder)
    {
        if(0 == $folder)
            return $query -> where('recommend', 1);

        return $query -> where('folder_id', $folder);
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
                return $query -> where('folder_id',$folder_id);
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

}