<?php

namespace App\Api\Transformer;

use CloudStorage;

class TweetsTrophyLogTransformer extends Transformer
{
    protected $usersTransformer;
    protected $tweetsTrophyConfigTransformer;

    public function __construct(

        UsersTransformer $usersTransformer,
        TweetsTrophyConfigTransformer $tweetsTrophyConfigTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->tweetsTrophyConfigTransformer = $tweetsTrophyConfigTransformer;
    }

    public function transform($data)
    {

        return [
            // 判断颁奖嘉宾是否为匿名
            'user'   => 0 === $data -> anonymity ? $this->usersTransformer->transform($data->belongsToUser) : (object)NULL,

            'trophy' => $this -> tweetsTrophyConfigTransformer->transform($data -> belongsToTrophy),

            'created_at'    => $data -> time_add
        ];
    }
}