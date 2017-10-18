<?php

namespace App\Api\Transformer;

use CloudStorage;

class TopicSimplyTransformer extends Transformer
{

    public function transform($topic)
    {

        return [
            'id'                => $topic->hasOneTopic->id,
            'name'              => $topic->hasOneTopic->name,
            'icon'              => CloudStorage::downloadUrl($topic->hasOneTopic->icon),
            'users_count'       => $topic->users_count,
            'like_count'        => $topic->like_count,
            'forwarding_times'  => $topic->forwarding_times,
        ];
    }
}