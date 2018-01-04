<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/19 0019
 * Time: 下午 15:34
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;
use App\Models\TweetLike;
use Auth;

class PersonTweetsTransformer extends Transformer
{
    private $newuserSearchTransformer;

    private $userSearchTransformer;

    public function __construct
    (
        NewUserSearchTransformer $newUserSearchTransformer,
        UsersSearchTransformer $usersSearchTransformer
    )
    {
        $this->newuserSearchTransformer = $newUserSearchTransformer;
        $this->userSearchTransformer = $usersSearchTransformer;
    }

    public  function transform($tweet)
    {

        $user_from = Auth::guard('api')->user();
        // 评论分数判断
        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

        return [
           'id'            => $tweet->id,
           'type'          => $tweet->type,
           'duration'      => $tweet->duration,
           'location'      => $tweet->location,
           'content'       => $tweet->hasOneContent->content,
           'browse_times'  => $tweet->browse_times,
           'like_count'    => $tweet->like_count,
           'reply_count'   => $tweet->reply_count,
           'grade'         =>  $grade <= 9.8 ? $grade : 9.8,
           'video'         => CloudStorage::downloadUrl($tweet->video),
           'channel'       => $tweet->belongsToManyChannel->count() ? $tweet->belongsToManyChannel->first()->name : '',
           'screen_shot'   => $tweet->screen_shot === null ? null : CloudStorage::downloadUrl($tweet->screen_shot),
         //  'user'          => $this->userSearchTransformer->transform($tweet->belongsToUser),
           'already_like'  =>  $user_from ? (TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user_from->id)->first() ? 1 : 0) : 0,
           'created_at'     => strtotime($tweet->created_at),
       ];
    }
}