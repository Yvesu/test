<?php
/**
 * Created by PhpStorm.
 * User: Mabiao
 * Date: 2016/3/15
 * Time: 18:35
 */

namespace App\Http\Controllers\Admin\Management\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Admin\Administrator;
use App\Models\Admin\Department;
use Illuminate\Http\Request;
class MemberController extends  BaseSessionController
{
    public function show(Request $request)
    {
        $departmentID = $request->input('departmentID');
        $dept_tab_title = null;
        /*
         * 返回部门及用户信息
         */
        $departments = Department::where('active',1)->orWhere('active',0)->get();
        $countArray = [];
        /*
         * 统计信息
         */
        $countArray = array_add($countArray,'all',Administrator::all()->count());
        $countArray = array_add($countArray,'disabled',Administrator::onlyTrashed()->count());
        foreach($departments as $department){
            $id = $department->id;
            $count = Administrator::whereHas('belongsToPosition.belongsToDepartment',function($q)use($id){
                $q->where('id',$id);
            })->count();
            $countArray = array_add($countArray,$id,$count);
        }
        /*
         * 统计end
         */
        if(is_null($departmentID)){
            $administrators = Administrator::with('belongsToPosition.belongsToDepartment')->paginate(7);
            $count = Administrator::all()->count();
            $dept_tab_title = trans('management.all_members') . '(' . $count . ')';
        }elseif(0 ==  $departmentID){
            $administrators = Administrator::onlyTrashed()->with('belongsToPosition.belongsToDepartment')->orderBy('deleted_at','desc')->paginate(7);
            $count = Administrator::onlyTrashed()->count();
            $dept_tab_title = trans('management.disabled_members') . '(' . $count . ')';
        }else{
            $administrators = Administrator::whereHas('belongsToPosition.belongsToDepartment',function($q)use($departmentID){
                                    $q->where('id',$departmentID);
                                })
                                ->with('belongsToPosition.belongsToDepartment')->paginate(7);
            $count = Administrator::whereHas('belongsToPosition.belongsToDepartment',function($q)use($departmentID){
                                    $q->where('id',$departmentID);
                                })->count();
            foreach($departments as $department){
                if($department->id == $departmentID){
                    $dept_tab_title = $department->description;
                    if(0 == $department->active){
                        $dept_tab_title .= ' ('.trans('management.deactivated').')';
                    }
                    $dept_tab_title .= ' (' . $count . ')';
                    break;
                }
            }
        }



        return view('admin/management/family/member')
                ->with([
                    'departments'       => $departments,
                    'administrators'    => $administrators,
                    'dept_tab_title'    => $dept_tab_title,
                    'departmentID'      => $departmentID,
                    'count_array'       => $countArray,
                ]);
    }
}