<?php

namespace App\Api\Transformer;

use CloudStorage;
use Auth;
use App\Models\Subscription;

class UsersSearchTransformer extends Transformer
{
    public function transform($user)
    {
        // 判断用户是否为登录状态
        $user_from = Auth::guard('api')->user();

        $already_follow = '';

        if($user_from) {

            // 判断登录用户是否关注对方
            $already_follow = Subscription::ofAttention($user_from->id, $user->id)->first();

            // 判断对方是否为登录用户粉丝
            $already_fans = Subscription::ofAttention($user->id, $user_from->id)->first();
        }

        return [
            'id'           =>  $user->id,
            'nickname'     =>  $user->nickname,
            'avatar'       =>  CloudStorage::downloadUrl($user->avatar),
            'signature'    =>  $user->signature,
            'num_attention'=>  $user->num_attention,
            'already_like' =>  $already_follow ?  ($already_fans ?  '2' : '1') : '0',
            'verify'       =>  $user->verify,
            'verify_info'  =>  $user->verify_info,
        ];
    }
}