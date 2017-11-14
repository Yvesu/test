<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 下午 13:08
 */
namespace App\Api\Transformer;

class FragmentTypeTransformer extends Transformer
{
    public  function  transform($item)
    {
        return [
            'name' => $item['name'],
        ];
    }
}