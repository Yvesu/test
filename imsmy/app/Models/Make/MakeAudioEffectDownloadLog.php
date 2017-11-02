<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 音效文件用户下载记录表
 * Class MakeAudioDownloadLog
 * @package App\Models
 */
class MakeAudioEffectDownloadLog extends Common
{
    protected  $table = 'make_audio_effect_download_log';

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
        return $this -> belongsTo('App\Models\Make\MakeAudioEffectFile','file_id','id');
    }

}