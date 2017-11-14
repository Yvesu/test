<?php


namespace App\Api\Controllers;

use App\Api\Transformer\UsersWithPhoneTransformer;
use App\Api\Transformer\UsersWithOauthTransformer;
use App\Models\LocalAuth;
use App\Models\OAuth;
use Illuminate\Http\Request;
use Auth;

class AttentionController extends BaseController
{

    protected $usersWithPhoneTransformer;
    protected $usersWithOauthTransformer;

    public function __construct(
        UsersWithPhoneTransformer $usersWithPhoneTransformer,
        UsersWithOauthTransformer $usersWithOauthTransformer
    )
    {
        $this->usersWithPhoneTransformer = $usersWithPhoneTransformer;
        $this->usersWithOauthTransformer = $usersWithOauthTransformer;
    }

    /**
     * 匹配手机通讯录好友信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function phone(Request $request)
    {
        try{

            // 接收所传的所有手机号
            $phone_number = $request -> get('phone');

            // 判断是否存在
            if(!$phone_number) throw new \Exception('bad_request',403);

            // 转码
            $phones = json_decode($phone_number);

            // 初始化
            $numbers = [];

            // 遍历检查是否都为数字 或者长度不对
            foreach($phones as $phone){

                $phone =trim((int)$phone);

                // 因为涉及国际手机号，长度目前设置为20位
                if($phone && strlen($phone) < 20) $numbers[] = $phone;
            }

            // 判断
            if(empty($numbers)) throw new \Exception('bad_request',403);

            // 去除重复值
            array_unique($numbers);

            // 判断用户是否为登录状态
            $user_self = Auth::guard('api')->user();

            // 如果有用户自己的id，去除
            if($user_self){

                // 获取用户的手机号
                $phone_self = LocalAuth::where('user_id',$user_self->id)->first();

                // 去除自己的手机号 方法1
                $numbers = array_diff($numbers,[$phone_self->username]);

                // 搜索是否存在自己的手机号 方法2
//                $phone_key = array_search($phone_self->username,$numbers);
//
//                // 去除自己的手机号
//                if($phone_key || $phone_key === 0) array_splice($numbers,$phone_key,1);
            }

            // 获取用户id
            $users = LocalAuth::with('hasOneUser') -> whereHas('hasOneUser',function($query){
                $query -> where('search_phone',1);
            })
                -> whereIn('username',$numbers)
                -> where('status',0)
                -> get();

            // 如果通讯录中没有注册用户，直接返回空数据
            if(!$users->count()) return response()->json([
                'data'  => [],
                'count' => 0
            ],201);

            return response()->json([
                'data'  => $this -> usersWithPhoneTransformer->transformCollection($users->all()),
                'count' => $users->count()
            ],201);

        }catch(\Exception $e){

            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }
    }

    /**
     * 匹配微博好友信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function weibo(Request $request)
    {
        try{

            // 接收所传的所有微博id
            $oauth_ids = $request -> get('oauth_id');

            // 判断是否存在
            if(!$oauth_ids) throw new \Exception('bad_request',403);

            // 转码
            $weibo_ids = json_decode($oauth_ids);

            // 初始化
            $numbers = [];

            // 遍历检查是否都为数字 或者长度不对
            foreach($weibo_ids as $weibo_id){

                if(is_numeric($weibo_id)) $numbers[] = $weibo_id;
            }

            // 获取用户信息
            $users = OAuth::with('hasOneUser')->whereIn('oauth_id',$numbers)->where('status',0)->get();

            // 如果有注册用户，获取信息
            if(!$users->count()) return response()->json([
                'data'  => [],
                'count' => 0
            ],201);

            return response()->json([
                'data'  => $this -> usersWithOauthTransformer->transformCollection($users->all()),
                'count' => $users->count()
            ]);
        }catch(\Exception $e){

            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }
    }



}