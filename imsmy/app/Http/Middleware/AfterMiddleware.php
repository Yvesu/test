<?php

namespace App\Http\Middleware;

use Closure;

class AfterMiddleware
{
    /**
     * Handle an incoming request.
     * log写入，调试时使用的，线上版本可以删除
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $str = str_random();
        \Log::debug("\n<========== " . $str . " ============>\n\n".
            "<==========  Request Start   ============>\n\n\n".
            $request . "\n".
            "<==========  Request End     ============>\n");

        $response = $next($request);

        \Log::debug("\n<========== " . $str . " ============>\n\n".
            "<==========   Response Start ============>\n\n\n".
            $response . "\n".
            "<==========   Response End   ============>\n");

        return $response;
    }
}
