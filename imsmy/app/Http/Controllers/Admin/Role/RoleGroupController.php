<?php

namespace App\Http\Controllers\Admin\Role;

use App\Models\RoleGroup;
use App\Models\RoleMenu;
use App\Models\Admin\Administrator;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Services\RoleGroupService;
use App\Services\RoleMenuService;
use App\Http\Controllers\Admin\BaseSessionController;
use Auth;
use DB;

/**
 * 权限管理模块
 * Class RoleGroupController
 * @package App\Http\Controllers\Admin\Role
 */
class RoleGroupController extends BaseSessionController
{
    protected $paginate = 20;

    /**
     * 权限组列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request){

        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search'));
        $num = (int)$request->input('num',$this->paginate);

        // 获取集合
        $datas = RoleGroup::orderBy('id','desc');

        // 是否为搜索
        if($search){

            // 条件
            switch($condition){
                // id
                case 1:
                    $datas = $datas->where('id','like','%'.$search.'%');
                    break;
                // 名称
                case 2:
                    $datas = $datas->where('name','like','%'.$search.'%');
                    break;
                // 描述
                case 3:
                    $datas = $datas->where('intro','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }
        }

        // 筛选集合
        $datas = $datas -> paginate($num);

        // 遍历集合，从local_user表中取数据
        $datas->each(function($data){

            // 获取提交人的名称
            $data -> user_name = Administrator::find($data->admin_id)->name;

        });

        // 搜索类型
        $cond = [1=>'ID',2=>'名称',3=>'描述'];

        // 登录用户
        $user = Auth::guard('web')->user();

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$num,
            'search'=>$search,
        ];

        // 返回视图
        return view('admin/role/index',[
            'user'=>$user,
            'datas'=>$datas,
            'request'=>$res,
            'condition'=>$cond
        ]);
    }
//    public function index(Request $request){
//
//        $service = new RoleGroupService();
//
//        $searchWhere = [];
//        if ($request->input('search_admin_id')) {
//            $searchWhere['_admin_id'] = ['email', 'like', '%' . $request->input('search_admin_id', '') . '%'];
//        }
//
//        $data = $service->getListPage([], ['id', 'desc'], $request->input('num', 10), $searchWhere);
//
//        // 设置返回数组
//        $res = [
//            'num' => $request->input('num', 10),
//            'search_admin_id' => $request->input('search_admin_id', ''),
//        ];
//
//        return view('/admin/role/index', [
//            'rows' => $data,
//            'request' => $res,
//            'statusDesc' => $service->getStatusDesc(),
//
//        ]);
//    }

    /**
     *  权限组添加
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add(Request $request)
    {
        // 加载视图
        return view('/admin/role/add', [
            'datas' => RoleMenu::ofData(),
        ]);
    }

    /**
     * 权限组添加动作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function insert(Request $request)
    {

        //名称,描述 不能为空
        $this->validate($request, [
            'name' => 'required',
            'intro' => 'required',
            'menu' => 'required',
        ],
            [
                'name.required' => '名称不能为空',
                'intro.required' => '描述不能为空',
                'menu.required' => '菜单不能为空',
            ]
        );

        //实例功能类
        $service = new RoleGroupService();

        //添加
        $result = $service->add(
            $request->input('name'),
            $request->input('intro'),
            $request->input('status',1),
            implode(',',$request->input('menu')),
            $this->_sessionAdmin->id
        );

        // 判断
        if (true === $result) return redirect('/admin/role/index')->with('success','添加成功');

        return back()->with('error','添加失败');

    }


    /**
     * 权组编辑
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        // 接收id 检测
        $id = intval($request->input('id'));
        if (!$id) return redirect('/admin/role/index')->with('error', 'id不能为空');

        //记录是否存在
        if (!$one = RoleGroup::find($id))
            return redirect('/admin/role/index')->with('error', '记录不存在');

        return view('/admin/role/edit', [
            'one' => $one,
            'menuIds' => $one->r_m_ids ? explode(',',$one->r_m_ids) : [],
            'datas' => RoleMenu::ofData(),
            'statusDesc' => [0=>'未生效',1=>'已生效']
        ]);
    }

    /**
     * 权组详情
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function details(Request $request)
    {
        // 接收id 检测
        $id = intval($request->input('id'));
        if (!$id) return redirect('/admin/role/index')->with('error', 'id不能为空');

        //记录是否存在
        if (!$one = RoleGroup::find($id))
            return redirect('/admin/role/index')->with('error', '记录不存在');

        return view('/admin/role/details', [
            'one' => $one,
            'menuIds' => $one->r_m_ids ? explode(',',$one->r_m_ids) : [],
            'datas' => RoleMenu::ofData(),
            'statusDesc' => [0=>'未生效',1=>'已生效']
        ]);
    }

    /**
     * 权限组添加动作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $service = new RoleMenuService();
        //名称,描述 不能为空
        $this->validate($request, [
            'id' => 'required',
            'name' => 'required',
            'intro' => 'required',
            'menu' => 'required',
        ],
            [
                'name.required' => '缺少ID',
                'intro.required' => '描述不能为空',
                'menu.required' => '菜单不能为空',
            ]
        );

        //实例功能类
        $service = new RoleGroupService();

        //添加
        $result = $service->update(
            $request->input('id'),
            $request->input('name'),
            $request->input('intro'),
            $request->input('status'),
            implode(',',$request->input('menu')),
            $this->_sessionAdmin->id
        );

        // 判断
        if (true === $result) return redirect('/admin/role/index')->with('success','更新成功');

        return back()->with('error',$result);

    }


    /**
     * 删除
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function delete(Request $request)
    {
        // 接收id
        $id = intval($request->input('id',''));
        $service = new RoleGroupService();
        if(!$id)
            return view('/admin/role/index');

        $result = $service->delete($id,$this->_sessionAdmin->id);

        if (true === $result) return redirect('/admin/role/index')->with('success', '注销成功');

        return back()->with('error', $result);
    }


}
