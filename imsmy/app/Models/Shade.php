<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shade extends Model
{
    protected $table = 'shade';

    protected $fillable = [
        'name',
        'user_id',
        'video',
        'image',
        'folder_id',
        'integral',
        'official',
        'down_count',
        'watch_count',
        'recommend',
        'sort',
        'active',
        'test_result',
        'size',
        'duration',
        'vipfree',
        'create_time',
        'update_time',
    ];

    public $timestamps = false;

    /**
     * 文件夹
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongToFolder()
    {
        return $this->belongsTo('App\Models\ShadeFolder','folder_id','id');
    }

    /**
     * 用户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }
}
