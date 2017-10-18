<?php

namespace App\Api\Transformer;

use CloudStorage;

class TweetsTrophyConfigTransformer extends Transformer
{
    public function transform($trophy)
    {
        return [
            'id'           =>  $trophy->id,
            'name'         =>  $trophy->name,
            'picture'      =>  CloudStorage::downloadUrl($trophy->picture),
        ];
    }
}