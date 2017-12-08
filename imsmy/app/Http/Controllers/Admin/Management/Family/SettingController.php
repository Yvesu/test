<?php
/**
 * Created by PhpStorm.
 * User: Mabiao
 * Date: 2016/3/17
 * Time: 18:20
 */

namespace app\Http\Controllers\Admin\Management\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Admin\Department;
use Request;
class SettingController extends BaseSessionController
{
    public function show()
    {
        /*
         * 返回部门及用户信息
         */
        $type = (Request::has('type')) ? Request::input('type') : 1;
        $deptID = Request::input('department');

        if(2 == $type){
            $departments = Department::with('hasManyPosition.hasManyAdmin')
                ->where('active',$type)
                ->orWhereHas('hasManyPosition',function($q)use($type){
                    $q->where('active',$type);
                })->orderBy('created_at','desc')
                ->paginate(7);
        }else{
            $departments = Department::with('hasManyPosition.hasManyAdmin')
                ->where('active',$type)
                ->orWhereHas('hasManyPosition',function($q)use($type){
                    $q->where('active',$type);
                })->paginate(7);
            if('last' == Request::input('page')){
                $page = $departments->lastpage();
                Request::merge(array('page' => $page));
                $departments = Department::with('hasManyPosition.hasManyAdmin')
                    ->where('active',$type)
                    ->paginate(7);
            }
        }

        while($deptID && $departments->currentPage() < $departments->lastPage()){
            $count = $departments->where('id',(int)$deptID)->count();
            if(!$count){
                Request::merge(array('page' => $departments->currentPage()+1));
                $departments = Department::with('hasManyPosition.hasManyAdmin')
                    ->where('active',$type)
                    ->orWhereHas('hasManyPosition',function($q) use($type){
                        $q->where('active',$type);
                    })->paginate(7);
            }else{
                break;
            }
        }

        return view('admin/management/family/setting')
                ->with([
                    'departments'   => $departments,
                    'deptType'      => $type,
                    'deptID'        => $deptID,
                ]);
    }

}