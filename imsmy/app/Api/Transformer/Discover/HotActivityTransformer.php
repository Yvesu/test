<?php

namespace App\Api\Transformer\Discover;

use App\Api\Transformer\{Transformer,UsersTransformer};
use CloudStorage;

class HotActivityTransformer extends Transformer
{

    protected $usersTransformer;

    public function __construct(UsersTransformer $usersTransformer)
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($data)
    {
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

            // 取前5个
            foreach ($data->hasManyTweets as $key=>$value) {

                if($key<5){

                    $tweets[] = [
                        'id' => $value->id,
                        'screen_shot' => CloudStorage::downloadUrl($value->screen_shot)
                    ];
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
        ];
    }
}