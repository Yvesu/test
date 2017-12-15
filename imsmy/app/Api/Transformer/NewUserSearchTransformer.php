<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/14 0014
 * Time: 下午 17:35
 */

namespace App\Api\Transformer;

use App\Facades\CloudStorage;
use App\Models\Subscription;
use Auth;

class NewUserSearchTransformer extends Transformer
{
    public  function transform($item)
    {
        //获取用户信息
        $user = Auth::guard('api')->user();

        if($user) {
            //判断关注状况
            $attention_1 = Subscription::where('from', $user->id)->where('to', $item['id'])->first(['id']);

            if ($attention_1) {

                $attention_2 = Subscription::where('to', $user->id)->where('from', $item['id'])->first(['id']);

                if ($attention_2) {
                    $attention = 2;         //互相关注
                } else {
                    $attention = 1;         //关注了对方
                }

            } else {
                $attention = 0;             //未关注
            }

            //如果用户登录的情况下
            return [
                'id' => $item['id'],
                'nickname' => $item['nickname'],
                'avatar' => CloudStorage::downloadUrl($item['avatar']),
                'verify' => $item['verify'],
                'verify_info' => $item['verify_info'],
                'signature' => $item['signature'] ?: '',
                'cover' => CloudStorage::downloadUrl($item['cover']),
                'attention' => $attention,
            ];

        }else{
            return [
                'id' => $item['id'],
                'nickname' => $item['nickname'],
                'avatar' => CloudStorage::downloadUrl($item['avatar']),
                'verify' => $item['verify'],
                'verify_info' => $item['verify_info'],
                'signature' => $item['signature'] ?: '',
                'cover' => CloudStorage::downloadUrl($item['cover']),
                'attention' => 0,
            ];

        }

    }
}