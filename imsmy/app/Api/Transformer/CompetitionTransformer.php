<?php
namespace App\Api\Transformer;

use Auth;
use CloudStorage;
use App\Models\{UserCollections,TweetActivity};

class CompetitionTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($data)
    {
        // 判断用户是否为登录状态
        $user_from = Auth::guard('api')->user();

        // 判断登录用户是否已经 收藏 1为话题,2为赛事
        $already_like = $user_from ? UserCollections::ofCollections($user_from->id, $data->id, 2)->first() : '';

        // 判断用户是否参与了该赛事
        $already_join = $user_from ? TweetActivity::where('activity_id',$data->id)->where('user_id',$user_from->id)->first() : 0;

        return [
            'id'                => $data->id,
            'name'              => $data->name,
            'bonus'             => $data->bonus,
            'expires'           => $data->expires,
            'icon'              => CloudStorage::downloadUrl($data->icon),
            'created_at'        => $data->time_add,
            'already_like'      => $already_like ? $already_like->status : '0',       // 收藏关系
            'forwarding_time'   => $data->forwarding_time,       // 阅读总量
            'like_count'        => $data->like_count,            // 点赞总量
            'work_count'        => $data->work_count,            // 作品总量
            'users_count'       => $data -> users_count,       // 用户总量
            'user'              => $this->usersTransformer->transform($data->belongsToUser),
            'official'          => $data->official,     // 是否为官方发布，0为不是，1为是
            'theme'             => $data->theme,
            'comment'           => $data->comment,
            'already_join'      => $already_join ? 1 : 0,
            'location'          =>  $data->location,
        ];
    }
}