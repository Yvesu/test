<?php
namespace App\Models;

class CreationCover extends Common
{
    protected  $table = 'creation_cover';

    protected $fillable = [
        'name',
        'cover',
        'integral',
        'recommend',
        'active',
        'from_time',
        'end_time',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 搜索
     * @param $query
     * @param $search
     * @param int $type
     * @return mixed
     */
    public function scopeOfSearch($query,$search,$type=2)
    {
        if(!$search) return $query;

        switch($type){
            // id
            case 1:
                return $query -> where('id',(int)$search);
                break;
            // name
            case 2:
                return $query -> where('name','like',"%$search%");
                break;
            default:
                return $query;
        }
    }

}