<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Cookie;

class AdminCheckIp
{
    /**
     * Handle an incoming request.
     * 后台管理页面 认证
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 全局函数，检测IP
        check_ip();

        return $next($request);
    }
}
