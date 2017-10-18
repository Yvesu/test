<?php

namespace App\Models;

use DB;
use App\Models\BaseModel;

class UserModel extends BaseModel
{

    protected $table = 'user';

    public $timestamps = false;

    public $confFieldValueDesc = [
        'sex'=>[ 0=>'未设置',1=>'男',2=>'女',3=>'保密' ],
        'email_status'=>[ 0=>'未验证',1=>'已验证']
    ];



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

    /**
     * 获取单个 通过邮箱
     * @param $email
     * @return mixed
     */
    public function selectOneByEmail($email)
    {
        return $this->tableInit()->whereInit([['email',$email]])->firstInit();
    }

    /*
     * 生成关联的用户的信息
     * @param Array $data 非user表的数据列表
     * @param string $relationUserField 关联user表的字段名称 多个已逗号分隔
     * @param string $needFields  需要的用户的信息字段
     * @return array
     */
    public function productRelationUser( $data,$relationUserField,$needFields=''){
        if(!$data) return [];


        //获取用户ID数组
        $userIds =[];
        $relationUserField = explode(',',$relationUserField);

        foreach($data as $k=> $v){
            foreach($relationUserField as $rel){
                if(in_array( $v[$rel],$userIds)) continue;

                $userIds[] = $v[$rel];
            }
        }
        //获取用户信息 并Key=id
        $users = $this->selectListByWhereIn('id',$userIds,$needFields);

        if(!$users) return $data;

        foreach($users as $v) $userT[$v['id']] = $v;

        //数据组合用户账户名称
        foreach($data as $k=> $v)
            foreach($relationUserField as $rel)
                $data[$k]['_'.$rel.'_user'] = isset($userT[$v[$rel]]) ? $userT[$v[$rel]] : [];

        return $data;

    }
}