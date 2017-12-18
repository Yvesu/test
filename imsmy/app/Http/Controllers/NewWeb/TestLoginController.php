<?php

namespace App\Http\Controllers\NewWeb;

use App\Models\Test\TestUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class TestLoginController extends Controller
{
    //
    /**
     * 登录
     */
    public function login(Request $request)
    {
        try {
            // 获取用户名及phone_id
            $username = $request->get('name');
            // 通过用户名获取用户信息，如果没有则返回错误信息
            $auth = TestUser::where('name',$username)->first();
            // 获取验证所需要的密码及生成凭证token需要的数据
            $credentials = [
                'id' => $auth->id,                          // 用户id
                'password' => $request->get('password')     // 用户输入的密码
            ];
            // 验证用户登录信息，如果验证成功则生成唯一凭证token，否则返回错误
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
            return response()->json(['token'=>$token],200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
    }
}
