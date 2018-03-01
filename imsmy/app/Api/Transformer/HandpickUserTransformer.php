<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/28 0028
 * Time: 下午 15:43
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class HandpickUserTransformer extends Transformer
{
    public  function transform($item)
    {
        return[
            'id'            => $item->id,
            'nickname'      => $item->nickname,
            'verify'        => $item->verify,
            'verify_info'   => $item->verify_info,
            'avatar'        => CloudStorage::downloadUrl($item->avatar),
        ];
    }
}