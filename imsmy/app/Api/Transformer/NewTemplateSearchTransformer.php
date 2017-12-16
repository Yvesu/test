<?php
namespace App\Api\Transformer;

use App\Facades\CloudStorage;

class NewTemplateSearchTransformer extends Transformer
{
    public  function transform($item)
    {
        return [
            'id'            =>  $item['id'],
            'name'          =>  $item['name'],
            'preview'       =>  CloudStorage::downloadUrl( $item['preview_address']),
            'address'       =>  CloudStorage::privateUrl_zip($item['address']),
            'cover'         =>  CloudStorage::downloadUrl($item['cover']),
            'duration'      =>  $item['duration'],
            'watch_count'   =>  $item['watch_count'],
            'type'          =>  $item['belongs_to_folder']['name'],
        ];
    }
}