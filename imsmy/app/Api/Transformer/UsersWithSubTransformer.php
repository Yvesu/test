<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/14
 * Time: 17:00
 */

namespace App\Api\Transformer;

use Auth;
use CloudStorage;
use App\Models\Subscription;

class UsersWithSubTransformer extends Transformer
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

        // 频道热度排行榜（详情页）、发现页面（大家都在看、附近动态）
        return [
            'id'           =>  $user->id,
            'nickname'     =>  $user->nickname,
            'avatar'       =>  CloudStorage::downloadUrl($user->avatar),
            'already_like' =>  $already_follow ?  ($already_fans ?  '2' : '1') : '0',
            'verify'       =>  $user->verify,

//            'follower'     =>  $user->hasManySubscriptions && ! $user->hasManySubscriptions->isEmpty() ? true : false,
//            'following'    =>  $user->hasManySubscriptionsFrom && ! $user->hasManySubscriptionsFrom->isEmpty() ? true : false
            'signature'    =>  $user->signature,
            'verify_info'  =>  $user->verify_info,
//            'hash_avatar'  =>  $user->hash_avatar,
        ];
    }
}