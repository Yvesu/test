<?php

namespace App\Models;

use App\Models\BaseModel;
use DB;

/**
 * 动态 数据类
 * Class VideoModel
 * @package App\Models
 */
class TweetLikeModel extends BaseModel
{
    protected $table = 'tweet_like';


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