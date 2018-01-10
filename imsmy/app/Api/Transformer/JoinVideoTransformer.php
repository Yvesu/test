<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/10 0010
 * Time: 下午 19:25
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class JoinVideoTransformer extends Transformer
{
    public  function transform($item)
    {
        return [
            'id'            =>  $item -> id,
            'name'          =>  $item -> name,
            'intro'         =>  $item -> intro,
            'image'         =>  CloudStorage::downloadUrl($item -> image),
            'weight_height' =>  $item -> weight_height,
            'duration'      =>  $item -> duration,
            'head_video'    =>  $item -> head_video ? CloudStorage::downloadUrl($item -> head_video) : '',
            'tail_video'    =>  $item -> tail_video ? CloudStorage::downloadUrl($item -> tail_video) : '',
            'down_count'    =>  $item -> down_count,
        ];
    }
}