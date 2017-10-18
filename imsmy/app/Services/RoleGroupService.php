<?php

namespace App\Services;

use App\Models\RoleGroupModel;
use App\Models\RoleMenuModel;
use App\Models\AdminModel;

/**
 * 后台权限组
 * Class RoleGroupService
 * @package App\Services
 */
class RoleGroupService
{
    protected $model;
    protected $modelAdmin;
    protected $modelMenu;

    public function __construct()
    {
        $this->model = new RoleGroupModel();
        $this->modelAdmin = new AdminModel();
        $this->modelMenu = new RoleMenuModel();

    }

    /**
     * 状态描述
     * @return string
     */
    public function getStatusDesc()
    {
        return $this->model->confDescField('status');
    }

    /**
     * 获取单个权限组  通过ID
     * @param $id
     * @return mixed
     */
    public function selectOneById($id)
    {
        return $this->model->selectOneById($id);
    }

    /**
     * 添加
     * @param $name 权限名
     * @param $intro 权限描述
     * @param $status 状态
     * @param $items 权限菜单ids 多个已逗号分隔
     * @param $uidAdmin 添加的管理员id
     * @return bool|string
     */
    public function add($name, $intro, $status, $items, $uidAdmin)
    {
        if ($one = $this->model->selectOneByWhere([['name', $name]])) {
            return '权限名称已经存在';
        }

        $arr = [
            'name' => $name,
            'intro' => $intro,
            'status' => $status,
            'admin_id' => $uidAdmin,
            'time_add' => getTime(),
            'time_update' => getTime(),
            'r_m_ids' => $items
        ];

        return !!$this->model->add($arr);

    }

    /**
     * 更新
     * @param $name 权限名
     * @param $intro 权限描述
     * @param $status 状态
     * @param $items 权限菜单ids 多个已逗号分隔
     * @param $auditAdminId 添加的管理员id
     * @return bool|string
     */
    public function update($id, $name, $intro, $status, $items, $auditAdminId)
    {

        if (!$one = $this->model->selectOneByWhere([['id', $id]])) {
            return '权限不存在';
        }

        if ($one = $this->model->selectOneByWhere([['name', $name], ['id', '!=', $id]])) {
            return '权限名称已经存在';
        }

        $arr = [
            'name' => $name,
            'intro' => $intro,
            'status' => $status,
            'audit_admin_id' => $auditAdminId,
            'time_add' => getTime(),
            'time_update' => getTime(),
            'r_m_ids' => $items
        ];

        return !!$this->model->updateById($id, $arr);

    }


    /**
     * 获取menuIds数组  通过 组id数组
     * @param array $groupIds
     * @return array
     */
    public function getMenusByGroupIds(array $groupIds)
    {
        //历史权限组  对应的menuID
        $menuIds = $this->model->getMenusByGroupIds($groupIds);
        if(!$menuIds) return $menuIds;

        //获取生效的权限菜单
        $activeMenuIds = $this->modelMenu->selectListByWhereAndWhereInAndWhereOr([['status',1]],[['id',$menuIds]]);
        if(!$activeMenuIds) return [];

        //获取生效的 菜单ID数组
        $menuT = [];
        foreach ($activeMenuIds as $v) {
            $menuT[]=$v->id;
        }

        return $menuT;

    }


    /**
     * 权限组列表
     * @param array $where
     * @param array $orderBy
     * @param int $paginate
     * @param int $searchUser eg:[fieldName=>[username,1=like,2=>aaa],fieldName=>[username,1=like,2=>aaa]]
     * @return array|mixed
     */
    public function getListPage(array $where = [], array $orderBy = [], $paginate = 10, $searchUser = [])
    {

        $adminModel = new AdminModel();
        $whereIn = [];
        if ($searchUser) {
            foreach ($searchUser as $k => $v) {
                if (!$admins = $adminModel->selectList([$v])) {
                    $whereIn[] = [$k, []];
                    continue;
                }

                foreach ($admins as $admin) $adminIds[$k][] = $admin['id'];

                $whereIn[] = [$k, $adminIds[$k]];
            }
        }

        $data = $this->model->selectListPage($where, $orderBy, $paginate, '', $whereIn);

        $data->_setItems($adminModel->productRelationAdmin($data->items(), 'admin_id', 'id,email'));

        return $data;
    }

    /**
     * 权限伪删除
     * @param $id
     * @param $auditAdminId
     * @return int|mixed
     */
    public function delete($id,$auditAdminId){
        if(!$one = $this->model->selectOneById($id)){
            return '记录不存在';
        }

        if(0==$one->status) return '状态错误';


        return !!$this->model->updateListWhereOr(
            [
                ['id','=',$id],
                ['path','like',$one->path.$one->id.',%'],
            ],
            ['status'=>0,'time_update'=>getTime()]
        );
    }

    /**
     * 获取多个 通过WhereIn
     * @param string $fieldName 字段名称
     * @param array $fieldValue 字段值 数组
     * @param string $fields 结果字段
     * @return mixed
     */
    final public function selectListByWhereIn($fieldName, $fieldValue, $fields = '')
    {
        return $this->model->selectListByWhereIn($fieldName, $fieldValue, $fields);
    }

}