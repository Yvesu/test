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

class FragmentTweetsReplyTransformer extends Transformer
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
        // 评论分数判断
        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

        return [
            'id'            =>  $tweet -> id,
            'grade'         =>  $grade <= 9.8 ? $grade : 9.8,
            'content'       =>  $tweet ->content,
            'user'          => $this->usersSearchTransformer->transform($tweet->belongsToUser),
            ''
        ];
    }
}