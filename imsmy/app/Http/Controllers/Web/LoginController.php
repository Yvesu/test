<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function guard()
    {
        return Auth::guard('local');
    }

    // 用户登录
    public function postLogin()
    {
        // TODO 验证登录信息


        $user_id = 1000240;

        // 登录成功，将用户部分信息存入session中
        $user = User::findOrFail($user_id,['id','fans_count', 'new_fans_count', 'follow_count', 'work_count', 'nickname','avatar','sex','verify','signature','num_attention','created_at']);

        session(['user' => $user]);
    }

    public function username()
    {
        return 'username';
    }
}
