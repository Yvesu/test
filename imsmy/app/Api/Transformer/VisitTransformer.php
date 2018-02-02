<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/1 0001
 * Time: 上午 9:47
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class VisitTransformer extends Transformer
{
    public  function transform($item)
    {
        $user = $item->belongToUser;
        return [
            'id'           =>  $user->id,
            'nickname'     =>  $user->nickname,
            'avatar'       =>  $user->avatar==null? null : CloudStorage::publicImage($user->avatar),
            'cover'        =>  $user->cover,
            'verify'       =>  $user->verify,
            'signature'    =>  $user->signature,
            'verify_info'  =>  $user->verify_info,
            'visit_id'      => $item -> id,
            'time'          => $item -> created_at,
        ];
    }
}