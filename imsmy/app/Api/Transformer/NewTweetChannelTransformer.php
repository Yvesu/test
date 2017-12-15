<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/15 0015
 * Time: 下午 16:52
 */

namespace App\Api\Transformer;


class NewTweetChannelTransformer extends Transformer
{
    public  function transform($item)
    {
        return [
            'name'      =>      $item['name'],
        ];
    }
}