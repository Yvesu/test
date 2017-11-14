<?php

namespace App\Api\Transformer;

use Auth;
use CloudStorage;
use App\Models\Subscription;

class SubTransformer extends Transformer
{
    public function transform($user)
    {

        // 获取登录用户信息
        $user_from = Auth::guard('api')->user();

        $already_follow = '';

        if($user_from) {

            // 判断登录用户是否关注对方
            $already_follow = Subscription::ofAttention($user_from->id, $user->id)->first();

            // 判断对方是否为登录用户粉丝
            $already_fans = Subscription::ofAttention($user->id, $user_from->id)->first();
        }

        // 频道热度排行榜 详情页 专用
        return [
            'id'           =>  $user->id,
            'nickname'     =>  $user->nickname,
            'signature'    =>  $user->signature,
            'avatar'       =>  CloudStorage::downloadUrl($user->avatar),
            'already_like' =>  $already_follow ?  ($already_fans ?  '2' : '1') : '0',
            'verify'       =>  $user->verify,
            'unread'       =>  $user->unread,

        ];
    }
}