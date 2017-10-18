<?php

namespace App\Http\Middleware;

use App\Models\Cloud\CloudStorageSpace;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Middleware\BaseMiddleware;
use App\Models\User;
use Closure;

class CloudVerify extends BaseMiddleware
{
    /**
     * 验证是否开通了云空间
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    // 测试使用，上面的为原代码
    public function handle($request, Closure $next)
    {
        try {
            $id = Auth::guard('api')->user()->id;

            // 检查用户是否开通云相册功能
            $cloud = CloudStorageSpace::where('user_id',$id) -> where('time_end','>=',getTime()) -> first();

            if(!$cloud) return response()->json(['error' => 'not_opened',432]);

            // 匹配用户是否为认证用户
            if(!User::findOrFail($id)->verify)
                return response()->json(['error' => 'not_verify'], 402);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found',404]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found',404]);
        }

        return $next($request);
    }
}
