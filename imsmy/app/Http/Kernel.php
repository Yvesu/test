<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \App\Http\Middleware\AccessControlAllowOrigin::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\AdminCheckIp::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            'cors'
        ],

        'api' => [
            'throttle:1000,1',
//            \App\Http\Middleware\AfterMiddleware::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        /*
         * for goobird
         */
        'jwt.auth'    => \Tymon\JWTAuth\Middleware\GetUserFromToken::class,
        'jwt.refresh' => \Tymon\JWTAuth\Middleware\RefreshToken::class,
        'auth.admin'  => \App\Http\Middleware\AdminAuth::class,
        'app.auth'    => \App\Http\Middleware\AppAuthenticate::class,
        'app.user'    => \App\Http\Middleware\GetUser::class,
        'app.cloud'   => \App\Http\Middleware\CloudVerify::class,

        'jwt.api.auth' => \App\Http\Middleware\JwtAuthModel::class, //新增注册的中间件

        'filmfestUser' => \App\Http\Middleware\Filmfest::class, //  竞赛管理中简件
        'filmfestUsserIssue' => \App\Http\Middleware\FilmfestUserIssue::class, //  竞赛管理中间件发起者
        'filmfestUserRole'=>\App\Http\Middleware\FilmfestUserRole::class,      //   竞赛管理中间件角色

        /*
         * personal
         */
        'web.auth'  => \App\Http\Middleware\WebAuth::class,

        // 跨域
        'cors' => \App\Http\Middleware\AccessControlAllowOrigin::class,

        //  测试用户
        'test.user.auth' => \App\Http\Middleware\JwtAuthWebUser::class,

    ];
}
