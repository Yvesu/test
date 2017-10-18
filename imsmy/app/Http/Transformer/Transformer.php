<?php

namespace App\Http\Transformer;


/**
 * Class Transformer
 * @package App\Api\Transformer
 */
abstract class Transformer
{
    /**
     * @param $items
     * @return array
     */
    public function transformCollection($items)
    {
        // 对所传数据进行回调函数处理
        return array_map([$this,'transform'],$items);
    }

    /**
     * @param $item
     * @return mixed
     * 定义接口，供子类继承实现
     */
    public abstract function transform($item);
}