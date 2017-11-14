<?php

namespace App\Api\Transformer;

use Auth;
use CloudStorage;
use App\Models\TopicUser;
use App\Models\User;
use App\Models\UserCollections;
use App\Models\TopicExtension;

/**
 * 第五版新接口，该起什么名呢 20170922
 *
 * Class TopicNewTransformer
 * @package App\Api\Transformer
 */
class TopicNewTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($topic)
    {
        // 判断用户是否为登录状态
        $user_from = Auth::guard('api')->user();

        // 判断自己是否参与了该话题
        $already_join = $user_from ? TopicUser::where('topic_id',$topic->id)->where('user_id',$user_from->id)->whereStatus(1)->first() : 0;

        // 判断登录用户是否已经 收藏 1为话题
        $already_like = $user_from ? UserCollections::ofCollections($user_from->id, $topic->id, 1)->where('status',1)->first() : '';

        return [
            'id'                => $topic->id,
            'name'              => $topic->name,
            'size'              => $topic->size,
            'compere'           => $topic->belongsToCompere ? ($topic->belongsToCompere->belongsToUser->nickname) : '',
            'icon'              => CloudStorage::downloadUrl($topic->icon),
            'comment'           => $topic->comment,
            'created_at'        => strtotime($topic->created_at),
            'already_like'      => $already_like ? $already_like->status : '0',       // 收藏关系
            'forwarding_time'   => $topic->forwarding_times,       // 阅读总量
            'like_count'        => $topic->like_count,            // 点赞总量
            'users_count'       => $topic -> users_count,       // 用户总量
            'already_join'       => $already_join ? 1 : 0,       // 用户总量
        ];
    }
}