<?php

namespace App\Api\Transformer;

use Auth;
use CloudStorage;
use App\Models\TopicUser;
use App\Models\User;
use App\Models\UserCollections;
use App\Models\TopicExtension;

class TopicDetailsTransformer extends Transformer
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

        // 获取某个话题参加的人数
        $topic_users = TopicUser::where('topic_id',$topic->id)->take(5)->get()->pluck('user_id');

        // 判断自己是否参与了该话题
        $already_join = $user_from ? TopicUser::where('topic_id',$topic->id)->where('user_id',$user_from->id)->whereStatus(1)->first() : 0;

        // 获取参加话题的前五个人的头像
        $avatars = $topic_users ? CloudStorage::downloadUrl(User::whereIn('id',$topic_users) -> pluck('avatar') -> all()) : (object)null;

        // 判断是否有官方发布的视频,如果是，查询 topic_extension 表
        $video = $topic->official === 1 ? TopicExtension::where('topic_id',$topic->id) -> first() : '';

        // 判断登录用户是否已经 收藏 1为话题
        $already_like = $user_from ? UserCollections::ofCollections($user_from->id, $topic->id, 1)->first() : '';

        return [
            'id'                => $topic->id,
            'style'             => 2,
            'name'              => $topic->name,
            'color'             => $topic->color,
            'size'              => $topic->size,
            'compere'           => $topic->belongsToCompere ? ($topic->belongsToCompere->belongsToUser->nickname) : '',
            'icon'              => CloudStorage::downloadUrl($topic->icon),
            'photo'             => $video ? ($video->photo ? CloudStorage::downloadUrl($video->photo) : '') : '',
            'screen_shot'       => $video ? ($video->screen_shot ? CloudStorage::downloadUrl($video->screen_shot) : '') : '',
            'video'             => $video ? ($video->video ? CloudStorage::downloadUrl($video->video) : '') : '',
            'comment'           => $topic->comment,
            'created_at'        => strtotime($topic->created_at),
            'avatars'           => $avatars,
            'already_like'      => $already_like ? $already_like->status : '0',       // 收藏关系
            'forwarding_time'   => $topic->forwarding_times,       // 阅读总量
            'like_count'        => $topic->like_count,            // 点赞总量
            'users_count'       => $topic -> users_count,       // 用户总量
            'already_join'       => $already_join ? 1 : 0,       // 用户总量
        ];
    }
}