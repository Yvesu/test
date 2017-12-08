<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Middleware\BaseMiddleware;

class GetUser extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
//    public function handle($request, Closure $next)
//    {
//        if ($token = $this->auth->setRequest($request)->getToken()) {
//            try {
//                $user = $this->auth->authenticate($token);
//            } catch (TokenExpiredException $e) {
//                return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
//            } catch (JWTException $e) {
//                return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
//            }
//
//            if (! $user) {
//                return $this->respond('tymon.jwt.user_not_found', 'user_not_found', 404);
//            }
//
//            $this->events->fire('tymon.jwt.valid', $user);
//        }
//
//        return $next($request);
//    }

    // 测试使用，上面的为原代码
    public function handle($request, Closure $next)
    {
        if ($token = $this->auth->setRequest($request)->getToken()) {
            try {
                $user = $this->auth->authenticate($token);
            } catch (TokenExpiredException $e) {
                $user = [];
            } catch (JWTException $e) {
                return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
            }
            
            $this->events->fire('tymon.jwt.valid', $user);
        }

        return $next($request);
    }
}
