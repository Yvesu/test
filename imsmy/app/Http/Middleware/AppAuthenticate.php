<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
class AppAuthenticate
{
    /**
     * Handle an incoming request.
     * 部分URL通过ID进行使用，要保证用户权限对应到相应ID
     * 注释代码为实现单一登录，通过数据库记录最近一次登录时间，每次解析判断
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $id = Auth::guard('api')->user()->id;
        $user = User::find($id);
        $iat = JWTAuth::getPayload()['iat'];
        if($request->id != $id){
            return response()->json(['error' => 'unauthorized'],401);
        }

        /*if(strtotime($user->last_token) > $iat){
            return response()->json(['error' => 'token_expire'],401);
        }*/

        return $next($request);
    }
}
