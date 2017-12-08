<?php

namespace App\Models;

use App\Models\BaseModel;
use DB;

/**
 * 后台视频 数据类
 * Class VideoModel
 * @package App\Models
 */
class ChannelModel extends BaseModel
{
    protected $table = 'channel';

    protected $confFieldValueDesc =  [
        'active'=>[
            0=>'停用',
            1=>'正常',
        ]
    ];

//    public $timestamps = false;


    /**
     * 获取指定id数据
     * @param array $ids
     * @return mixed
     */
    public function selectListInByIds(array $ids){
        return $this->tableInit()->whereInInit('id',$ids)->getInit();
    }

}