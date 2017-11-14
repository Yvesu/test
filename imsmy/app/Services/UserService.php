<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\LocalAuthModel;
use App\Models\OauthModel;


class UserService
{
    protected $model;
    protected $localAuth;
    protected $oauth;
    protected $user;


    // 初始化
    public function __construct()
    {
        // user表
        $this->model = new UserModel();

        // local_auth表
        $this -> localAuth = new LocalAuthModel();

        // oauth表
        $this -> oauth = new OauthModel();

        // user表
        $this -> user = new OauthModel();
    }

    /**
     * 查询本地用户分页列表
     * @param array $where [0=>[fieldName,how,fieldVal],1=>[fieldName,how,fieldVal], ....]
     * @param array $orderBy [0=>fieldName, 1=>how]
     * @param int $paginate 每页显示的条数
     * @return mixed
     */

    public function selectListPage(array $where = [], array $orderBy = [], $paginate = 10)
    {
        return $this->localAuth->selectListPage($where, $orderBy, $paginate);
    }

    /**
     * 查询第三方用户分页列表
     * @param array $where [0=>[fieldName,how,fieldVal],1=>[fieldName,how,fieldVal], ....]
     * @param array $orderBy [0=>fieldName, 1=>how]
     * @param int $paginate 每页显示的条数
     * @return mixed
     */
    public function selectOauthListPage(array $where = [], array $orderBy = [], $paginate = 10)
    {
        return $this->oauth->selectListPage($where, $orderBy, $paginate);
    }

    /**
     * 获取单个 通过ID
     * @param int $id
     * @param string $fields
     * @return mixed
     */
    final public function selectOneById($id, $fields = '')
    {
        return $this->model->selectOneById($id, $fields);
    }

    /**
     * 获取单个用户信息 通过ID
     * @param int $id
     * @param string $fields
     * @return mixed
     */
    final public function selectOneByUserId($id, $fields = '')
    {
        return $this->user->selectOneById($id, $fields);
    }

    /**
     * 核实 通过local_auth表唯一字段核实用户是否存在,返回基本信息或空
     * @param $field 字段名
     * @param $userAccount 对应字段值
     * @return mixed
     */
    public function checkOneByUniqueField($field,$userAccount)
    {
        return $this->localAuth->selectOneByUniqueField($field,$userAccount);
    }

    /**
     * 通过id启用local_auth表用户
     * @param $id 用户id
     * @return mixed
     */
    public function localEnable($id)
    {
        // 启用
        return $this->localAuth->updateByUniqueField('id', $id, ['status' => 0]);
    }

    /**
     * 通过id启用oauth表用户
     * @param $id 用户id
     * @return mixed
     */
    public function oauthEnable($id)
    {
        // 启用
        return $this->oauth->updateByUniqueField('id', $id, ['status' => 0]);
    }

    /**
     * 通过id删除local_auth表用户信息 修改状态
     * @param $id 用户id
     * @return mixed
     */
    public function localDelete($id)
    {
        return $this->localAuth->updateByUniqueField('user_id', $id, ['status' => '1']);
    }

    /**
     * 通过id删除oauth表用户信息 修改状态
     * @param $id 用户id
     * @return mixed
     */
    public function oauthDelete($id)
    {
        return $this->oauth->updateByUniqueField('user_id', $id, ['status' => '1']);
    }


}