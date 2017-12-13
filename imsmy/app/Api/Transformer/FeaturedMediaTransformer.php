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
            'user_id'     => $data -> id,
            'nickname'    => $data -> nickname,
            'verify'      => $data -> verify,
            'verify_info' => $data ->verify_info,
            'avatar'      => CloudStorage::downloadUrl($data -> avatar),
        ];
    }
}