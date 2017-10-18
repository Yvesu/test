<?php

namespace App\Http\Controllers\Admin\APP\Make;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Make\{MakeFilterFolder};

class MakeFilterFolderController extends BaseSessionController
{
    /**
     * 添加效果目录
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        return view('admin/app/make/filter/folder/add');
    }

    /**
     * 效果目录的主页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try{
            // 搜索条件
            $condition = $request -> get('condition','');
            $search = $request -> get('search','');

            // 是否审批通过
            $active = (int)$request->get('active',1);

            // 获取集合
            $folder = MakeFilterFolder::where('active',$active)
                -> ofSearch($search,$condition)
                -> orderBy('sort')
                -> paginate((int)$request->input('num',20));

            // 搜索条件
            $condition = [1=>'ID',2=>'名称'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'       => $request->input('num',20),
                'search'    => $search,
                'active'    => $active,
            ];

            // 返回视图
            return view('admin/app/make/filter/folder/index',['datas'=>$folder,'request'=>$res,'condition' => $condition]);

        }catch(ModelNotFoundException $e){
            abort(404);
        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 修改 排序/删除/激活
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sort(Request $request)
    {
        try{

            // 获取集合
            $data = MakeFilterFolder::findOrFail((int)$request -> get('id'));

            // 获取操作类型
            if(!is_numeric($status = $request -> get('status')))
                return response()->json(0);

            switch($status){
                // 上移 升
                case 1:
                    $id = MakeFilterFolder::where('sort','<',$data -> sort)
                        -> orderBy('sort','DESC')
                        -> first();
                    break;
                // 下移 降
                case 2:
                    $id = MakeFilterFolder::where('sort','>',$data -> sort)
                        -> orderBy('sort')
                        -> first();
                    break;
                // 删除，暂时只是注销状态
                case 3:
                    return response()->json($data -> update(['active'=>2]));
                // 激活
                case 4:
                    return response()->json($data -> update(['active'=>1]));
                default:
                    return response()->json(0);
            }

            // 获取要更换顺序的集合
            $role_type = MakeFilterFolder::findOrFail($id->id);

            list($data -> sort,$role_type -> sort) = [$role_type -> sort,$data -> sort];

            // 保存
            $data -> save();
            $role_type -> save();

            return response()->json(1);

        }catch(\Exception $e){

            return response()->json(0);
        }
    }

    /**
     * 保存
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function insert(Request $request)
    {
        try{

            $name = post_check($request -> get('name'));

            // 查询是否已经存在
            if(MakeFilterFolder::where('name',$name)->first())
                return back()->with(['error'=>'已经存在']);

            // 获取最大sort
            $sort = MakeFilterFolder::orderBy('sort','DESC')->first();

            $time = getTime();

            // 保存
            MakeFilterFolder::create([
                'name'          => $name,
                'sort'          => $sort ? ++$sort -> sort : 1,
                'time_add'      => $time,
                'time_update'   => $time
            ]);

            return redirect('/admin/make/filter/folder/index');

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 编辑效果目录
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        return view('admin/app/make/filter/folder/edit',['data'=>MakeFilterFolder::findOrFail((int)$request->get('id'))]);
    }

    /**
     * 更新效果目录
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try{
            // 获取名称与id
            $name = post_check($request -> get('name'));
            $id = (int)$request -> get('id');

            // 获取数据
            $data = MakeFilterFolder::findOrFail($id);

            // 查询名称是否已经存在
            $check_name = MakeFilterFolder::where('name',$name)->first();
            if($check_name && $check_name -> name != $data -> name)
                return back()->with(['error'=>'已经存在']);

            // 修改
            $data -> name = $name;
            $data -> time_update = getTime();

            $data -> save();

            return redirect('/admin/make/filter/folder/index');

        }catch(\Exception $e){

            abort(404);
        }
    }

}