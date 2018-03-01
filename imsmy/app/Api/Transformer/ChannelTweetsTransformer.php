<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/30
 * Time: 20:35
 */

namespace App\Api\Transformer;

use App\Models\UserCollections;
use CloudStorage;
use Auth;
use App\Models\Subscription;
use App\Models\TweetLike;

class ChannelTweetsTransformer extends Transformer
{
    private $usersSearchTransformer;

    private $tweetPhoneTransformer;

    public function __construct(
        UsersSearchTransformer $usersSearchTransformer,
        TweetPhoneTransformer $tweetPhoneTransformer
    )
    {
        $this->usersSearchTransformer = $usersSearchTransformer;
        $this->tweetPhoneTransformer = $tweetPhoneTransformer;
    }

    public function transform($tweet)
    {
        // 判断用户是否为登录状态
        $user_from = Auth::guard('api')->user();

        // 评论分数判断
        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

        return [
            'id'            => $tweet->id,
            'type'          => $tweet->type,
            'duration'      => $tweet->duration,
            'location'      => $tweet->location,
            'lgt'           => $tweet->lgt ?: '',
            'lat'           => $tweet->lat ?: '',
            'content'       => $tweet->hasOneContent->content,
            'browse_times'  => $tweet->browse_times,
            'like_count'    => $tweet->like_count,
            'reply_count'   => $tweet->reply_count,
            'grade'         => $grade <= 9.8 ? $grade : 9.8,
            'video'         => $tweet->join_video ? CloudStorage::downloadUrl($tweet->join_video) : ($tweet -> transcoding_video ? CloudStorage::downloadUrl($tweet -> transcoding_video) : CloudStorage::downloadUrl($tweet->video)),
            'original_video'    => $tweet -> video_m3u8 ? CloudStorage::downloadUrl($tweet -> video_m3u8) : '',
            'normal_video'    => $tweet -> norm_video ? CloudStorage::downloadUrl($tweet -> norm_video) : '',
            'high_video'    => $tweet -> high_video ? CloudStorage::downloadUrl($tweet -> high_video) : '',
            'channel'       => $tweet->belongsToManyChannel->count() ? $tweet->belongsToManyChannel->first()->name : '',
            'screen_shot'   => $tweet->screen_shot === null ? null : CloudStorage::downloadUrl($tweet->screen_shot),
            'user'          => $this->usersSearchTransformer->transform($tweet->belongsToUser),
            'already_like'  => $user_from ? (TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user_from->id)->first() ? 1 : 0) : 0,
            'created_at'    => strtotime($tweet->created_at),
            'phone'         => $this->tweetPhoneTransformer->transform($tweet->hasOnePhone),
            'phone_id'      => $tweet->phone_id,
            'collections'   => $user_from ? (UserCollections::where('user_id',$user_from->id)->where('type',3)->where('status',1)->where('type_id',$tweet->id)->first() ? 1 : 0) : 0,
            'is_download'   => $tweet->is_download,
        ];
    }

    /**
     * 置顶动态  （格式化时间戳为时分秒）
     * @param $tweet
     * @return array
     */
    public function ptransform($tweets)
    {
        $arr = [];
        foreach ($tweets as $tweet){

            $user_from = Auth::guard('api')->user();

            // 评论分数判断
            $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

            $arr[] =  [
                'id'            => $tweet->id,
                'type'          => $tweet->type,
                'duration'      => $tweet->duration,
                'location'      => $tweet->location,
                'content'       => $tweet->hasOneContent->content,
                'browse_times'  => $tweet->browse_times,
                'like_count'    => $tweet->like_count,
                'reply_count'   => $tweet->reply_count,
                'grade'         =>  $grade <= 9.8 ? $grade : 9.8,
                'video'         => $tweet->join_video ? CloudStorage::downloadUrl($tweet->join_video) : ($tweet -> transcoding_video ? CloudStorage::downloadUrl($tweet -> transcoding_video) : CloudStorage::downloadUrl($tweet->video)),
                'original_video'    => $tweet -> video_m3u8 ? CloudStorage::downloadUrl($tweet -> video_m3u8) : '',
                'normal_video'    => $tweet -> norm_video ? CloudStorage::downloadUrl($tweet -> norm_video) : '',
                'high_video'    => $tweet -> high_video ? CloudStorage::downloadUrl($tweet -> high_video) : '',
                'channel'       => $tweet->belongsToManyChannel->count() ? $tweet->belongsToManyChannel->first()->name : '',
                'screen_shot'   => $tweet->screen_shot === null ? null : CloudStorage::downloadUrl($tweet->screen_shot),
                'user'          => $this->usersSearchTransformer->transform($tweet->belongsToUser),
                'already_like'  =>  $user_from ? (TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user_from->id)->first() ? 1 : 0) : 0,
                'created_at'    => strtotime($tweet->created_at),
            ];
        }
        return $arr;
    }
}