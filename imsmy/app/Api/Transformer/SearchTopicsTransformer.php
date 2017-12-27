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

    public function ptransform($topics)
    {
        $arr = [];
        foreach ($topics as $topic){
            $arr[] =  [
                'id'            => $topic->id,
                'name'          => $topic->name,
                'comment'       => $topic->comment,
//            'icon'          => CloudStorage::downloadUrl($topic->icon),
                'like_count'    =>  $topic->like_count,
            ];
        }

        return $arr;
    }
}