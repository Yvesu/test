<?php

namespace App\Api\Transformer;

use CloudStorage;

class LocationTweetsTransformer extends Transformer
{

    public function transform($tweets)
    {
        return [
            'id'            => $tweets->id,
            'screen_shot'   => CloudStorage::downloadUrl($tweets->screen_shot),
            'content'       => $tweets->hasOneContent->content
        ];
    }
}