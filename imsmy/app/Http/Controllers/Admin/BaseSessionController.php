<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use  App\Services\RoleMenuService;
use  App\Services\RoleGroupService;
use  App\Services\AdminService;
use  App\Services\UserService;
use  App\Services\InfoCateService;
use Illuminate\Support\Facades\Auth;

/**
 * 后台已登录用户 基类Controller
 * Class BaseSessionController
 * @package App\Http\Controllers\Admin
 */
class BaseSessionController extends Controller
{
    protected $_sessionAdmin;

    public function __construct()
    {

        //未登录直接跳转
        if (empty(Auth::guard('web')->user())) {
            header('location:/admin/login');
            exit;
        }

        //管理员的session
        $sessionAdmin = Auth::guard('web')->user();
        $adminService = new AdminService();

        // 通过id获取管理员信息
        $this->_sessionAdmin = $sessionAdmin = $admin = $adminService->selectOneById($sessionAdmin->id);

        //route获取 例 admin/content/video
        $route =  trim($_SERVER['REQUEST_URI'], '/');

        if(strpos($route , '?') !==false) $route = strstr($route,"?",true);

        // 如果有数字，只取数字前面的部分
        $route = preg_split('/\/\d/',$route)[0];

        //获取该路由的记录
        $roleMenuService = new RoleMenuService();
        $arr = $roleMenuService->selectOneByWhere([['route', $route]]);

        // 转为数组
        foreach($arr as $k=>$v){
            $menuNow[$k] = $v;
        }

        // 存入session
        session(['_menuNow' => $menuNow]);

        //获取管理员具有权限的menuIds role_menu 表
        $roleGroupService = new RoleGroupService();
        $gIds = explode(',',$admin->g_r_ids);
        $sessionAdmin->_menu_ids = $roleGroupService->getMenusByGroupIds($gIds);

        //管理员session重写
        session(['admin' => $sessionAdmin]);

        //检测路由是否有权限 没有则跳转
        $this->checkRouteRole($sessionAdmin->_menu_ids, $sessionAdmin,$route,$menuNow);

        //左侧导航栏数据
        $roleMenuService = new RoleMenuService();
        $menus  = $roleMenuService->getNav();
        session(['menu' => $menus]);
    }

    /**
     * 检测当前路由是否有权限
     * @param $menuIds
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function checkRouteRole($menuIds, $sessionAdmin,$route,$menuNow)
    {
        //判断是否是超级管理员
        if (1 == $sessionAdmin->id) return;

        // 不需要检测的路由数组
        $routeAdminNotCheck = ['admin/dashboard'];

        //判断是否是不需要检测的路由
        if (in_array($route, $routeAdminNotCheck)) return;

        //判断是有MenuId权限
        if (!$menuNow || !in_array($menuNow['id'], $menuIds)) {
            header('location:/admin/dashboard?roleName='.$menuNow['name'].'&roleRoute='.$menuNow['route']);
            exit;
        }

    }


}
