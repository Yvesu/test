<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/1 0001
 * Time: 下午 15:26
 */

namespace App\Api\Transformer;


class FloderTransformer extends Transformer
{
    public  function transform($item)
    {
        return [
            'name'  => $item['name'],
        ];
    }
}