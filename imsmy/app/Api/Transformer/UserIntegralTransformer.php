<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/6 0006
 * Time: 下午 16:15
 */
namespace App\Api\Transformer;

class UserIntegralTransformer extends Transformer
{
    public function transform($data)
    {
        return [
            'integral_count' => $data -> integral_count,
            'type' => $data -> type,
            'valid_time' => $data -> valid_time,
        ];
    }
}