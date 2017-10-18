<?php
namespace App\Api\Transformer;

use CloudStorage;
use Auth;

class MakeFileTransformer extends Transformer
{
    public function transform($data)
    {
        $user = Auth::guard('api') -> user();

        return [
            'id'        => $data -> id,
            'name'      => $data -> name,
            'integral'  => $data -> integral,
            'cover'     => CloudStorage::downloadUrl($data -> cover),
            'count'     => $data -> count,
            'has_download' => $user ? (isset($data -> hasManyDownload) ? 0 : 1) : 0,
        ];
    }
}