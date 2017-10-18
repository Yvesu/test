<?php

namespace App\Models\Cloud;

use App\Models\Common;

/**
 * 用户云相册 使用空间统计表
 * Class CloudStorageFile
 * @package App\Models
 */
class CloudStorageSpace extends Common
{
    protected  $table = 'cloud_storage_space';

    protected $fillable = [
        'user_id',
        'total_space',
        'used_space',
        'free_space',
        'time_from',
        'time_end',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}