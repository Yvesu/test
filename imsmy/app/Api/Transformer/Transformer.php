<?php
/**
 * Created by PhpStorm.
 * User: mabiao
 * Date: 2016/4/13
 * Time: 18:49
 */

namespace App\Api\Transformer;


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
        // 对所传数据进行回调函数处理,第一个参数Callback可以为数组，
        // Callback 函数不仅可以是一个简单的函数，它还可以是一个对象的方法，包括静态类的方法。
        return array_map([$this,'transform'],$items);
    }

    /**
     * @param $item
     * @return mixed
     * 定义接口，供子类继承实现
     */
    public abstract function transform($item);
}