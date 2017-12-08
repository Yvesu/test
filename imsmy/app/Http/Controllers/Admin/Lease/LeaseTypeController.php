<?php

namespace App\Http\Controllers\Admin\Lease;

use App\Models\Lease\UserLeaseType;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Admin\BaseSessionController;

/**
 * 商品类型种类管理模块
 * Class DemandJobController
 * @package App\Http\Controllers\Admin\Demand
 */
class LeaseTypeController extends BaseSessionController
{
    /**
     * 需求种类列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('/admin/lease_config/index', ['datas' => UserLeaseType::ofData()]);
    }

    /**
     *  需求种类添加
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add(Request $request)
    {
        return view('/admin/lease_config/add', ['datas' => UserLeaseType::ofData()]);
    }

    /**
     * 需求种类添加动作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function insert(Request $request)
    {
        try{

            //名称,描述 不能为空
            $this->validate($request, [
                'name' => 'required',
            ],
                [
                    'name.required' => '菜单名称不能为空',
                ]
            );

            $pid = $request->input('pid');
            $name = $request->input('name');

            // 判断名称是否已经存在
            if(UserLeaseType::where('name',$name)->first())
                return back()->with('error','名称已经存在');

            //设置路径
            $path = '0,';
            if($pid){
                if($one = UserLeaseType::find($pid))
                    $path = $one->path.$pid.',';
            }

            //添加
            UserLeaseType::create([
                'name' => $name,
                'pid'  => $pid,
                'path'=>$path,
                'time_add'=>getTime(),
                'time_update'=>getTime(),
            ]);

            // 返回
            return redirect('/admin/config/lease/index')->with('success','添加成功');
        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 删除
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function delete(Request $request)
    {
        try{

            // 要删除的id
            if(!$id = (int)$request -> get('id')) return back('error','id不能为空');

            // 删除
            UserLeaseType::where('id', $id)->orWhere('pid', $id)->delete();

            // 返回
            return redirect('/admin/config/lease/index');

        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 编辑
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        try{

            // 要编辑的id
            if(!$id = (int)$request -> get('id')) return back('error','id不能为空');

            // 返回视图
            return view('/admin/lease_config/edit', [
                'one' => UserLeaseType::findOrFail($id),
                'datas' => UserLeaseType::ofData(),
                'status' => [1=>'生效',0=>'失效'],
            ]);

        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 修改信息
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try{
            //名称,描述 不能为空
            $this->validate($request, [
                'id'=> 'required',
                'name' => 'required',
                'active' => 'required',
            ],
                [
                    'id.required' => 'id不能为空',
                    'name.required' => '名称不能为空',
                    'active.required' => '状态不能为空',
                ]
            );

            // 获取集合
            $data = UserLeaseType::findOrFail((int)$request -> get('id'));

            // 修改状态
            $active = (int)$request->input('active');

            // 如果状态做了修改
            if($data -> active != $active){

                // 修改状态
                $data -> active = $active === 1 ? 1 : 0;

                // 获取子集
                $children = UserLeaseType::where('pid',$data->id)->get();

                // 如果有子集
                if($children->count()){

                    $children -> each(function($child)use($active){

                        // 修改状态
                        $child -> active = $active;

                        // 保存
                        $child -> save();
                    });
                }
            }

            // 修改名称
            $name = post_check($request->input('name'));

            // 判断是否编辑
            if($data -> name != $name){

                // 判断名称是否已经存在
                if(UserLeaseType::where('name',$name)->first())
                    return back()->with('error','名称已经存在');

                $data -> name = $name;
            }

            // 修改pid
            $pid = (int)$request->input('pid');

            // 判断pid是否已做编辑
            if($data -> pid != $pid){

                //设置默认路径
                $path = '0,';

                // 判断父级是否为顶级
                if($pid !== 0){
                    // 拼接 path
                    $path = UserLeaseType::findOrFail($pid)->path.$pid.',';
                }

                // 修改集合
                $data -> pid = $pid;
                $data -> path = $path;

                // 获取子集
                $children = UserLeaseType::where('pid',$data->id)->get();

                // 如果有子集
                if($children->count()){

                    $children -> each(function($child)use($path,$data){

                        // 修改自己的 path
                        $child -> path = $path.$data->id.',';

                        // 保存
                        $child -> save();
                    });
                }
            }
            // 保存
            $data -> save();

            // 判断
            return redirect('/admin/config/lease/index')->with('success', '编辑成功');

        }catch(\Exception $e){
            abort(404);
        }
    }
}
