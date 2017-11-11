<?php

namespace App\Api\Transformer;

use CloudStorage;
use Auth;
use App\Models\Subscription;

class NearbyUsersTransformer extends Transformer
{

    private $usersWithFansTransformer;

    public function __construct(
        UsersWithFansTransformer $usersWithFansTransformer
    )
    {
        $this->usersWithFansTransformer = $usersWithFansTransformer;

    }

    public function transform($user)
    {

        // 取5条动态
        $user -> hasManyTweet = $user -> hasManyTweet -> take(5);

        // 将动态遍历
        foreach($user -> hasManyTweet as $key => $tweet){

            // 如果为图片
            if($tweet->photo){

                // 解析成数组
                $photo = json_decode($tweet->photo,true);

                // 筛选图片的信息，取第一张
                $user -> hasManyTweet[$key] = ['tweet_id'=>$tweet->id,'picture'=>CloudStorage::downloadUrl($photo[0])];
            }else{

                // 视频封面
                $user -> hasManyTweet[$key] = ['tweet_id'=>$tweet->id,'picture'=>CloudStorage::downloadUrl($tweet->screen_shot)];
            }
        }

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
            'already_like' =>  $already_follow ?  ($already_fans ?  '2' : '1') : '0',
            'verify'       =>  $user->verify,
            'verify_info'  =>  $user->verify_info,
            'signature'    =>  $user->signature,
            'tweets'       =>  $user -> hasManyTweet,
            'user'         =>  $this -> usersWithFansTransformer -> transform($user),
        ];
    }
}