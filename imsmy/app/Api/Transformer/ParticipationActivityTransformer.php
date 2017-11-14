<?php

namespace App\Api\Transformer;

use App\Models\TweetActivity;
use CloudStorage;
use Auth;

class ParticipationActivityTransformer extends Transformer
{

    protected $usersTransformer;

    public function __construct(UsersTransformer $usersTransformer)
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($data)
    {
        $user = Auth::guard('api')->user();

        // 初始化数组
        $tweets = array();

        // 获取参与动态信息
        if($len = $data -> hasManyTweets -> count()) {
            
            // 冒泡
            for($k=1;$k<$len;$k++)
            {
                for($j=0;$j<$len-$k;$j++){
                    if($data->hasManyTweets[$j]->pivot['like_count']<$data->hasManyTweets[$j+1]->pivot['like_count']){
                        $temp =$data->hasManyTweets[$j+1];
                        $data->hasManyTweets[$j+1] =$data->hasManyTweets[$j] ;
                        $data->hasManyTweets[$j] = $temp;
                    }
                }
            }

            $rank = 0;
            $allocation = 0;

            // 取前5个
            foreach ($data->hasManyTweets as $key=>$value) {

                if($key<5){

                    $tweets[] = [
                        'id' => $value->id,
                        'screen_shot' => CloudStorage::downloadUrl($value->screen_shot)
                    ];
                }

                // 自己的名次和奖金
                if($user->id == $value->user_id){

                    $rank = ++$key;

                    switch($rank){
                        case 1:
                            $allocation = $data->bonus * 0.5;
                            break;
                        case 2:
                            $allocation = $data->bonus * 0.2;
                            break;
                        case 3:
                            $allocation = $data->bonus * 0.1;
                            break;
                        default :
                            $count = TweetActivity::where('activity_id',$data->id)->count();
                            $allocation = number_format($data->bonus * 0.2 / $count,2,'.','');
                    }
                }
            }
        }

        return [
            'id'                => $data->id,
            'bonus'             => $data->bonus,
            'comment'           => $data->comment,
            'expires'           => $data->expires,
            'created_at'        => $data->time_add,
            'icon'              => CloudStorage::downloadUrl($data->icon),
            'work_count'        => $data->work_count,
            'user'              => $this->usersTransformer->transform($data->belongsToUser),
            'tweets'            => $tweets,    // 取前五个
            'ranking'           => $rank,    // 名次
            'allocation'        => $allocation,    // 应得奖金
        ];
    }
}