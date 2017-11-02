<?php

namespace App\Models;

use App\Models\BaseModel;
use DB;

/**
 * 权限菜单 数据类
 * Class RoleMenuModel
 * @package App\Models
 */
class RoleMenuModel extends BaseModel
{
    protected $table = 'role_menu';

    protected $query;
    public $timestamps = false;


    /**
     * 获取列表 公共权限组ids数组
     * @param array $groupIds
     * @return mixed
     */
    public function getMenusByGroupIds(array $groupIds){
        return $this->tableInit()->whereInInit('id', $groupIds)->getInit();
    }


    /**
     * 获取孩子
     * @param array $ids
     * @param array $where
     * @return mixed
     */
    public function selectListInByPid(array $ids,$where=[]){
        return $this->tableInit()->whereInit($where)->whereInInit('pid',$ids)->getInit();
    }
}