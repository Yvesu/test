<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 音频文件用户下载记录表
 * Class MakeAudioDownloadLog
 * @package App\Models
 */
class MakeAudioDownloadLog extends Common
{
    protected  $table = 'make_audio_download_log';

    protected $fillable = [
        'file_id',
        'user_id',
        'time_add',
    ];

    public $timestamps = false;

    /**
     * 多对一关系 文件详情
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function belongsToFile()
    {
        return $this -> belongsTo('App\Models\Make\MakeAudioFile','file_id','id');
    }

}