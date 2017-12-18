<?php

namespace App\Http\Middleware;

use Closure;

class JwtAuthWebUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        config(['jwt.web.user' => '\App\Models\Test\TestUser']); //用于重定位model
        config(['auth.providers.users.model' => \App\Models\Test\TestUser::class]); //用于重定位model

        return $next($request);

    }
}
