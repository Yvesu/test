<?php

namespace App\Http\Controllers\Admin\System;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Channel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Requests;
use DB;
class SortController extends BaseSessionController
{
    public function sort(Request $request)
    {
        $id = $request -> get('id');
        $type = $request -> get('type');

        // 判断type 0为升序，1为降序
        if($type == 0) {

            // 判断本频道是否已经是第一个
            if ($id === 1) return 0;

            // 开启事务
            DB::beginTransaction();

            // 获取本条信息的sort值
            $self = DB::table('channel')->where('id', $id)->first();

            $sort = $self->sort;    //6

            // 将数据库两组数据的sort交换值
            DB::table('channel')->where('sort', $sort)->update(['sort' => 0]);  // 6=>0

            // 修改前一条
            $result_prev = DB::table('channel')->where('sort', $sort - 1)->update(['sort' => $sort]);  // 5=>6

            // 修改本条
            $result_self = DB::table('channel')->where('sort', 0)->update(['sort' => $sort - 1]);  // 0=>5

            // 数据库写入成功
            if ($result_prev && $result_self) {

                DB::commit();
                return 1;
            }

            // 写入失败回滚
            DB::rollback();

            // 返回0
            return 0;
        }

        // 判断type 0为升序，1为降序
        if($type == 1) {

            // 查询表内最大sort值
            $max_sort = Channel::orderBy('sort','desc')->take(1)->pluck('sort')[0];

            // 判断本频道是否已经是最后一个
            if ($id == $max_sort) return 0;

            // 开启事务
            DB::beginTransaction();

            // 获取本条信息的sort值
            $self = DB::table('channel')->where('id', $id)->first();

            $sort = $self->sort;    //6

            // 将数据库两组数据的sort交换值
            DB::table('channel')->where('sort', $sort)->update(['sort' => 0]);  // 6=>0

            // 修改前一条
            $result_next = DB::table('channel')->where('sort', $sort + 1)->update(['sort' => $sort]);  // 7=>6

            // 修改本条
            $result_self = DB::table('channel')->where('sort', 0)->update(['sort' => $sort + 1]);  // 0=>7

            // 数据库写入成功
            if ($result_next && $result_self) {

                DB::commit();
                return 1;
            }

            // 写入失败回滚
            DB::rollback();

            // 返回0
            return 0;



//            // 查询上一条频道信息
//            $channel_prev = Channel::where('id',$id-1)->get();
//
////            dd($channel_prev->pluck('sort')[0]);
//
//            // 获取本条频道信息‘
//            $channel_self = Channel::where('id',$id)->first();
//
////            $channel_self->sort=20;
//
//
//            //获取上一条频道的排序编号
//            $sort_prev = $channel_prev->pluck('sort')[0];
//
//            $channel_self->update(['sort'=>$sort_prev]);
//
//            // 获取本条频道的排序编号
//            $sort_self = $channel_self->pluck('sort')[0];
//
//            list($channel_prev->sort,$channel_self->sort) = array($sort_self,$sort_prev);
//
//            // 判断是否操作成功，成功返回1



//            if($channel_prev->sort == $sort_self) return 1;
//
//            return 0;
        }

    }


}