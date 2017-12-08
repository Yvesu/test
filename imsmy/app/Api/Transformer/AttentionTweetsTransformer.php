<?php
namespace App\Api\Transformer;

use CloudStorage;
use Auth;
use App\Models\TweetLike;
use App\Api\Controllers\TweetPlayController;

class AttentionTweetsTransformer extends Transformer
{
    protected $usersTransformer;
    protected $tweetSimplyRepliesTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        TweetSimplyRepliesTransformer $tweetSimplyRepliesTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->tweetSimplyRepliesTransformer = $tweetSimplyRepliesTransformer;
    }

    public function transform($tweet)
    {
        // 判断用户是否为登录状态
        $user = Auth::guard('api')->user();

        // 判断动态类型，如果是图片，观看次数自动＋1  目前版本又修改为暂停图片，只有视频 20170908
//        if(1 === $tweet->type){
//            $tweetPlay = new TweetPlayController();
//            $tweetPlay -> countIncrement($tweet->id,$user);
//        }

        // 评论分数判断
        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

        return [
            'id'            => $tweet->id,
            'type'          => $tweet->type,
            'content'       => $tweet->hasOneContent->content,
            'video'         =>  CloudStorage::downloadUrl($tweet->video),
            'browse_times'  => $tweet->browse_times,
            'like_count'    => $tweet->like_count,
            'grade'   =>  $grade <= 9.8 ? $grade : 9.8,
            'channel'       => $tweet->belongsToManyChannel->count() ? $tweet->belongsToManyChannel->first()->name : '',
            'reply_count'   => $tweet->reply_count,
            'already_like'  => $user ? (TweetLike::where('user_id',$user->id)->where('tweet_id',$tweet->id)->first() ? 1 : 0) : 0,
            'reply'         => $tweet->hasManyTweetReply ? $this->tweetSimplyRepliesTransformer->transformCollection($tweet->hasManyTweetReply->take(3)->all()) : [],
//            // 视频截图
            'screen_shot'   => $tweet->screen_shot === null ? '' : CloudStorage::downloadUrl($tweet->screen_shot),
//            // 相册
//            'photo'         => $tweet->photo == null ? [] : CloudStorage::download(json_decode($tweet->photo,true)),
            'user'          => $this->usersTransformer->transform($tweet->belongsToUser),
            'shot_width_height' => $tweet->shot_width_height,
            'location'      => $tweet->location,
            'duration'      => $tweet->duration,
            'created_at'     => strtotime($tweet->created_at),
        ];
    }


}