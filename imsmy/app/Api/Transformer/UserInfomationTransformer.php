<?php

namespace App\Api\Transformer;

use App\Models\Subscription;
use CloudStorage;

class UserInfomationTransformer extends Transformer
{
    public function transform($user)
    {
        $login_user = \Auth::guard('api')->user();

        if ($login_user){
            $result_1 = Subscription::where('from',$login_user->id)->where('to',$user->id)->first();
            if ($result_1){
                $result_2 = Subscription::where('from',$user->id)->where('to',$login_user->id)->first();
                if ($result_2){
                    $attention = '2';
                }else{
                    $attention = '1';
                }
            }else{
                $attention = '0';
            }
        }else{
            $attention = '0';
        }


        return [
            'id'                    =>  $user -> id,
            'nickname'              =>  $user -> nickname,
            'avatar'                =>  CloudStorage::downloadUrl($user -> avatar),
            'title'                 =>  $user -> title,
            'signature'             =>  $user -> signature ? $user -> signature :'',
            'sex'                   =>  $user -> sex,
            'location'              =>  $user -> location,
            'follower_count'        =>  $user -> fans_count,                       // 粉丝
            'following_count'       =>  $user -> follow_count,                   // 关注
            'likes_count'           =>  $user -> like_count,                     // 点赞总数
            'work_count'            =>  $user -> work_count,                     // 作品总数
            'verify'                =>  $user -> verify,
            'verify_info'           =>  $user -> verify_info,
            'fans_count_add'        =>  $user -> new_fans_count,
            'level'                 =>  $user -> level,
            'honor'                 =>  $user -> honor,
            'cover'                 =>  CloudStorage::downloadUrl($user -> cover),
            'birthday'              =>  $user -> birthday,
            'attention'             => $attention,
        ];
    }
}