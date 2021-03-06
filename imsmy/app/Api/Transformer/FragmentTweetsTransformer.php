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
class FragmentTweetsTransformer extends Transformer
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
        // 判断用户是否为登录状态
        $user_from = Auth::guard('api')->user();

        // 评论分数判断
        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

        return [
            'id'            => $tweet->id,
            'type'          => $tweet->type,
            'duration'      => $tweet->duration,
            'location'      => $tweet->location,
            'fragment_name' => $tweet->hasOneFragment->name,
            'content'       => $tweet->hasOneContent->content,
            'browse_times'  => $tweet->browse_times,
            'like_count'    => $tweet->like_count,
            'reply_count'   => $tweet->reply_count,
            'location'      => $tweet->location,
            'grade'   =>  $grade <= 9.8 ? $grade : 9.8,
            'video'         => CloudStorage::downloadUrl($tweet->video),
            'channel'       => $tweet->belongsToManyChannel->count() ? $tweet->belongsToManyChannel->first()->name : '',
            'reply'         => $this-> reply($tweet->hasManyTweetReply),
            'screen_shot'   => $tweet->screen_shot === null ? null : CloudStorage::downloadUrl($tweet->screen_shot),
            'user'          => $this->usersSearchTransformer->transform($tweet->belongsToUser),
            'already_like'  =>  $user_from ? (TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user_from->id)->first() ? 1 : 0) : 0,
            'created_at'     => strtotime($tweet->created_at),
        ];
    }

    public function reply($reply)
    {
        $a = [];
        foreach ($reply as $v){
            $a[] = [
                'id'         => $v['id'],
                'content'    =>$v['content'],
                'grade'      => $v['grade'] <= 9.8 ? $v['grade'] : 9.8,
                'nickname'       => $v->belongsToUser->nickname,
            ];
        }
        return $a;
    }

}