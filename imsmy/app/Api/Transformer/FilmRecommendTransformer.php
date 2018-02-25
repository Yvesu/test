<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/11 0011
 * Time: 下午 19:47
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class FilmRecommendTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public  function transform($item)
    {
        // 获取参加赛事的前9个人的头像
        $avatars = [];

        if($item -> hasManyTweets -> first()){

            foreach($item -> hasManyTweets -> take(9) as $key => $value){

                $avatars[] = CloudStorage::downloadUrl($value -> belongsToUser -> avatar);
            }
        }

        return [
            'id'            =>   $item->id,
            'name'          =>   $item->filmfest->name,
            'comment'       =>   $item->filmfest->des,
            'cover'         =>   CloudStorage::downloadUrl( $item->filmfest->cover),
            'url'           =>   $item->filmfest->url,
            'users_count'   =>   $item->users_count,
            'expires'       =>   $item->filmfest->time_end,
//            'bonus'         =>   $item->filmfest->cost,
            'user'          => $this->usersTransformer->transform($item->belongsToUser),
            'avatars'       => $avatars,
            'type'          =>   'film',
        ];
    }

}