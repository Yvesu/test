<?php

namespace App\Api\Transformer;

use CloudStorage;

class SearchTopicsTransformer extends Transformer
{

    public function transform($topic)
    {
        return [
            'id'            => $topic->id,
            'name'          => $topic->name,
            'comment'       => $topic->comment,
            'icon'          => CloudStorage::downloadUrl($topic->icon),
        ];
    }
}