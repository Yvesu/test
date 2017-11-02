<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserCollections extends Model
{
    protected $table = 'user_collections';

    protected $fillable = ['user_id','type_id','type','status','time_add','time_update'];

    public $timestamps = false;

    public function scopeStatus($query)
    {
        return $query -> where('status',1);
    }

    /**
     * @param $query
     * @param $last 最后一个数据的id
     * @return mixed
     */
    public function scopeOfLast($query,$last)
    {
        // 判断last
        if($last) return $query -> where('id','<',$last);
        return $query;
    }

    /**
     * 查询是否为收藏关系
     * @param $query
     * @param $from
     * @param $to
     * @return mixed
     */
    public function scopeOfCollections($query, $from, $to, $type)
    {
        return $query->where('user_id', $from)->where('type_id', $to)->where('type',$type);
    }
}