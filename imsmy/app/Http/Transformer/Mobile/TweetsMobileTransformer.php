<?php

namespace App\Http\Transformer\Mobile;

use App\Http\Transformer\Transformer;
use App\Http\Transformer\UsersTransformer;
use CloudStorage;

class TweetsMobileTransformer extends Transformer
{

    protected $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($tweet)
    {

        // 评论分数判断
        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

        return [
            'id'            =>  $tweet->id,
            'type'          =>  $tweet->type,
            'content'       =>  $tweet->content,
            'duration'      =>  secondsToMinute($tweet->duration),
            'tweet_grade'   =>  $grade <= 9.8 ? $grade : 9.8,
            'created_at'    =>  $tweet->created_at,
            'browse_times'  =>  $tweet->browse_times,
            'picture'       =>  CloudStorage::downloadUrl($tweet->type == 0 ? $tweet->screen_shot : json_decode($tweet->photo,true)[0]),
            'video'         =>  CloudStorage::downloadUrl($tweet->video),
            'user'          => $this->usersTransformer->transform($tweet->belongsToUser),
        ];
    }
}