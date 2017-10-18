<?php

namespace App\Api\Transformer;

use CloudStorage;

class ActivityTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($data)
    {

        // 获取参加赛事的前9个人的头像
        $avatars = [];

        if($data -> hasManyTweets -> first()){

            foreach($data -> hasManyTweets -> take(9) as $key => $value){

                $avatars[] = CloudStorage::downloadUrl($value -> belongsToUser -> avatar);
            }
        }

        return [
            'id'            => $data->id,
            'name'          => $data->name,
            'bonus'         => $data->bonus,
            'comment'       => $data->comment,
            'users_count'   => $data->users_count,
            'expires'       => $data->expires,
            'cover'         => CloudStorage::downloadUrl($data->icon),
            'user'          => $this->usersTransformer->transform($data->belongsToUser),
            'avatars'       => $avatars,
        ];
    }
}