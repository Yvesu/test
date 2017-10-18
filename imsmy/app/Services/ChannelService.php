<?php

namespace App\Services;


use App\Models\ChannelModel;


/**
 * 频道 服务类
 * Class ChannelService
 * @package App\Services
 */
class ChannelService
{
    protected $channelModel;


    public function __construct()
    {
        $this->channelModel = new ChannelModel();
    }

    /**
     * 查询列表
     * @param array $where
     * @param array $orderBy
     * @param string $fields
     * @return mixed
     */
    public function selectList($where,$orderBy,$active)
    {
        return $this -> channelModel -> selectList($where,$orderBy,$active);
    }













}