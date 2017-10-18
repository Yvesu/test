<?php
namespace App\Models;

class CreationAds extends Common
{
    protected  $table = 'creation_ads';

    protected $fillable = [
        'active',
        'from_time',
        'end_time',
        'type_id',
        'type',
        'url',
        'image',
        'user_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 在有效期内
     * @param $query
     * @return mixed
     */
    public function scopeOfRecommend($query, $time)
    {
        return $query->where('active', 1)->where('from_time', '<=', $time)->where('end_time', '>', $time);
    }

}