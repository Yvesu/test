<?php

namespace App\Services;

use App\Models\RoleMenuModel;


/**
 * 权限菜单 服务类
 * Class RoleMenuService
 * @package App\Services
 */
class RoleMenuService
{
    protected $model;
    public function __construct(){
        $this->model = new RoleMenuModel();

    }

    public function selectOneByWhere($where){
        return $this->model->selectOneByWhere($where);
    }
    /**
     * 获取菜单IDS
     * @param $groupIdsArr
     * @return array
     */
    public function getMenuIdsByGroupIds( array $groupIdsArr)
    {
        if (!$groupIdsArr) {
            return [];
        }
        if (!$groups = $this->model->getMenusByGroupIds($groupIdsArr)) {
            return [];
        }

        $data = [];
        foreach ($groups as $v)
            if( $v['r_m_ids']) $data = array_merge($data,explode(',',$v['r_m_ids']));

        return array_unique($data);
    }

    /**
     *获取一级菜单
     */
    public function getListLevelFirst()
    {
        return $this->model->selectList([['pid',0],['status',1],['show_nav',1]]);
    }

    /**
     *获取二级菜单
     */
    public function getListLevelSecond($ids)
    {
        return $this->model->selectListInByPid($ids,[['status',1],['show_nav',1]]);
    }


    /**
     * 查询列表
     * @param array $where
     * @param array $orderBy
     * @param string $field
     * @return mixed
     */
    public function selectList(array $where = [], array $orderBy = [], $field=''){

        $orderBy = $orderBy ? $orderBy : [['status','DESC'],['path_pid', 'ASC']];
        $field = $field ? $field :'*, CONCAT(path,id) as path_pid, CONCAT(id," ",name) as id_name';

        $data = $this->model->selectList($where,$orderBy,$field);
        if($data){
            foreach ($data as $k => $v) {
                $idName = str_repeat('|--', substr_count($v['path'], ',') - 1) . '';
                $data[$k]['name'] = $idName . $data[$k]['name'];
            }
        }

        return $data;
    }

    /**
     * 插入记录
     * @param $name 菜单名称
     * @param $intro 菜单介绍
     * @param $route 菜单路由
     * @param $pid 菜单父级
     * @param $showNav 导航是否展示
     * @param $classIcon 徽章
     * @return int
     */
    public function add($name,$intro,$route,$pid,$showNav,$classIcon){

        //唯一性判断
        if($had = $this->model->selectOneByWhere([ ['name','=',$name]])){
            return '名称已经存在';
        }

        if($route && $had = $this->model->selectOneByWhere([ ['route','=',$route]])){
            return '路由已经存在';
        }

        //设置路径
        $path = '0,';
        if($pid){
            if(!$one = $this->model->selectOneById($pid)){
                return 0;
            }
            $path = $one->path.$pid.',';
        }

        //组装数据
        $arr = [
            'name'=>$name,'intro'=>$intro,'route'=>$route,
            'pid'=>$pid,'path'=>$path,'show_nav'=>$showNav,'class_icon'=>$classIcon,
            'time_add'=>getTime(),'time_update'=>getTime(),
        ];

        return !!$this->model->add($arr);

    }
    /**
     * 菜单伪删除
     * @param $id
     * @return int|mixed
     */
    public function delete($id){
        if(!$one = $this->model->selectOneById($id)){
            return 0;
        }

        if( 0 == $one -> status ) return 0;

        return $this->model->updateListWhereOr(
            [
                ['id','=',$id],
                ['path','like',$one->path.$one->id.',%'],
            ],
            ['status'=>0,'time_update'=>getTime()]
        );
    }


    /**
     * 菜单更新
     * @param $id
     * @return int|mixed
     */
    public function updateById($id,$name,$array){
        if(!$one = $this->model->selectOneById($id)){
            return 0;
        }

        if($one = $this->model->selectOneByWhere([
            ['id','!=',$id],
            ['name','=',$name],
        ])){
            return 0;
        }

        //设置路径
        $array['path'] = '0,';
        $pid = $array['pid'];
        if($array['pid']){
            if(!$one = $this->model->selectOneById($pid)){
                return 0;
            }
            $array['path'] = $one->path.$pid.',';
        }

        return $this->model->updateById($id,$array);

    }

    /**
     * 获取管理后台  导航栏数据  TODO 已改成Eloquent方式
     * @return mixed|void
     */
    public function getNav(){

        $first = RoleMenuModel::where('pid',0)
            -> where('status',1)
            -> where('show_nav',1)
            -> get(['id','name','intro','route','class_icon','path'])
            -> toArray();

        if (!$first) {
            session(['menu' => []]);
            return;
        }

        $ids = [];
        foreach ($first as $v) {
            $ids[] = $v['id'];
        }

        $second = RoleMenuModel::whereIn('pid',$ids)
            -> where('status',1)
            -> where('show_nav',1)
            -> get(['id','name','intro','route','class_icon','path','pid'])
            -> toArray();;

        foreach ($first as $k => $v) {
            $first[$k]['_children'] = [];
            foreach ($second as $vv) {

                if ($vv['pid'] == $v['id']) $first[$k]['_children'][] = $vv;
            }
        }

        foreach($first as $k => $v){
            if(!$v['_children']) unset($first[$k]);
        }

        return $first;

    }



}