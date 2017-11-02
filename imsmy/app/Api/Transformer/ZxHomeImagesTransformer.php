<?php

namespace App\Api\Transformer;

use CloudStorage;

class ZxHomeImagesTransformer extends Transformer
{

    public function transform($data)
    {
        return [
            'type'       => $data->type,
            'type_id'    => $data->type_id,
            'image'      => CloudStorage::downloadUrl($data->image),
            'url'        => $data->url,
        ];
    }
}