<?php

namespace App\Api\Transformer;

use App\Models\TweetLike;
use CloudStorage;
use Auth;
class TweetsDetailsTransformer extends Transformer
{
    private $usersWithTransformer;

    public function __construct(
        UsersWithSubTransformer $usersWithTransformer
    )
    {
        $this->usersWithTransformer = $usersWithTransformer;
    }

    public function transform($tweet)
    {
        $user = Auth::guard('api')->user();

        // 评论分数判断
        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

//        // 初始化
        $topics = [];
//
//        // 处理话题数组
        if($tweet->belongsToManyTopic->count()){

            foreach($tweet->belongsToManyTopic as $value){

                $topics[] = (object)['name'=>$value->name,'id'=>$value -> id];
            }
        }

        return [
            'id'            =>  $tweet->id,
            'type'          =>  $tweet->type,
            'browse_times'  =>  $tweet->browse_times,
            'video'         =>  CloudStorage::downloadUrl($tweet->video),
            'photo'         =>  $tweet->photo === null ? [] : CloudStorage::downloadUrl(json_decode($tweet->photo,true)),
            'already_like'  =>  $user ? TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user->id)->count() : 0,
            'like_count'    =>  $tweet->like_count,
            'reply_count'   =>  $tweet->reply_count,
//            'retweet_count' =>  $tweet->retweet_count,
            'content'       =>  $tweet->hasOneContent->content,
            'shot_width_height' =>  $tweet->shot_width_height,
            'screen_shot'   =>  CloudStorage::downloadUrl($tweet->screen_shot),
            'user'          =>  $this->usersWithTransformer->transform($tweet->belongsToUser),
            'created_at'    =>  strtotime($tweet->created_at),
            'tweet_grade'   =>  $grade <= 9.8 ? $grade : 9.8,
            'topics'        =>  $topics,
            'at'            =>  $tweet->hasManyAt,
            'original'      =>  $tweet->original,
            'retweet'       =>  $tweet->retweet,
            'location'      => $tweet ->location,
        ];
    }

    public function ptransform($tweet)
    {
        $user = Auth::guard('api')->user();

        // 评论分数判断
        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

//        // 初始化
        $topics = [];
//
//        // 处理话题数组
        if($tweet->belongsToManyTopic->count()){

            foreach($tweet->belongsToManyTopic as $value){

                $topics[] = (object)['name'=>$value->name,'id'=>$value -> id];
            }
        }

        return [
            'id'            =>  $tweet->id,
            'type'          =>  $tweet->type,
            'browse_times'  =>  $tweet->browse_times,
            'video'         =>  CloudStorage::downloadUrl($tweet->video),
            'photo'         =>  $tweet->photo === null ? [] : CloudStorage::downloadUrl(json_decode($tweet->photo,true)),
            'already_like'  =>  $user ? TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user->id)->count() : 0,
            'like_count'    =>  $tweet->like_count,
            'reply_count'   =>  $tweet->reply_count,
//            'retweet_count' =>  $tweet->retweet_count,
            'content'       =>  $tweet->hasOneContent->content,
            'frag_content'  => $tweet->hasOneFragment->name,
            'shot_width_height' =>  $tweet->shot_width_height,
            'screen_shot'   =>  CloudStorage::downloadUrl($tweet->screen_shot),
            'user'          =>  $this->usersWithTransformer->transform($tweet->belongsToUser),
            'created_at'    =>  strtotime($tweet->created_at),
            'tweet_grade'   =>  $grade <= 9.8 ? $grade : 9.8,
            'topics'        =>  $topics,
            'at'            =>  $tweet->hasManyAt,
            'original'      =>  $tweet->original,
            'retweet'       =>  $tweet->retweet,
            'location'      => $tweet ->location,
        ];
    }

}