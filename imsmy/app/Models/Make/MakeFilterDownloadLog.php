<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 滤镜文件用户下载记录表
 * Class MakeEffectsDownloadLog
 * @package App\Models
 */
class MakeFilterDownloadLog extends Common
{
    protected  $table = 'make_filter_download_log';

    protected $fillable = [
        'file_id',
        'user_id',
        'time_add'
    ];

    public $timestamps = false;

    /**
     * 多对一关系 文件详情
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function belongsToFile()
    {
        return $this -> belongsTo('App\Models\Make\MakeFilterFile','file_id','id');
    }

}