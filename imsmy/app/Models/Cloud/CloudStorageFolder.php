<?php

namespace App\Models\Cloud;

use App\Models\Common;

/**
 * 用户云相册 根目录下的文件夹名
 * Class CloudStorageFolder
 * @package App\Models
 */
class CloudStorageFolder extends Common
{
    protected  $table = 'cloud_storage_folder';

    protected $fillable = [
        'name',
        'user_id',
        'count',
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
        return $this -> hasMany('App\Models\Cloud\CloudStorageFile','folder_id','id');
    }

}