<?php

namespace App\Models;

use App\Models\BaseModel;

class RoleGroupModel extends BaseModel
{
    /**
     *    定义当前模型关联的表名
     *    指定真实表,要不然会在表名后面自动加s去匹配
     */
    protected $table = 'role_group';

    protected $query;

    protected $confFieldValueDesc = ['status'=>[0=>'已注销',1=>'已生效']];

    public $timestamps = false;

    /**
     * 获取menuIds数组  通过 组id数组
     * @param array $groupIds
     * @return array
     */
    public function getMenusByGroupIds(array $groupIds){

        if(!$data =  $this->tableInit()->whereInit([['status',1]])->whereInInit('id', $groupIds)->getInit()){
            return [];
        }

        $menus = [];
        foreach ($data as $v) {
            if($v->r_m_ids) $menus = array_merge($menus,explode(',',$v->r_m_ids));
        }

        return array_unique($menus);

    }
}