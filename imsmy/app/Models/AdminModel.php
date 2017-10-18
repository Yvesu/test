<?php

namespace App\Models;

use App\Models\BaseModel;
use DB;

class AdminModel extends BaseModel
{

    protected $table = 'administrator_b';

    public $timestamps = false;


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

    /*
   * 生成关联的管理员的信息
   * @param Array $data 非admin表的数据列表
   * @param string $relationAdminField 关联admin表的字段名称 多个已逗号分隔
   * @param string $needFields  需要的用户的信息字段
   * @return array
   */
    public function productRelationAdmin( $data,$relationAdminField,$needFields=''){
        if(!$data) return [];


        //获取用户ID数组
        $adminIds =[];
        $relationAdminField = explode(',',$relationAdminField);

        foreach($data as $k=> $v){
            foreach($relationAdminField as $rel){
                if(in_array( $v[$rel],$adminIds)) continue;

                $adminIds[] = $v[$rel];
            }
        }

        //获取管理员信息 并Key=id
        $admins = $this->selectListByWhereIn('id',$adminIds,$needFields);

        if(!$admins) return $data;

        foreach($admins as $v) $adminT[$v['id']] = $v;

        //数据组合用户账户名称
        foreach($data as $k=> $v)
            foreach($relationAdminField as $rel)
                $data[$k]['_'.$rel.'_admin'] = isset($adminT[$v[$rel]]) ? $adminT[$v[$rel]] : [];

        return $data;

    }




}