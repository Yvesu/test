<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 音频文件用户中心
 * Class MakeAudioUser
 * @package App\Models
 */
class MakeAudioUser extends Common
{
    protected  $table = 'make_chartlet_user';

    protected $fillable = [
        'file_id',
        'user_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 多对一关系 文件详情
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function belongsToFile()
    {
        return $this -> belongsTo('App\Models\Make\MakeChartletFile','file_id','id');
    }

}