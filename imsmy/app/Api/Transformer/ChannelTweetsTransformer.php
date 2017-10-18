<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/30
 * Time: 20:35
 */

namespace App\Api\Transformer;

use CloudStorage;
use Auth;
use App\Models\Subscription;
use App\Models\TweetLike;

class ChannelTweetsTransformer extends Transformer
{
    private $usersSearchTransformer;

    public function __construct(
        UsersSearchTransformer $usersSearchTransformer
    )
    {
        $this->usersSearchTransformer = $usersSearchTransformer;
    }

    public function transform($tweet)
    {

        // 获取图片信息
//        if($tweet->photo){
//
//            // 解析成数组
//            $photo = json_decode($tweet->photo,true);
//
//            // 筛选图片的信息，最多三张
//            if(count($photo) <= 3){
//
//                $new_photo = $photo;
//            }else{
//
//                // 取前三张图片
//                $new_photo = array_slice($photo,0,3);
//            }
//        }

        // 判断用户是否为登录状态
        $user_from = Auth::guard('api')->user();

//        $already_follow = '';
//
//        if($user_from) {
//
//            // 判断登录用户是否关注对方
//            $already_follow = Subscription::ofAttention($user_from->id, $tweet->belongsToUser->user_id)->first();
//
//            // 判断对方是否为登录用户粉丝
//            $already_fans = Subscription::ofAttention($tweet->belongsToUser->user_id, $user_from->id)->first();
//        }

        return [
            'id'            => $tweet->id,
            'type'          => $tweet->type,
            'duration'      => $tweet->duration,
            'location'      => $tweet->location,
            'content'       => $tweet->hasOneContent->content,
            'browse_times'  => $tweet->browse_times,
            'like_count'    => $tweet->like_count,
            'reply_count'   => $tweet->reply_count,
            'video'         => CloudStorage::downloadUrl($tweet->video),
            'channel'       => $tweet->belongsToManyChannel->count() ? $tweet->belongsToManyChannel->first()->name : '',
            // 视频截图
            'screen_shot'   => $tweet->screen_shot === null ? null : CloudStorage::downloadUrl($tweet->screen_shot),
            // 相册
//            'photo'         => $tweet->photo === null ? null : CloudStorage::downloadUrl($new_photo),
            'user'          => $this->usersSearchTransformer->transform($tweet->belongsToUser),
            'already_like'  =>  $user_from ? (TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user_from->id)->first() ? 1 : 0) : 0,
//            'shot_width_height' => $tweet->shot_width_height,
            'created_at'     => strtotime($tweet->created_at),
        ];
    }
}