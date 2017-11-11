<?php

namespace App\Api\Transformer;

use CloudStorage;

class UsersTransformer extends Transformer
{
    public function transform($user)
    {
        return [
            'id'           =>  $user->id,
            'nickname'     =>  $user->nickname,
            'avatar'       =>  CloudStorage::downloadUrl($user->avatar),
            'cover'        =>  CloudStorage::downloadUrl($user->cover),
            'verify'       =>  $user->verify,
//            'hash_avatar'  =>  $user->hash_avatar,
            'signature'    =>  $user->signature,
            'verify_info'  =>  $user->verify_info,
        ];
    }

    public function fragtransform($user)
    {
        return [
            'nickname'     =>  $user['nickname'],
            'avatar'       =>  CloudStorage::downloadUrl($user['avatar']),
            'cover'        =>  CloudStorage::downloadUrl($user['cover']),
            'verify'       =>  $user['verify'],
//            'hash_avatar'  =>  $user->hash_avatar,
            'signature'    =>  $user['signature'],
            'verify_info'  =>  $user['verify_info'],
        ];
    }
}