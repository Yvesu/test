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
        'test_result',
        'time_update',
        'vipfree',
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


    public function belongsToFolder()
    {
        return $this->belongsTo('App\Models\Make\MakeTemplateFolder','folder_id','id');
    }

    public function belongsToRecommender()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','dorecommend_id','id');
    }

    public function belongsToType()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','dotype_id','id');
    }

    public function belongsToShield()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','doshield_id','id');
    }

    public function belongsToChecker()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','checker_id','id');
    }

    public function belongsToHot()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','ishot_id','id');
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


    /**
     * @param $query
     * @param $name
     * @return mixed
     * 关键字搜索
     */
    public function scopeName($query,$name)
    {
        if($name == null){
            return $query;
        }else{
            return $query->where('name','like',"%$name%")->orWhere('intro','like',"%$name%")->orWhereHas('belongsToUser',function ($q) use($name){
                $q->where('nickname','like',"%$name%");
            });
        }
    }


    /**
     * @param $query
     * @param $type
     * @return mixed
     * 查询类别
     */
    public function scopeType($query,$type)
    {
        if($type==null){
            return $query;
        }else{
            return $query->whereHas('belongsToFolder',function ($q) use($type){
                $q->where('id','=',$type);
            });
        }
    }

    /**
     * @param $query
     * @param $operator
     * @param $status
     * @param $recommend
     * @param $ishot
     * @return mixed
     * 查询审核员
     */
    public function scopeOperator($query,$operator,$status,$recommend,$ishot)
    {
        if($operator==null){
            return $query;
        }else{
            if($recommend==1){
                return $query->whereHas('belongsToRecommender',function ($q) use($operator){
                    $q->where('id','=',$operator);
                });
            }

            if($status == 2){
                return $query->whereHas('belongsToShield',function ($q) use($operator){
                    $q->where('id','=',$operator);
                });
            }

            if($ishot==1){
                return $query->whereHas('belongsToHot',function ($q) use($operator){
                    $q->where('id','=',$operator);
                });
            }

            return $query->whereHas('belongsToChecker',function ($q) use($operator){
                $q->where('id','=',$operator);
            })->orWhereHas('belongsToHot',function ($q) use($operator){
                $q->where('id','=',$operator);
            })->orWhereHas('belongsToShield',function ($q) use($operator){
                $q->where('id','=',$operator);
            })->orWhereHas('belongsToRecommender',function ($q) use($operator){
                $q->where('id','=',$operator);
            });
        }
    }

    /**
     * @param $query
     * @param $time
     * @return mixed
     * 查询时间
     */
    public function scopeTime($query,$time)
    {
        return $query->where('time_add','>',$time);
    }

    /**
     * @param $query
     * @param $duration
     * @return mixed
     * 查询时长
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
        return $query->where('count','>',$count);
    }


}