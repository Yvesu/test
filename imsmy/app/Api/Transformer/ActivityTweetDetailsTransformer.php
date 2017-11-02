<?php
namespace App\Api\Transformer;

use App\Api\Controllers\Traits\TweetsCommon;
use CloudStorage;
use Auth;
use App\Models\TweetLike;

class ActivityTweetDetailsTransformer extends Transformer
{
    use TweetsCommon;

    protected $usersTransformer;
    private $usersWithTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        UsersWithSubTransformer $usersWithTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->usersWithTransformer = $usersWithTransformer;
    }

    public function transform($data)
    {
        $user = Auth::guard('api')->user();

        $tweet = $data->hasOneTweet;
        
        $replies = $tweet->hasManyTweetReply->take(9);

        // 排名，写在缓存，一分钟一更新，写在trait文件里。
        $ranking = $this -> activityUsersRanking($data->activity_id);

        $ranking_array = array_flip($ranking->all());

        $reply = [];

        if($replies->first()) {
            foreach($replies as $value) {
                $reply[] = [
                    'nickname'  => $value -> belongsToUser -> nickname,
                    'content'  => $value -> content,
                ];
            }
        }

        return [
            'id'            => $data->tweet_id,
            'rank'          => ++$ranking_array[$data->tweet_id],
            'bonus'         => $data->bonus,
            'created_at'    => strtotime($tweet->created_at),
            'browse_times'  =>  $tweet->browse_times,
            'already_like'  =>  $user ? TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user->id)->count() : 0,
            'like_count'    =>  $tweet->like_count,
            'reply_count'   =>  $tweet->reply_count,
            'content'       => $tweet->hasOneContent->content,
            'screen_shot'   => CloudStorage::downloadUrl($tweet->screen_shot),
            'video'         => CloudStorage::downloadUrl($tweet->video),
            'user'          => $this->usersWithTransformer->transform($data->hasOneUser),
            'reply'         => $reply,
        ];
    }
}