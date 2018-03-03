<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/1 0001
 * Time: 下午 20:36
 */

namespace App\Api\Transformer;


class GoldNumTransformer extends Transformer
{
    public  function transform($item)
    {
        return [
            'gold_num'  => $item->gold_num,
            'money'     => ($item->money)/100,
        ];
    }
}