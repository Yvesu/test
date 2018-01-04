<?php

namespace App\Http\Controllers\NewWeb;

use App\Models\Test\TestUser;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TestLoginController extends Controller
{
    //
    /**
     * 登录
     */
    public function login(Request $request)
    {
        try {
            $username = $request->get('name');
            $auth = TestUser::where('name',$username)->first();
            $credentials = [
                'id' => $auth->id,                          // 用户id
                'password' => $request->get('password')     // 用户输入的密码
            ];
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }

            $data = Redis::EXISTS($auth->name);
            Redis::Expire($auth->name,86400);
            if($data){
                $finallyData = Redis::get($auth->name);
                if($finallyData){
                    JWTAuth::invalidate($finallyData);
                }
            }
            Redis::set($auth->name,$token);
            $token = Redis::get($auth->name);
            return response()->json(['token'=>$token],200);

        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
    }
}
