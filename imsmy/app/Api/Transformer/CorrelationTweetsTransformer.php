<?php
namespace App\Api\Transformer;


use App\Facades\CloudStorage;
use Auth;

class CorrelationTweetsTransformer extends Transformer
{
    private $usersTransformer;
    public function __construct
    (
        UsersTransformer $usersTransformer
    )
    {
        $this -> usersTransformer = $usersTransformer;
    }

    public function transform($tweet)
    {
        // 判断用户是否为登录状态
        $user_from = Auth::guard('api')->user();

        // 评论分数判断
        $grade = $tweet['tweet_grade_total'] ? number_format($tweet['tweet_grade_total']/$tweet['tweet_grade_times'],1) : 0;

        return [
            'id'            => $tweet['id'],
            'type'          => $tweet['type'],
            'duration'      => $tweet['duration'],
            'location'      => $tweet['location'],
            'content'       => $tweet['has_one_content']['content'],
            'browse_times'  => $tweet['browse_times'],
            'like_count'    => $tweet['like_count'],
            'reply_count'   => $tweet['reply_count'],
            'grade'         => $grade <= 9.8 ? $grade : 9.8,
            'video'         => $tweet['join_video'] ? CloudStorage::downloadUrl($tweet['join_video']) : ($tweet['transcoding_video'] ? CloudStorage::downloadUrl($tweet['transcoding_video']) : CloudStorage::downloadUrl($tweet['video'])),
            'original_video'    => $tweet['video_m3u8'] ? CloudStorage::downloadUrl($tweet['video_m3u8']) : '',
            'normal_video'    => $tweet['norm_video'] ? CloudStorage::downloadUrl($tweet['norm_video']) : '',
            'high_video'    => $tweet['high_video'] ? CloudStorage::downloadUrl($tweet['high_video']) : '',
            'screen_shot'   => $tweet['screen_shot'] === null ? null : CloudStorage::downloadUrl($tweet['screen_shot']),
            'user'          => $this->usersTransformer->tweettransformer($tweet['belongs_to_user']),
            'already_like'  => $user_from ? (TweetLike::where('tweet_id',$tweet['id'])->where('user_id',$user_from->id)->first() ? 1 : 0) : 0,
            'created_at'    => strtotime($tweet['created_at']),
        ];
    }

}



