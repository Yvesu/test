<?php

namespace App\Api\Transformer;

use CloudStorage;

class ProfilesTrophyLogTransformer extends Transformer
{

    public function transform($data)
    {

        return [

            'user_id'            => $data -> belongsToUser -> id,

            'user_nickname'      => $data -> belongsToUser -> nickname,

            'trophy_name'        => $data -> belongsToTrophy -> name,

            'trophy_picture'     => CloudStorage::downloadUrl($data -> belongsToTrophy -> picture),

            'tweet_id'           => $data -> belongsToTweet -> id,

            'tweet_screen_shot'  => CloudStorage::downloadUrl($data -> belongsToTweet -> screen_shot),

            'created_at'         => $data -> time_add
        ];
    }
}