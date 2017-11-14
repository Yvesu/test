<?php

namespace App\Http\Controllers\Admin\Menu;

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
 * 路由管理模块
 * Class RoleGroupController
 * @package App\Http\Controllers\Admin\Role
 */
class MenuController extends BaseSessionController
{
    protected $paginate = 20;

    /**
     * 路由列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('/admin/menu/index', ['datas' => RoleMenu::ofData()]);
    }

    /**
     *  路由添加
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add(Request $request)
    {
        return view('/admin/menu/add', ['datas' => RoleMenu::ofData()]);
    }

    /**
     * 路由添加动作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function insert(Request $request)
    {
        //名称,描述 不能为空
        $this->validate($request, [
            'name' => 'required',
            'intro' => 'required',
            'show_nav' => 'required',
            'route' => 'required',
        ],
            [
                'name.required' => '菜单名称不能为空',
                'intro.required' => '菜单描述不能为空',
                'show_nav.required' => '导航展示不能为空',
                'route.required' => '路由不能为空',
            ]
        );

        //如果不是顶级,则路由不能为空
        if( $request->input('pid')){
            $this->validate(
                $request,
                ['route' => 'required'],
                [ 'route.required' => '菜单路由不能为空']
            );
        }
        //实例功能类
        $service = new RoleMenuService();
        //添加
        $result = $service->add(
            $request->input('name'),
            $request->input('intro'),
            $request->input('route'),
            $request->input('pid'),
            $request->input('show_nav'),
            $request->input('class_icon','')

        );

        // 判断
        if (true === $result) return redirect('/admin/menu/index')->with('success','添加成功');

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
        $service = new RoleMenuService();
        if(!$id)
            return view('/admin/menu/delete',['rows' => $service->selectList()]);

        $result = $service->delete($id);

        if ($result) return redirect('/admin/menu/index')->with('success', '注销成功');

        return back()->with('error', '注销失败');
    }

    /**
     * 编辑
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        // 接收id 检测
        $id = intval($request->input('id', 0));
        if (!$id) return redirect('/admin/menu/index')->with('error', 'id不能为空');

        //记录是否存在
        $service = new RoleMenuService();
        if (!$one = $service->selectOneByWhere([['id', '=', $id]]))
            return redirect('/admin/menu/index')->with('error', '记录不存在');

        return view('/admin/menu/edit', [
            'one' => $one,
            'datas' => RoleMenu::ofData(),
            'statusDesc' => [0=>'不显示',1=>'显示'],
            'status' => [1=>'生效',0=>'失效'],
        ]);
    }

    /**
     * 修改信息
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        //名称,描述 不能为空
        $this->validate($request, [
            'id'=> 'required',
            'name' => 'required',
            'intro' => 'required',
            'status' => 'required',
            'show_nav' => 'required',
        ],
            [
                'name.required' => '菜单名称不能为空',
                'intro.required' => '菜单描述不能为空',
                'status.required' => '状态不能为空',
                'show_nav.required' => '导航栏展示不能为空',
            ]
        );

        //实例功能类
        $service = new RoleMenuService();
        //更新
        $result = $service->updateById(
            $request->input('id'),
            $request->input('name'),

            [
                'name'=>$request->input('name'),
                'intro'=>$request->input('intro'),
                'status'=>$request->input('status'),
                'show_nav'=>$request->input('show_nav'),
                'class_icon'=>$request->input('class_icon',''),
                'pid'=>$request->input('pid'),
                'route'=>$request->input('route'),
                'time_update'=>getTime()
            ]

        );

        // 判断
        if ($result) return redirect('/admin/menu/index')->with('success', '编辑成功');

        return back()->with('error', '编辑失败');
    }
}
