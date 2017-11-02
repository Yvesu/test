<?php

namespace App\Api\Transformer;

use Auth;
use CloudStorage;
use App\Models\Subscription;

class UsersWithOauthTransformer extends Transformer
{
    public function transform($user)
    {

        // 判断用户是否为登录状态
        $user_from = Auth::guard('api')->user();

        $already_follow = '';

        if($user_from) {

            // 判断登录用户是否关注对方
            $already_follow = Subscription::ofAttention($user_from->id, $user->user_id)->first();

            // 判断对方是否为登录用户粉丝
            $already_fans = Subscription::ofAttention($user->user_id, $user_from->id)->first();
        }

        // 匹配微博好友
        return [
            'id'           =>  $user->user_id,
            'nickname'     =>  $user->hasOneUser->nickname,
            'avatar'       =>  CloudStorage::downloadUrl($user->hasOneUser->avatar),
            'already_like' =>  $already_follow ?  ($already_fans ?  '2' : '1') : '0',
            'verify'       =>  $user->hasOneUser->verify,
            'oauth_id'     =>  $user -> oauth_id,
            'signature'    =>  $user->hasOneUser->signature,
            'verify_info'  =>  $user->verify_info,
        ];
    }
}