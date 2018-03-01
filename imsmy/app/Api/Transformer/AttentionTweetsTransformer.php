<?php
namespace App\Api\Transformer;

use App\Models\UserCollections;
use CloudStorage;
use Auth;
use App\Models\TweetLike;
use App\Api\Controllers\TweetPlayController;

class AttentionTweetsTransformer extends Transformer
{
    protected $usersTransformer;
    protected $tweetSimplyRepliesTransformer;
    private $tweetPhoneTransformer;
    public function __construct(
        UsersTransformer $usersTransformer,
        TweetSimplyRepliesTransformer $tweetSimplyRepliesTransformer,
        TweetPhoneTransformer $tweetPhoneTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->tweetSimplyRepliesTransformer = $tweetSimplyRepliesTransformer;
        $this->tweetPhoneTransformer = $tweetPhoneTransformer;
    }

    public function transform($tweet)
    {
        // 判断用户是否为登录状态
        $user = Auth::guard('api')->user();

        // 评论分数判断
        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

        return [
            'id'                =>      $tweet->id,
            'type'              =>      $tweet->type,
            'content'           =>      $tweet->hasOneContent->content,
            'video'             =>      $tweet->join_video ? CloudStorage::downloadUrl($tweet->join_video) : ($tweet -> transcoding_video ? CloudStorage::downloadUrl($tweet -> transcoding_video) : CloudStorage::downloadUrl($tweet->video)),
            'original_video'    =>      $tweet -> video_m3u8 ? CloudStorage::downloadUrl($tweet -> video_m3u8) : '',
            'normal_video'      =>      $tweet -> norm_video ? CloudStorage::downloadUrl($tweet -> norm_video) : '',
            'high_video'        =>      $tweet -> high_video ? CloudStorage::downloadUrl($tweet -> high_video) : '',
            'browse_times'      =>      $tweet->browse_times,
            'like_count'        =>      $tweet->like_count,
            'grade'             =>      $grade <= 9.8 ? $grade : 9.8,
            'channel'           =>      $tweet->belongsToManyChannel->count() ? $tweet->belongsToManyChannel->first()->name : '',
            'reply_count'       =>      $tweet->reply_count,
            'already_like'      =>      $user ? (TweetLike::where('user_id',$user->id)->where('tweet_id',$tweet->id)->first() ? 1 : 0) : 0,
            'reply'             =>      $tweet->hasManyTweetReply ? $this->tweetSimplyRepliesTransformer->transformCollection($tweet->hasManyTweetReply->take(3)->all()) : [],
            'screen_shot'       =>      $tweet->screen_shot === null ? '' : CloudStorage::downloadUrl($tweet->screen_shot),
            'user'              =>      $this->usersTransformer->transform($tweet->belongsToUser),
            'shot_width_height' =>      $tweet->shot_width_height,
            'location'          =>      $tweet->location,
            'lgt'               =>      $tweet->lgt ?: '',
            'lat'               =>      $tweet->lat ?: '',
            'duration'          =>      $tweet->duration,
            'created_at'        =>      strtotime($tweet->created_at),
            'phone'             =>      $this->tweetPhoneTransformer->transform($tweet->hasOnePhone),
            'phone_id'          =>      $tweet->phone_id,
            'collections'       =>      $user ? (UserCollections::where('user_id',$user->id)->where('status',1)->where('type',3)->where('type_id',$tweet->id)->first() ? 1 : 0) : 0,
            'is_download'       =>      $tweet->is_download,
        ];
    }

}