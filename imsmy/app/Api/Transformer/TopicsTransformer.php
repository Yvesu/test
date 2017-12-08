<?php

namespace App\Api\Transformer;

use CloudStorage;

class TopicsTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($topic)
    {

        // 获取参加话题的前三个人的头像
        $avatars = [];

        if($topic -> hasManyTopicUser -> first()){

            foreach($topic -> hasManyTopicUser -> take(3) as $key => $value){

                $avatars[] = CloudStorage::downloadUrl($value -> belongsToUser -> avatar);
            }
        }

        return [
            'id'            => $topic->id,
//            'type'          => 'topic',
//            'type'          => 1,
            'style'         => 2,
            'name'          => $topic->name,
//            'color'         => $topic->color,
            'size'         => $topic->size,
            'icon'         => CloudStorage::downloadUrl($topic->icon),
            //TODO screen_shot
//            'screen_shot'   => CloudStorage::downloadUrl($topic->icon),
//            'comment'       => $topic->comment,
//            'created_at'    => strtotime($topic->created_at),
//            'user'          =>  $this->usersTransformer->transform($topic->belongsToUser),
            'avatars'       => $avatars,
            'users_count'   => $topic->users_count,     // 参加的总人数
        ];
    }
}