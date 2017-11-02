<?php

namespace App\Models;

use DB;

class OauthModel extends BaseModel
{

    protected $table = 'oauth';

//    public $timestamps = false;

    /**
     * 获取单个 通过单字段
     * @param $field
     * @param $value
     * @return mixed
     */
    public function selectOneByField($field,$value)
    {
        return $this->tableInit()->whereInit([[$field,$value]])->firstInit();
    }

    /**
     * 获取单个 通过账户
     * @param $username
     * @return mixed
     */
    public function selectOneByUsername($username)
    {
        return $this->tableInit()->whereInit([['username',$username]])->firstInit();
    }

    /**
     * 获取单个 通过电话
     * @param $phone
     * @return mixed
     */
    public function selectOneByPhone($phone)
    {
        return $this->tableInit()->whereInit([['phone',$phone]])->firstInit();
    }


}