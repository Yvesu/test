<?php
namespace App\Api\Transformer;

use CloudStorage;

/**
 * 发现页面精选媒体
 *
 * Class FeaturedMediaTransformer
 * @package App\Api\Transformer
 */
class FeaturedMediaTransformer extends Transformer
{
    public function transform($data)
    {

        return [
            'user_id'     => $data -> user_id,
            'nickname'    => $data -> belongsToUser -> nickname,
            'verify'      => $data -> belongsToUser -> verify,
            'verify_info' => $data -> belongsToUser -> verify_info,
            'avatar'      => CloudStorage::downloadUrl($data -> belongsToUser -> avatar),
        ];
    }
}