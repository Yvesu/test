<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/9
 * Time: 16:01
 */

namespace App\Api\Transformer;


use App\Models\Tweet;
use App\Models\TweetReply;
use App\Models\TweetTrophyLog;
use App\Models\User;

class NotificationsTransformer extends  Transformer
{
    private $tweetsTransformer;

    private $tweetRepliesTransformer;

    private $usersTransformer;

    private $tweetsAtTransformer;

    private $tweetsTrophyLog;

    public function __construct(TweetsTransformer $tweetsTransformer,
                                TweetRepliesTransformer $tweetRepliesTransformer,
                                UsersTransformer $usersTransformer,
                                TweetsAtTransformer $tweetsAtTransformer,
                                TweetsTrophyLogTransformer $tweetsTrophyLog
    )
    {
        $this->tweetsTransformer = $tweetsTransformer;
        $this->tweetRepliesTransformer = $tweetRepliesTransformer;
        $this->usersTransformer = $usersTransformer;
        $this->tweetsAtTransformer = $tweetsAtTransformer;
        $this->tweetsTrophyLog = $tweetsTrophyLog;
    }
    
    public function transform($notification)
    {
        if ( in_array($notification->type,[0,1,3],TRUE)){
            $tweet = Tweet::find($notification->type_id);
        }else{
            $tweet_id = TweetReply::find($notification->type_id)->tweet_id;
            $tweet = Tweet::find($tweet_id);
        }
        $user = User::find($notification->user_id);
        //查看用户状态
        $user_status =  (in_array($user->status,[1],TRUE) || in_array($user->active,[5],TRUE));
        //查看动态的状态
        $tweet_status  = in_array($tweet->active,[0,1],TRUE);
        return [
            'id'             => $notification->id,
            'type'           => $notification->type,
            'type_id'        => $notification->type_id,
            'created_at'     => strtotime($notification->created_at),
            'data'           => $this->getData($notification->type,$notification->type_id),
            'user'           => $this->usersTransformer->transform($notification->belongsToUser),
            'user_status'    => $user_status ? '0' : '1',
            'tweet_status'   => $tweet_status ? '1' : '0',
        ];

    }

    // 根据不同类型获取数据
    private function getData($type,$type_id)
    {
        $data = null;
        switch($type){
            // 0别人发的动态 被@ 或 1自己发的动态 被点赞
            case 0:
            case 1:
                $tweet = Tweet::with('hasOneContent')->find($type_id);
                $data = $this->tweetsAtTransformer->transform($tweet);
                break;
            // 2自己发的动态 被评论 或 3任何动态，在评论中被@ 或 4自己发的评论被回复
            case 2:
            case 3:
            case 4:
                $reply = TweetReply::find($type_id);
                $tweet = Tweet::with('hasOneContent')->find($reply->tweet_id);
                $content = $reply -> content;
//                $data = $this->tweetRepliesTransformer->transform($reply);
                $data = $this->tweetsAtTransformer->transform($tweet);
                $data['content'] = $reply -> content;
                break;
            // 新收奖杯
            case 6:
                $trophy = TweetTrophyLog::find($type_id);
                $data = $this->tweetsTrophyLog->transform($trophy);
                break;
            default :
                break;
        }
        return $data;
    }
}