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
            'avatar'       =>  $user->avatar==null? null : CloudStorage::publicImage($user->avatar),
            'cover'        =>  $user->cover,
            'verify'       =>  $user->verify,
//            'hash_avatar'  =>  $user->hash_avatar,
            'signature'    =>  $user->signature,
            'verify_info'  =>  $user->verify_info,
        ];
    }

    public function fragtransform($user)
    {
        return [
            'id'           =>  $user['id'],
            'nickname'     =>  $user['nickname'],
            'avatar'       =>  CloudStorage::publicImage($user['avatar']),
            'cover'        =>  $user['cover'],
            'verify'       =>  $user['verify'],
            'signature'    =>  $user['signature'],
            'verify_info'  =>  $user['verify_info'],
        ];
    }

    /**
     * 动态相关
     * @param $user
     * @return array
     */
    public function tweettransformer($user)
    {
        return [
            'id'           =>  $user['id'],
            'nickname'     =>  $user['nickname'],
            'avatar'       =>  CloudStorage::publicImage($user['avatar']),
            'cover'        =>  $user['cover'],
            'verify'       =>  $user['verify'],
            'signature'    =>  $user['signature'],
            'verify_info'  =>  $user['verify_info'],
        ];
    }

    /**
     * 滤镜
     * @param $user
     * @return array
     */
    public function filetransformer($user)
    {
        return [
            'id'           =>  $user['id'],
            'nickname'     =>  $user['nickname'],
        ];
    }

}