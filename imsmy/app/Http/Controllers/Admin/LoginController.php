<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * 管理员登录界面
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login()
    {
        // 判断是否为登录状态
        if(Auth::guard('web')->user()) return redirect('admin/dashboard');

        return view('admin.login');
    }

    /**
     * 管理员登录认证
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function signIn(Request $request)
    {
        $remember = false;
        if($request->exists('remember')){
            $remember = true;
        }
        if(Auth::guard('web')->attempt(
            [
                'email'         =>  $request->get('email').'@goobird.com',
                'password'      =>  $request->get('password'),
                'deleted_at'    =>  null
            ],
            $remember
        )){
            return redirect('admin/dashboard');
        }

        \Session::flash('user_login_failed','用户名或密码不正确,或用户被停用');

        return redirect('admin/login')->withInput();
    }

    /**
     * 管理员登出
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout(Request $request)
    {
        // 删除session
        Auth::guard('web')->logout();
        $request->session()->forget('admin');
        return redirect('/admin/login');
    }


}