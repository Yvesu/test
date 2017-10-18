<?php

namespace App\Models;

/**
 * 精选媒体
 */
class FeaturedMedia extends Common
{
    protected  $table = 'featured_media';

    protected $fillable = [
        'user_id',
        'top',
        'sort',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 与用户关系 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

}