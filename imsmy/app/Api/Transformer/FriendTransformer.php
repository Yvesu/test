<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/17
 * Time: 11:20
 */

namespace App\Api\Transformer;

use CloudStorage;

class FriendTransformer extends Transformer
{
    // 继承了Transformer中的接口transform并实现
    public function transform($friend)
    {
        return [
            'id'         => $friend->to,
            'nickname'   => $friend->belongsToUser->nickname,
            'phone'      => $friend->belongsToUser->hasOneLocalAuth === null ? null : (int)$friend->belongsToUser->hasOneLocalAuth->username,
            'remark'     => $friend->remark,
            'top'        => $friend->top === null ? null : strtotime($friend->top),
            'avatar'     => $friend->belongsToUser->avatar === null ? null : CloudStorage::downloadUrl($friend->belongsToUser->avatar),
            'hash_avatar'=> $friend->belongsToUser->hash_avatar,
            'updated_at' => strtotime($friend->updated_at)
        ];
    }
}