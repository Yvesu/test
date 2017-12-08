<?php

namespace App\Api\Transformer;

use CloudStorage;
class HotTweetsTransformer extends Transformer
{
    protected $usersTransformer;

    public function __construct(UsersTransformer $usersTransformer)
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($tweet)
    {
        return [
            'id'            => $tweet->id,
            'style'         => 1,     // 1代表动态
            'type'          => $tweet->type,
            'content'       => $tweet->hasOneContent->content,
            //TODO 图片时的处理
            'screen_shot'   => CloudStorage::downloadUrl($tweet->screen_shot),
            // 视频处理
            'video'   => CloudStorage::downloadUrl($tweet->video),
            // 图片处理
            'photo'   => $tweet->photo,
            //TODO label
            'label'         => $tweet->label,
            'user'          => $this->usersTransformer->transform($tweet->belongsToUser),
            'shot_width_height' => $tweet->shot_width_height,
            'like' => $tweet->like,
            'reply' => $tweet->reply,
            'created_at'    => strtotime($tweet->created_at)
        ];
    }
}