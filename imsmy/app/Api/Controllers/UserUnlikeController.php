<?php

namespace App\Api\Controllers;

use App\Models\UsersUnlike;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class UserUnlikeController extends Controller
{
    public function add($type,$id)      //不感兴趣的类型 $type 1 动态  2 模板   3 竞赛   4 广告
    {
        try{
            //获取用户信息
            $user = Auth::guard('api')->user();

            //用户的ID
            $user_id = $user->id;

            $arr = [
                'user_id'   =>  $user_id,
                'type'      =>  $type,
                'work_id'   =>  $id,
            ];

            UsersUnlike::create($arr);

        }catch (\Exception $e){
            return response()->json(['message'=>'bad_request'],500);
        }
    }
}
