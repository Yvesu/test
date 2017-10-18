<?php
namespace App\Api\Transformer;

use CloudStorage;

class MakeTemplateFileDetailsTransformer extends Transformer
{
    public function transform($data)
    {
        return [
            'id'            => $data -> id,
            'name'          => $data -> name,
            'introduction'  => $data -> intro,
            'integral'      => $data -> integral,
            'preview_address'=> CloudStorage::downloadUrl($data -> preview_address),
            'cover'         => CloudStorage::downloadUrl($data -> cover),
            'count'         => $data -> count,
            'time_add'      => $data -> time_add,
            'has_download'  => isset($data -> hasManyDownload) ? 0 : 1,
            'user'          => [
                'id'    => $data -> belongsToUser -> id,
                'nickname' => $data -> belongsToUser -> nickname,
                'avatar'   => CloudStorage::downloadUrl($data -> belongsToUser -> avatar)
            ],
        ];
    }
}