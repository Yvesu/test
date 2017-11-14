<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * 用户需求岗位的种类
 * Class UserDemandMenu
 * @package App\Models
 */
class UserDemandJob extends Common
{
    protected  $table = 'zx_user_demand_job';

    protected $fillable = [
        'name',
        'pid',
        'path',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 获取数据库基本信息
     * @param $query
     * @return mixed
     */
    public function scopeOfData($query){

        // 获取集合，以path拼接id排序
        $datas = $query->orderBy('path_pid','ASC')
            ->get(['*',DB::raw('CONCAT(path,id) as path_pid')]);

        // 如果数量不为空
        if($datas->count()){

            // 遍历集合
            foreach ($datas as $k => $v) {

                // 在子类根据层级拼接 '|--'
                $idName = str_repeat('|--', substr_count($v->path, ',')-1 ) . '';
                $v->name = $idName . $v->name;
            }
        }

        return $datas;
    }

}