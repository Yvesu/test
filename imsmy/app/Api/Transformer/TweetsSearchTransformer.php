<?php

namespace App\Api\Transformer;

use CloudStorage;

class TweetsSearchTransformer extends Transformer
{

    public function transform($tweet)
    {
        return [
            'id'            =>  $tweet->id,
            'type'          =>  $tweet->type,
            'like_count'    =>  $tweet->like_count,     // 点赞总数
            'browse_times'  =>  $tweet->browse_times,  // 观看次数
            'content'       =>  $tweet->hasOneContent->content,
            'picture'       =>  CloudStorage::downloadUrl($tweet->type == 0 ? $tweet->screen_shot : json_decode($tweet->photo,true)[0]),
        ];
    }
}