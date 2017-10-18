<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/18
 * Time: 16:01
 */

namespace app\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePositionRequest;
use App\Http\Requests\PositionIDRequest;
use App\Models\Admin\Department;
use App\Models\Admin\Position;
use Illuminate\Http\Request;
use DB;
class PositionController extends Controller
{
    public function addPage()
    {
        $departments = Department::where('active',1)->get();
        return view('admin/management/family/addPosition')
                ->with([
                    'departments' => $departments
                ]);
    }

    public function add(CreatePositionRequest $request)
    {
        $name = $request->input('name');
        $description = $request->input('description');

        $department = Department::find($request->input('department'));

        $nameCount = Position::where('dept_id',$department->id)->where('name',$name)->count();
        $descriptionCount = Position::where('dept_id',$department->id)->where('description',$description)->count();
        if($nameCount){
            \Session::flash('postName', '"'.$name.'"'.trans('common.has_been_existed'));
        }
        if($descriptionCount){
            \Session::flash('postDescription', '"'.$description.'"'.trans('common.has_been_existed'));
        }
        if(\Session::has('postName') || \Session::has('postDescription')){
            return redirect('/admin/management/position')->withInput();
        }

        $position = new Position();
        $position->name = $name;
        $position->description = $description;
        $position->dept_id = $department->id;
        $position->save();
        return redirect('/admin/management/family/setting?department=' . (string)$department->id .'&type=2');
    }

    public function review($id)
    {
        $position = Position::with('belongsToDepartment')->find($id);
        $position->active = 1;
        $position->save();
        return  redirect('/admin/management/family/setting?department=' . $position->belongsToDepartment->id);
    }

    public function delete($id)
    {
        Position::find($id)->delete();
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
                ->where('id',$id)
                ->update(['active' => 1]);
        });
        $departmentID = Position::with('belongsToDepartment')->find($id)->id;

        return redirect('/admin/management/family/setting?department='.$departmentID);
    }

    public function disable($id)
    {
        /*
         * 此处用了DB 并非 Eloquent ORM
         * DB带有'事务' ，异常回滚
         */
        DB::transaction(function()use($id){
            DB::table('position_b')
                ->where('id',$id)
                ->update(['active' => 0]);
        });
        $department = Position::with('belongsToDepartment')->find($id);
        return redirect('/admin/management/family/setting?type=0&department='.$department->belongsToDepartment->id);
    }

    public function editPage($id)
    {
        $position = Position::with('belongsToDepartment')->find($id);
        return view('admin/management/family/editPosition')
            ->with([
                'position'      =>  $position
            ]);

    }

    public function editByID($id,Request $request)
    {
        $name = $request->input('name');
        $description = $request->input('description');

        $position = Position::find($id);
        $nameCount = Position::where('id','!=',$id)->where('dept_id',$position->dept_id)->where('name',$name)->count();
        $descriptionCount = Position::where('id','!=',$id)->where('dept_id',$position->dept_id)->where('description',$description)->count();
        if($nameCount){
            \Session::flash('postName', '"'.$name.'"'.trans('common.has_been_existed'));
        }
        if($descriptionCount){
            \Session::flash('postDescription', '"'.$description.'"'.trans('common.has_been_existed'));
        }
        if(\Session::has('postName') || \Session::has('postDescription')){
            return redirect('/admin/management/position/edit/'. $id)->withInput();
        }

        $position->name = $name;
        $position->description = $description;
        $position->save();
        return redirect('/admin/management/family/setting?department='.$position->dept_id);
    }
}