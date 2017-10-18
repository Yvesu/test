<?php

namespace App\Services;

use App\Models\AdminModel;

class AdminService
{
    protected $model;

    public function __construct(){

        $this->model = new AdminModel();

    }

    /**
     * 管理员添加
     * @param $username 用户名
     * @param $pwd 密码
     * @param $realName 真实姓名
     * @param $phone 电话
     * @param $adminId 添加者ID
     * @return null
     */
    public  function add($username,$pwd,$realName,$phone,$adminId){

        return $this->model->add([
            'username'=>$username,
            'realname'=>$realName,
            'pwd'=>$pwd,
            'phone'=>$phone,
            'admin_id'=>$adminId
        ]);
    }

    /**
     *  信息修改
     * @param $id 主键id
     * @param $array 修改信息
     * @return null
     */
    public  function adminUpdatePwd($id,$array){

        // 将新密码信息传至M处理
        return $this->model->updateById($id,$array);

    }

    /**
     * 通过用户名和密码检测登录是否成功
     * @param $username
     * @param $pwd
     * @return mixed|null  成功返回 记录,失败返回 null
     */
    public function checkLogin($username, $pwd)
    {

        if(!$one = $this->model->selectOneByUsername($username))
            return null;

        if(strEncrypt($pwd) != $one['pwd'])
            return null;

        return $one;

    }

    /**
     * 通过id和密码检测登录是否成功
     * @param $username
     * @param $pwd
     * @return mixed|null  成功返回 记录,失败返回 null
     */
    public function checkLoginById($id, $pwd)
    {
        if(!$one = $this->model->selectOneById($id))
            return null;

        if(strEncrypt($pwd) != $one['pwd'])
            return null;

        return $one;

    }

    /**
     * 获取单个记录 通过ID
     * @param $id
     * @return mixed
     */
    public function selectOneById($id){
        return $this->model->selectOneById($id);
    }

    /**
     * 添加管理员信息
     * @param $array
     * @return mixed
     */
    public function addAdmin(array $array)
    {
        return $this->model->add($array);
    }

    /**
     * 查询分页列表
     * @param array $where [0=>[fieldName,how,fieldVal],1=>[fieldName,how,fieldVal], ....]
     * @param array $orderBy [0=>fieldName, 1=>how]
     * @param int $paginate 每页显示的条数
     * @return mixed
     */

    public function selectListPage(array $where = [], array $orderBy = [], $paginate = 10)
    {
        return $this->model->selectListPage($where, $orderBy, $paginate);
    }

    /**
     * 通过id删除管理员信息 修改状态
     * @param $id 管理员id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->updateByUniqueField('id', $id, ['status' => '2']);
    }

    /**
     * 通过id启用管理员
     * @param $id 管理员id
     * @return mixed
     */
    public function enable($id)
    {
        // 启用
        return $this->model->updateByUniqueField('id', $id, ['status' => 0]);
    }

    /**
     * 通过id查询信息
     * @param $id 管理员id
     * @return mixed
     */
    public function selectInfoById($id)
    {
        return $this->model->selectOneById($id);
    }

    /**
     * 通过id更新管理员信息
     * @param $id 管理员id
     * @param $array 管理员信息
     * @return mixed
     */
    public function updateAdmin($id,$array)
    {
        return $this->model->updateById($id,$array);
    }

}