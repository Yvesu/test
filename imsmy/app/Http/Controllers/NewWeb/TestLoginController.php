<?php

namespace App\Http\Controllers\NewWeb;

use App\Models\LocalAuth;
use App\Models\Test\TestUser;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Library\aliyun\SmsDemo;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            if($data){
                $finallyData = Redis::get($auth->name);
                if($finallyData){
                    JWTAuth::invalidate($finallyData);
                }
            }
            Redis::set($auth->name,$token);
            Redis::Expire($auth->name,86400);
            $token = Redis::get($auth->name);
            $ip = getIP();
            \DB::beginTransaction();
            $loginData = new User\UserLoginLog;
            $loginData -> ip = $ip;
            $loginData -> way = 'web';
            $loginData -> login_time = time();
            $loginData -> user_id = $auth->id;
            $loginData -> save();
            \DB::commit();
            return response()->json(['token'=>$token],200);

        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 验证码登录之生成验证码
     */
    public function sendCode(Request $request)
    {
        $username = $request ->get('username');
        if(strlen($username) == "11"){
            $rel = "/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/";
            if(preg_match($rel,$username)){
                //生成验证码
                $code = mt_rand(1000,9999);

                //将验证码放入缓存
                \Cache::put('SMS'.$request->get('username'),$code,'5');

                //将验证码发送给用户
                $response = SmsDemo::sendSms(
                    "嗨视频",
                    "SMS_110830042",
                    $username,
                    Array(
                        "code"=>$code,
                        "product"=>"dsd"
                    )
                );

                if($response->Message == 'OK'){
                    return response()->json(['message'=>'Send success'],200);
                }else{
                    return response()->json(['Send failure']);
                }
            }else{
                return response()->json(['message'=>'必须是手机号'],200);
            }
        }else{
            return response()->json(['message'=>'必须11位手机号'],200);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 验证验证码且登录
     */
    public function verifyCode(Request $request)
    {
        try{
            $username = $request->get('username');
            if(strlen($username) == "11"){
                $rel = "/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/";
                if(preg_match($rel,$username)){
                    $cache = Cache::get('SMS'.$username);
                    $newCache = $request->get('cache');
                    if($cache == $newCache){
                        $user = LocalAuth::where('username','=',$username)->first();
                        if($user){
                            $mainUser = User::find($user->user_id);
                            $finallyUser = TestUser::find($mainUser->id);
                            if($finallyUser){
                                $token = JWTAuth::fromUser($finallyUser);
                            }else{
                                $finallyUser = new TestUser;
                                $finallyUser -> id = $mainUser->id;
                                $finallyUser -> name = $username;
                                $finallyUser -> time_add = time();
                                $finallyUser -> time_update = time();
                                $finallyUser -> save();
                                $token = JWTAuth::fromUser($finallyUser);
                            }
                        }else{
                            DB::beginTransaction();
                            $user = new User;
                            $user -> nickname = 'Hi'.rand(0,999).$username[9].$username[10];
                            $user -> is_film_add = 1;
                            $user -> is_phonenumber = 1;
                            $user -> created_at = time();
                            $user -> updated_at = time();
                            $user -> avatar = 'img.cdn.hivideo.com/hivideo/web/headportraiticon_300*300_.png';
                            $user -> save();

                            $finallyUser = new TestUser;
                            $finallyUser -> id = $user->id;
                            $finallyUser -> name = $username;
                            $finallyUser -> time_add = time();
                            $finallyUser -> time_update = time();
                            $finallyUser -> save();

                            $localAuth = new LocalAuth;
                            $localAuth -> user_id = $user->id;
                            $localAuth -> username = $username;
                            $localAuth -> status = 0;
                            $localAuth -> created_at = time();
                            $localAuth -> updated_at = time();
                            $localAuth -> save();

                            DB::commit();
                            $token = JWTAuth::fromUser($finallyUser);
                        }
                        $data = Redis::EXISTS($username);
                        if($data){
                            $finallyData = Redis::get($username);
                            if($finallyData){
                                JWTAuth::invalidate($finallyData);
                            }
                        }
                        Redis::set($username,$token);
                        Redis::Expire($username,86400);
                        $token = Redis::get($username);
                        $ip = getIP();
                        \DB::beginTransaction();
                        $loginData = new User\UserLoginLog;
                        $loginData -> ip = $ip;
                        $loginData -> way = 'web';
                        $loginData -> login_time = time();
                        $loginData -> user_id = $finallyUser->id;
                        $loginData -> save();
                        \DB::commit();
                        return response()->json(['token'=>$token],200);
                    }else{
                        return response()->json(['message'=>'验证码错误'],200);
                    }
                }else{
                    return response()->json(['message'=>'必须是手机号'],200);
                }
            }else{
                return response()->json(['message'=>'必须11位手机号'],200);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
    }


    public function detectionToken(Request $request)
    {
        try{$token = JWTAuth::getToken();
            $a = JWTAuth::invalidate($token);
            if($a === true){
                return response()->json(['status'=>1]);
            }
        }catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

    }
}
