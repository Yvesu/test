<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/22 0022
 * Time: 下午 15:55
 */

namespace App\Api\Transformer;


use App\Models\PrivateLetter;

class LettersDetailsTransformer extends Transformer
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
//        PrivateLetter::find($letter->id)->update(['type'=>1]);

        $user = \Auth::guard('api')->user();

        if ($user->id ===  $letter -> from){
            $read_user_id = $letter -> to;
        }elseif($user->id ===  $letter -> to){
            $read_user_id =  $letter -> from;
        }

        PrivateLetter::where('to',$user->id)->where('from',$read_user_id)->update(['read_to'=>'1']);
        return [
            'id'            =>  $letter -> id,
            'content'       =>  $letter -> content,
            'from'          =>  $letter -> from,
            'to'            =>  $letter -> to,
            'is_tweet'      =>  $letter->is_tweet,
            'created_at'    =>  strtotime($letter -> created_at),
            'user'          =>  $this -> usersTransformer->transform($letter->belongsToUser),
        ];
    }
}