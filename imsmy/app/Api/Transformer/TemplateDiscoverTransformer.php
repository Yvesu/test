<?php

namespace App\Api\Transformer;

use CloudStorage;

class TemplateDiscoverTransformer extends Transformer
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
            'user_id'       => $data->user_id,
            'style'         => 2,
            'name'          => $data->name,
            'intro'         => $data->intro,
            'location'      => $data->location ?? '',
            'count'         => $data->count,
            'cover'         => CloudStorage::downloadUrl($data->cover),
            'preview_address' => CloudStorage::downloadUrl($data->preview_address),
            'user'          => $this->usersSearchTransformer->transform($data->belongsToUser),
            'time_add'      => $data->time_add,
        ];
    }
}