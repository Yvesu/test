<?php

namespace App\Models\Cloud;

use App\Models\Common;

/**
 * 用户云相册 文件表
 * Class CloudStorageFile
 * @package App\Models
 */
class CloudStorageFile extends Common
{
    protected  $table = 'cloud_storage_file';

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'screenshot',
        'folder_id',
        'type',
        'format',
        'extension',
        'size',
        'active',
        'date',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    public function scopeOfFileStatus($query, $name)
    {
        if($name == '回收站'){

            return $query -> where('active',0);
        }else{

            return $query -> where('active',1);
        }

    }

    // 文件类型
    public function scopeOfFileType($query, $type)
    {
        switch($type){
            // 0视频，1图片，2其他
            case 0:
            case 1:
            case 2:
                return $query -> where('type',$type);
            break;
            // 全部
            case 3:
                return $query;
            break;
            // 共享
            case 4:
                return $query;
            break;

        }
    }

    // 图片或视频
    public function scopeOfPicture($query){
        return $query -> whereType(0) -> orWhere('type',1);
    }

}