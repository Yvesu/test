<?php

namespace App\Api\Transformer;

use CloudStorage;

class TweetsAtTransformer extends Transformer
{

    public function transform($tweet)
    {
        return [
            'id'            =>  $tweet -> id,
            'type'          =>  $tweet -> type,
            'content'       =>  $tweet -> hasOneContent->content,
            'duration'      =>  $tweet -> duration,
            'created_at'    =>  strtotime($tweet->created_at),
            'nickname'      =>  $tweet->belongsToUser->nickname,
            'browse_times'  =>  $tweet->browse_times,
            'video' =>  CloudStorage::downloadUrl( $tweet ->video),
            'picture'       =>  CloudStorage::downloadUrl($tweet->type == 0 ? $tweet->screen_shot : json_decode($tweet->photo,true)[0]),
        ];
    }
}