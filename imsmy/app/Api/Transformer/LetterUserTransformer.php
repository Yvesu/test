<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/22 0022
 * Time: 下午 14:24
 */

namespace App\Api\Transformer;


use App\Models\PrivateLetter;
use App\Models\User;

class LetterUserTransformer extends Transformer
{
    private $usersTransformer;
    private $channelTweetsTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        ChannelTweetsTransformer $channelTweetsTransformer
    )
    {
        $this -> usersTransformer = $usersTransformer;
        $this -> channelTweetsTransformer = $channelTweetsTransformer;
    }

    public  function transform($letter)
    {

        if ($letter->is_tweet === 0){

            $user = \Auth::guard('api')->user();
            $count = 0;
            if ($user->id === $letter->from){
                $user_info = User::find($letter->to);
            }elseif ($user->id === $letter->to){
                $user_info = User::find($letter->from);
            }else{
                $user_info = User::find($letter->from);
            }
            $count = PrivateLetter::where('to',$user->id)->where('from',$user_info->id)->where('read_to','0')->count();

            return [
                'id'            =>  $letter -> id,
                'from'          =>  $letter -> from,
                'to'            =>  $letter -> to,
                'user_type'     =>  $letter -> user_type,
                'content'       =>  $letter -> content,
                'is_tweet'      =>  $letter->is_tweet,
                'created_at'    =>  strtotime($letter -> created_at),
                'user'          =>  $this -> usersTransformer->transform($user_info),
                'unread'        =>  $count,
            ];
        }else{

            $tweets_data = Tweet::where('type', 0)
                ->where('visible', 0)
                ->with(['belongsToManyChannel' => function ($q) {
                    $q->select(['name']);
                }, 'hasOneContent' => function ($q) {
                    $q->select(['content', 'tweet_id']);
                }, 'belongsToUser' => function ($q) {
                    $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                },'hasOnePhone' =>function($q){
                    $q->select(['id','phone_type','phone_os','camera_type']);
                }])
                ->find($letter->is_tweet);

            switch ($tweets_data->active){
                case 3 :
                    $str =  (object)'该条动态已经被主人删除了!';
                    break;
                case 5 :
                    $str = (object)'管理员已经禁止观看!';
                    break;
                case 6 :
                    $str =  (object)'该动态还在审核中,请您耐心等候...';
                    break;
            }

            return [
                'id'            =>  $letter -> id,
                'content'       =>  $letter -> content,
                'is_tweet'      => $letter->is_tweet,
                'tweet'         =>  in_array($tweets_data->active,[3,5,6],TRUE) ? $str :  $this->channelTweetsTransformer->transform( $tweets_data ),
                'from'          =>  $letter -> from,
                'to'            =>  $letter -> to,
                'created_at'    =>  strtotime($letter -> created_at),//scalar
                'user'          =>  $this -> usersTransformer->transform($letter->belongsToUser)
            ];
        }
    }
}