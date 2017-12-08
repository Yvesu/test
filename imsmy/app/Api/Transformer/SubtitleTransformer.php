<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 下午 16:25
 */
namespace App\Api\Transformer;

class SubtitleTransformer extends Transformer
{
    public function transform($item)
    {
        return [
            'name'       => $item['name'],
            'content'    => $item['content'],
            'start_time' => $item['start_time'],
            'end_time'   => $item['end_time'],
        ];
    }
}