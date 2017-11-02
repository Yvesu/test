<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 音效文件用户中心
 * Class MakeAudioUser
 * @package App\Models
 */
class MakeAudioEffectUser extends Common
{
    protected  $table = 'make_audio_effect_user';

    protected $fillable = [
        'file_id',
        'user_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;


}