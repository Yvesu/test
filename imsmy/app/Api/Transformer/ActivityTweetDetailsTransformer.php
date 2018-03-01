<?php
namespace App\Api\Transformer;

use App\Api\Controllers\Traits\TweetsCommon;
use App\Models\Subscription;
use App\Models\UserCollections;
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
//        $ranking = $this -> activityUsersRanking($data->activity_id);

//        $ranking_array = array_flip($ranking->all());

        $reply = [];

        if($replies->first()) {
            foreach($replies as $value) {
                $reply[] = [
                    'nickname'  => $value -> belongsToUser -> nickname,
                    'content'  => $value -> content,
                ];
            }
        }

        if ($user){
            $res_1 = Subscription::OfAttention($user->id,$data->hasOneUser->id)->first();
            $res_2 = Subscription::OfAttention($data->hasOneUser->id,$user->id)->first();
            if ($res_1){
                if ($res_2){
                    $result = 2;
                }else{
                    $result = 1;
                }
            }else{
                $result = 0;

                if ($user->id === $data->hasOneUser->id){
                    $result = 3;
                }

            }

        }else{
            $result = 0;
        }

        $grade = $tweet->tweet_grade_total ? number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1) : 0;

        return [
            'id'                =>      $data->tweet_id,
//            'rank'              =>      ++$ranking_array[$data->tweet_id],
            'bonus'             =>      $data->bonus,
            'created_at'        =>      strtotime($tweet->created_at),
            'browse_times'      =>      $tweet->browse_times,
            'already_like'      =>      $user ? TweetLike::where('tweet_id',$tweet->id)->where('user_id',$user->id)->count() : 0,
            'like_count'        =>      $tweet->like_count,
            'reply_count'       =>      $tweet->reply_count,
            'content'           =>      $tweet->hasOneContent->content,
            'screen_shot'       =>      CloudStorage::downloadUrl($tweet->screen_shot),
            'video'             =>      $tweet->join_video ? CloudStorage::downloadUrl($tweet->join_video) : ($tweet -> transcoding_video ? CloudStorage::downloadUrl($tweet -> transcoding_video) : CloudStorage::downloadUrl($tweet->video)),
            'original_video'    =>      $tweet -> video_m3u8 ? CloudStorage::downloadUrl($tweet -> video_m3u8) : '',
            'normal_video'      =>      $tweet -> norm_video ? CloudStorage::downloadUrl($tweet -> norm_video) : '',
            'high_video'        =>      $tweet -> high_video ? CloudStorage::downloadUrl($tweet -> high_video) : '',
            'user'              =>      $this->usersWithTransformer->transform($data->hasOneUser),
            'reply'             =>      $reply,
            'attention'         =>      $result,
            'duration'          =>      $tweet -> duration,
            'location'          =>      $tweet -> location,
            'grade'             =>      $grade,
            'collections'       =>      $user ? (UserCollections::where('user_id',$user->id)->where('type',3)->where('status',1)->where('type_id',$tweet->id)->first() ? 1 : 0) : 0,
            'lgt'               =>      $tweet -> lgt,
            'lat'               =>      $tweet -> lat,
            'is_download'       =>      $tweet -> is_download,
        ];
    }
}