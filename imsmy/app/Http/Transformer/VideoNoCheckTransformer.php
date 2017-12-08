<?php

namespace App\Http\Transformer;

use App\Models\TweetLike;
//use App\Api\Transformer\UsersTransformer;
use App\Models\Admin\Administrator;
use App\Models\TweetActivity;
use App\Models\User;
use CloudStorage;
use Auth;

class VideoNoCheckTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($tweet)
    {
//        $user = Auth::guard('api')->user();

        $official = '';
        $activity = '';
        $verity = '';

        // 判断视频类型
        if(!$official = Administrator::where('user_id', $tweet->user_id) -> first()) {

            if(!$activity = TweetActivity::where('tweet_id', $tweet->id) -> first()) {

                if(!$verity = User::where('id', $tweet->user_id) -> where('verify', '<>', 0) -> first());
            }
        }


        return [
            'id'            =>  $tweet->id,
            'browse_times'  =>  $tweet->browse_times,
            'screen_shot'   =>  CloudStorage::downloadUrl($tweet->screen_shot),
            'type'          =>  $official ? '官方发布' : ($activity ? '参赛作品' : ($verity ? '认证用户' : '')),   // 视频类型，参赛、官方和认证,注意优先级
            'video'         =>  CloudStorage::downloadUrl($tweet->video),
            'duration'      =>  floor($tweet->duration),
            'user'          =>  $this->usersTransformer->transform($tweet->belongsToUser),
            'created_at'    =>  strtotime($tweet->created_at),
            'operator'      =>  '',
        ];
    }
}