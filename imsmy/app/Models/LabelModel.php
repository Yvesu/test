<?php

namespace App\Models;

use App\Models\BaseModel;
use DB;

/**
 * 动态 数据类
 * Class LabelModel
 * @package App\Models
 */
class LabelModel extends BaseModel
{
    protected $table = 'label';

    protected $confFieldValueDesc =  [
        'active'=>[
            0=>'正常',
            1=>'屏蔽'
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