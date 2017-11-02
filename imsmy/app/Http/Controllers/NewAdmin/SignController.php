<?php

namespace App\Http\Controllers\NewAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;
use App\Http\Middleware\JwtAuthModel;

class SignController extends Controller
{
    /**
     * 管理员登录
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function sign(Request $request)
    {
        try {
            if(! $email = removeXSS($request->get('email')))
                return response()->json(['error' => 'bad_request'], 403);

            // 通过用户名获取用户信息
            $auth = Administrator::where('email',$email)->firstOrFail();

            // 获取验证所需要的密码及生成凭证token需要的数据
            $credentials = [
                'id' => $auth->id,                          // 用户id
                'password' => $request->get('password')     // 用户输入的密码
            ];

            // 验证用户登录信息，如果验证成功则生成唯一凭证token，否则返回错误
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }

            return response()->json(['token' => $token],'200');

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'invalid_credentials'],401);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
    }

    /**
     * 刷新token 方式1 对前端技术要求比较高
     * @param Request $request
     * @return array
     */
//    public function refresh(){
//
//        return ['status'=>'ok'];
//    }

    /**
     * 刷新token 方式2
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(){

        try {
            $old_token = JWTAuth::getToken();
            $token = JWTAuth::refresh($old_token);
//            JWTAuth::invalidate($old_token);
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json(compact('token'));
    }

}