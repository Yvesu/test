<?php

namespace App\Api\Transformer;

use CloudStorage;

class ActivityDiscoverTransformer extends Transformer
{
    private $usersSearchTransformer;

    public function __construct(
        UsersSearchTransformer $usersSearchTransformer
    )
    {
        $this->usersSearchTransformer = $usersSearchTransformer;
    }

    public function transform($data)
    {

        // 获取参加赛事的前3张封面
        $covers = [];

        if($count = $data -> hasManyTweets -> count()){

            foreach($data -> hasManyTweets -> take(3) as $key => $value){

                $covers[] = CloudStorage::downloadUrl($value -> screen_shot);
            }
        }

        return [
            'id'            => $data->id,
            'comment'       => $data->comment,
            'location'      => $data->location,
            'style'         => 3,
            'icon'          => CloudStorage::downloadUrl($data->icon),
            'user'          => $this->usersSearchTransformer->transform($data->belongsToUser),
            'covers'        => $covers,
            'count'         => $count,
            'recommend_expires'      => $data->recommend_expires,
            'time_add'      => $data->time_add,
        ];
    }
}