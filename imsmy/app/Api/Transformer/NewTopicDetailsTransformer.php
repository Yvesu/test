<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/30 0030
 * Time: 下午 14:49
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;
use App\Models\TopicExtension;
use App\Models\TopicUser;
use App\Models\UserCollect;
use App\Models\UserCollections;
use Auth;

class NewTopicDetailsTransformer extends Transformer
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

        // 判断是否有官方发布的视频,如果是，查询 topic_extension 表
        $video = $topic->official === 1 ? TopicExtension::where('topic_id',$topic->id) -> first() : '';

        // 判断登录用户是否已经 收藏 1为话题
        $already_like = $user_from ? UserCollections::ofCollections($user_from->id, $topic->id, 1)->first() : '';

        //累计关注量
        $attention_count = UserCollections::where('type',1)->where('type_id',$topic->id)->count();

        return [
            'id'                => $topic->id,
            'name'              => $topic->name,
            'color'             => $topic->color,
            'size'              => $topic->size,
            'compere'           => $topic->hasOneCompere ? ($this->usersTransformer->transform($topic->hasOneCompere)) : '',
            'icon'              => CloudStorage::downloadUrl($topic->icon),
            'photo'             => $video ? ($video->photo ? CloudStorage::downloadUrl($video->photo) : '') : '',
            'screen_shot'       => $video ? ($video->screen_shot ? CloudStorage::downloadUrl($video->screen_shot) : '') : '',
            'video'             => $video ? ($video->video ? CloudStorage::downloadUrl($video->video) : '') : '',
            'comment'           => $topic->comment,
            'created_at'        => strtotime($topic->created_at),
            'already_like'      => $already_like ? $already_like->status : '0',             // 收藏关系
            'work_count'        => $topic -> work_count,
            'forwarding_time'   => $topic->forwarding_time,                                // 阅读总量
            'like_count'        => $topic->like_count,                                      // 点赞总量
            'attention_count'   => $attention_count,                                        // 关注量
            'already_join'      => $already_join ? 1 : 0,                                   // 是否已经参与
        ];

    }
}