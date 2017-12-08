<?php

namespace App\Models;

class UploadFiles extends Common
{
    protected  $table = 'zx_upload_files';

    protected $fillable = [
        'name',
        'url',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 搜索
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfSearch($query, $name)
    {
        if($name){

            // 搜索
            return $query->where('name', 'like', '%'.$name.'%');

        }else{

            return $query;
        }
    }

}
