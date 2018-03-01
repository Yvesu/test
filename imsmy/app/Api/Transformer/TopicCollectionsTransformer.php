<?php

namespace App\Api\Transformer;

use CloudStorage;

class TopicCollectionsTransformer extends Transformer
{

    public function transform($topic)
    {

        return [
            'id'                => $topic->id,
            'name'              => $topic->name,
            'icon'              => CloudStorage::downloadUrl($topic->icon),
            'forwarding_times'  => $topic -> forwarding_times,
            'work_count'        => $topic -> work_count,
            'type'              => 1,
        ];
    }
}