<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/29 0029
 * Time: 下午 15:41
 */

namespace App\Api\Transformer;


class TweetPhoneTransformer extends Transformer
{
    public  function transform($item)
    {
       return [
           'phone_type' => $item -> phone_type,
           'phone_os'   => $item -> phone_os,
           'camera_type'=>$item->camera_type,
       ];
    }
}