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
            'user_id'     => $data->belongsToUser -> id,
            'nickname'    => $data->belongsToUser -> nickname,
            'verify'      => $data->belongsToUser -> verify,
            'verify_info' => $data->belongsToUser ->verify_info,
            'avatar'      => CloudStorage::downloadUrl($data->belongsToUser -> avatar),
        ];
    }

    public function ptransform($item)
    {
        $data = $item -> toArray();

        $a = [];
        foreach ($data as $v){
             $a[] = [
                'user_id'     => $v['id'],
                'nickname'    => $v['nickname'],
                'verify'      => $v['verify'],
                'verify_info' => $v['verify_info'],
                'avatar'      => CloudStorage::downloadUrl($v['avatar']),
            ];
        }
        return $a;
    }

}