<?php

namespace App\Api\Transformer;

use CloudStorage;

class RecommendActivityTransformer extends Transformer
{

    public function transform($view)
    {
        return [
            'id'            => $view->activity_id,
            'icon'         => CloudStorage::downloadUrl($view->image),
        ];
    }
}