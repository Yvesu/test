<?php

namespace App\Api\Transformer;

use Auth;
use CloudStorage;

class UsersWithSubIndexTransformer extends Transformer
{
    public function transform($user)
    {

        // 频道热度排行榜 详情页 专用
        return [
            'id'           =>  $user->id,
            'avatar'       =>  CloudStorage::downloadUrl($user->avatar),
            'verify'       =>  $user->verify,
            'unread'       =>  $user->unread,
            'signature'    =>  $user->signature,
            'verify_info'  =>  $user->verify_info,
            'nickname'     =>  $user->nickname,
        ];
    }
}