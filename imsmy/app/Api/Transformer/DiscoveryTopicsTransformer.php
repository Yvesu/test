<?php

namespace App\Api\Transformer;

use CloudStorage;
class DiscoveryTopicsTransformer extends Transformer
{

    public function transform($topic)
    {
        return [
            'id'                   => $topic->id,
            'type'                 => $topic->type,                             // 类型 0为话题，1为活动
            'name'                 => $topic->name,                             // 名称
            'forwarding_time'      => $topic->forwarding_time,                  // 阅读数
            'comment_time'         => $topic->comment_time,                     // 评论数
            'icon'                 => CloudStorage::downloadUrl($topic->icon),  // 图片地址
            'comment'              => $topic->comment,                          // 内容
            'work_count'           => $topic->work_count,                       // 作品数
        ];
    }
}