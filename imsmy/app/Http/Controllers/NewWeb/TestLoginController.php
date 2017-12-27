<?php

namespace App\Http\Controllers\NewWeb;

use App\Models\Test\TestUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class TestLoginController extends Controller
{
    //
    /**
     * 登录
     */
//     public function login(Request $request)
//    {
//        try {
//            // 获取用户名及phone_id
//            $username = $request->get('name');
//            // 通过用户名获取用户信息，如果没有则返回错误信息
//            $auth = TestUser::where('name',$username)->first();
//            Redis::select(2);
//            $data = Redis::exists($auth->name);
//            // 获取验证所需要的密码及生成凭证token需要的数据
//            $credentials = [
//                'id' => $auth->id,                          // 用户id
//                'password' => $request->get('password')     // 用户输入的密码
//            ];
//            // 验证用户登录信息，如果验证成功则生成唯一凭证token，否则返回错误
//            if($data){
//                $old_token = Redis::get($auth->name);
//                $token = JWTAuth::refresh($old_token);
//                Redis::set($auth->name,$token);
//                $token = Redis::get($auth->name);
//
//            }else{
//                if (!$token = JWTAuth::attempt($credentials)) {
//                    return response()->json(['error' => 'invalid_credentials'], 401);
//                }
//                Redis::set($auth->name,$token);
//                $token = Redis::get($auth->name);
//            }
//
//            return response()->json(['token'=>$token],200);
//        } catch (ModelNotFoundException $e) {
//            return response()->json(['error' => 'invalid_credentials'], 401);
//        } catch (JWTException $e) {
//            // something went wrong whilst attempting to encode the token
//            return response()->json(['error' => 'could_not_create_token'], 500);
//        }
//    }


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
//        return true;
    }
}
