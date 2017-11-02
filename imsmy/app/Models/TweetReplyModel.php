<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * 动态 数据类
 * Class TweetReplyModel
 * @package App\Models
 */
class TweetReplyModel extends BaseModel
{
    protected $table = 'tweet_reply';


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