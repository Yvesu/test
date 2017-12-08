<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/16
 * Time: 17:34
 */

namespace app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDepartmentRequest;
use App\Http\Requests\DepartmentIDRequest;
use App\Models\Admin\Department;
use App\Models\Admin\Position;
use Illuminate\Http\Request;
use DB;
class DepartmentController extends Controller
{
    public function addPage()
    {
        return view('admin/management/family/addDept');
    }

    public function add(CreateDepartmentRequest $request)
    {
        $name = $request->input('name');
        $description = $request->input('description');

        $nameCount = Department::where('name',$name)->count();
        $descriptionCount = Department::where('description',$description)->count();
        if($nameCount){
            \Session::flash('deptName', '"'.$name.'"'.trans('common.has_been_existed'));
        }
        if($descriptionCount){
            \Session::flash('deptDescription', '"'.$description.'"'.trans('common.has_been_existed'));
        }
        if(\Session::has('deptName') || \Session::has('deptDescription')){
            return redirect('/admin/management/department')->withInput();
        }

        $department = new Department();
        $department->name = $name;
        $department->description = $description;
        $department->save();
        return redirect('/admin/management/family/setting?type=2');
    }

    public function review($id)
    {
        $department = Department::find($id);
        $department->active = 1;
        $department->save();
        return  redirect('/admin/management/family/setting?page=last');
    }

    public function delete($id)
    {
        Department::find($id)->delete();
        return  redirect('/admin/management/family/setting?type=2');
    }

    public function enable($id)
    {
        /*
         * 此处用了DB 并非 Eloquent ORM
         * DB带有'事务' ，异常回滚
         */
        DB::transaction(function()use($id){
            DB::table('position_b')
                        ->where('dept_id',$id)
                        ->update(['active' => 1]);

            DB::table('department_b')
                        ->where('id',$id)
                        ->update(['active' => 1]);
        });

        return redirect('/admin/management/family/setting?department='.$id);

    }

    public function disable($id)
    {
        /*
         * 此处用了DB 并非 Eloquent ORM
         * DB带有'事务' ，异常回滚
         */
        DB::transaction(function()use($id){
            DB::table('position_b')
                ->where('dept_id',$id)
                ->update(['active' => 0]);

            DB::table('department_b')
                ->where('id',$id)
                ->update(['active' => 0]);
        });

        return redirect('/admin/management/family/setting?type=0&department='.$id);
    }

    public function editPage($id)
    {
        $department = Department::find($id);
        return view('admin/management/family/editDept')
                ->with([
                    'department'    =>  $department
                ]);
    }

    public function editByID($id,Request $request)
    {
        $name = $request->input('name');
        $description = $request->input('description');

        $nameCount = Department::where('id','!=',$id)->where('name',$name)->count();
        $descriptionCount = Department::where('id','!=',$id)->where('description',$description)->count();
        if($nameCount){
            \Session::flash('deptName', '"'.$name.'"'.trans('common.has_been_existed'));
        }
        if($descriptionCount){
            \Session::flash('deptDescription', '"'.$description.'"'.trans('common.has_been_existed'));
        }
        if(\Session::has('deptName') || \Session::has('deptDescription')){
            return redirect('/admin/management/department/edit/'. $id)->withInput();
        }

        $department = Department::find($id);
        $department->name = $name;
        $department->description = $description;
        $department->save();
        return redirect('/admin/management/family/setting');
    }

    public function show($id)
    {
        $positions = Position::where('dept_id',$id)
                                ->where('active',1)
                                ->get();
        return response()->json($positions);
    }
}