<?php

namespace App\Http\Transformer;

use CloudStorage;

class UsersTransformer extends Transformer
{
    public function transform($user)
    {
        return [
            'id'           =>  $user->id,
            'nickname'     =>  $user->nickname,
            'avatar'       =>  CloudStorage::downloadUrl($user->avatar),
            'hash_avatar'  =>  $user->hash_avatar,
            'verify'       =>  $user->verify,
            'signature'    =>  $user->signature,
        ];
    }
}