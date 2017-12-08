<?php

namespace App\Api\Transformer;

use CloudStorage;

class AdsDiscoverTransformer extends Transformer
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

        return [
            'id'            => $data->id,
            'name'          => $data->name,
            'type_id'       => $data->type_id,
            'type'          => $data->type,
            'style'         => 1,
            'url'           => $data->url,
            'count'         => $data->count,
            'image'         => CloudStorage::downloadUrl($data->image),
            'user'          => $this->usersSearchTransformer->transform($data->belongsToUser),
            'time_add'      => $data->time_add,
        ];
    }
}