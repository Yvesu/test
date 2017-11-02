<?php

namespace App\Models\Cloud;

use App\Models\Common;

/**
 * 用户云相册 分享表
 * Class CloudStorageShare
 * @package App\Models
 */
class CloudStorageShare extends Common
{
    protected  $table = 'cloud_storage_share';

    protected $fillable = [
        'user_id_from',
        'user_id_to',
        'dirname_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}