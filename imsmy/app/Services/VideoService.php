<?php

namespace App\Services;


use App\Models\VideoModel;


/**
 * 视频video 服务类
 * Class VideoService
 * @package App\Services
 */
class VideoService
{
    protected $videoModel;


    public function __construct()
    {
        $this->videoModel = new VideoModel();
    }

    /**
     * 通过type查询动态tweet
     * @param int $type video所属频道 channel_id
     * @param int $num 每次获取条目数
     * @return mixed
     */
    public function selectListByChannel($active,$channel_id,$num)
    {
        return $this -> videoModel -> selectListPage([['active', $active], ['channel_id', $channel_id]], [['created_at', 'desc']], $num);
    }

    /**
     * 修改状态
     * @param int $id 主键ID
     * @param array $array 数组
     * @return mixed
     */
    final public function updateById($id, $array)
    {
        return $this->videoModel->updateById($id, $array);
    }











}