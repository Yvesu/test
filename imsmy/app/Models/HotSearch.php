<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class HotSearch extends Model
{
    protected  $table = 'hot_search';

    protected $fillable = [
        'hot_word',
        'active',
        'sort',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;

    /**
     * 查询推送的热词
     * @param $query
     * @return mixed
     */
    public function scopeRecommend($query)
    {
        return $query->where('active',1)->orderBy('sort','desc');
    }

    /**
     * 查询可用的热词
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active',1);
    }

}