<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 上午 11:38
 */
namespace App\Api\Transformer;

class StoryboardTransformer extends Transformer
{
    public function  transform($item)
    {
        return [
            'id'           =>  $item['id'],
            'name'         =>  $item['name'],
            'address'      =>  $item['address'],
            'isliveshot'   =>  (boolean)$item['isliveshot'],
            'address'      =>  $item['address'],
        ];
    }
}