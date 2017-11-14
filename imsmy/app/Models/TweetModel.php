<?php

namespace App\Models;

use App\Models\BaseModel;
use DB;

/**
 * 动态 数据类
 * Class VideoModel
 * @package App\Models
 */
class TweetModel extends BaseModel
{
    protected $table = 'tweet';

    protected $confFieldValueDesc =  [
        'active'=>[
            0=>'未审批',
            1=>'正常',
            2=>'屏蔽'
        ],
        'type'=>[
            0 => 'video' ,
            1 => 'photo',
            2=>'text'
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