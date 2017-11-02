<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * 封装基类 20170201，目前只是部分Model使用
 * Class Common
 * @package App\Models
 */
class Common extends Model
{

    // sql 拼接
    protected $query;

    protected $q;

    /**
     * 查看审批通过动态
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * 正常未删除的
     * @param $query
     * @return mixed
     */
    public function scopeOfNormal($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 查询非屏蔽或正常
     * @param $query
     * @return mixed
     */
    public function scopeAble($query)
    {
        return $query->where('active', 0)->orWhere('active', 1);
    }

    /**
     * 按id 刷新与加载
     * @param $query
     * @param $style 1为刷新 2为加载
     * @param $last_id
     * @return mixed
     */
    public function scopeOfNearbyDate($query, $style, $last_id)
    {
        if($last_id){

            // 刷新，大于某一id
            if($style == 1) return $query->where('id', '>', $last_id);

            // 加载，小于某一id
            return $query->where('id', '<', $last_id);
        }else{

            return $query;
        }
    }

    /**
     * 按id 非第一次请求
     * @param $query
     * @param $last_id
     * @return mixed
     */
    public function scopeOfSecond($query, $last_id)
    {
        if($last_id){

            // 加载，小于某一id
            return $query->where('id', '<', $last_id);
        }else{

            return $query;
        }
    }

    /**
     * 查询用户是否在登录状态下，是否下载过其中的一些资源文件
     *
     * @param $query
     * @param $user 获取用户信息
     * @return mixed
     */
    public function scopeOfHasDownload($query, $user)
    {
        if(!$user) return $query;

        return $query -> with(['hasManyDownload' => function($q) use ($user) {
            $q -> where('user_id', $user -> id) -> select('id','file_id');
        }]);
    }

    /**
     * 查询用户是否下载过该文件
     *
     * @param $query
     * @param int $user_id 用户id
     * @return mixed
     */
    public function scopeOfDownloadLog($query, $user_id)
    {
        return $query -> with(['hasManyDownload' => function($q) use ($user_id) {
            $q -> where('user_id', $user_id) -> select('id','file_id','time_update');
        }]);
    }

    /**
     * with多表联查 查询时的查询字段
     * @param $query
     * @param string $relation 关联模型
     * @param array $columns    一维数组，要查询关联模型的字段
     * @return mixed
     */
//    public function scopeWithOnly($query,$relation,Array $columns)
//    {
//        return $query -> with([$relation=>function($query)use($columns){
//            $query -> select(array_merge(['id'],$columns));
//        }]);
//    }

    /**
     * where多条件查询，二维数组
     * @param $query
     * @param array $columns eg:[[id,2],[id,'>',3]]
     * @return mixed
     */
//    public function scopeWhereOnly($query,Array $columns)
//    {
//        return $this -> queryInit($query) -> whereInit($columns);
//    }

    /**
     * whereHas() 的封装
     * @param $query
     * @param $relation 关联的模型
     * @param array $columns  二维数组 关联模型内的条件
     * @return mixed
     */
//    public function scopeWhereHasOnly($query,$relation,Array $columns)
//    {
//        return $this -> queryInit($query) -> whereHas($relation,function($q)use($columns){
//
//            if(empty($columns)) return $q;
//
//            foreach($columns as $key=>$value) {
//                isset($value[2]) ? $q -> where($value[0],$value[1],$value[2]) : $q -> where($value[0],$value[1]);
//            }
//
//            return $q;
//        });
//    }

    /**
     * where()->orderBy()->first() TODO 目前没内容时会报500错误，需修复
     * @param $query
     * @param array $where  条件，二维数组，[[id,2],[id,'>',3]]
     * @param array $orderBy    排序，一维或二维数组  ['id','DESC']或[['id','DESC'],['sort','ASC']]
     * @return mixed
     */
    final public function scopeSelectFirst($query, Array $where = [], Array $orderBy = [])
    {
        return $this -> queryInit($query) -> whereInit($where) -> orderByInit($orderBy) -> firstInit();
    }

    /**
     * where()->orderBy()->get()
     * @param $query
     * @param array $where  条件，二维数组，[[id,2],[id,'>',3]]
     * @param array $orderBy    排序，一维或二维数组  ['id','DESC']或[['id','DESC'],['sort','ASC']]
     * @param array $fields 查询字段，一维数组
     * @return mixed
     */
    final public function scopeSelectList($query, Array $where = [], Array $orderBy = [], $fields = [])
    {
        return $this -> queryInit($query) -> whereInit($where) -> orderByInit($orderBy) -> getInit($fields);
    }

    /**
     * with()->whereHas()->where()->orderBy()->forPage()->get()
     * @param $query
     * @param array $with       with(),关联查询模型，二维数组
     * @param array $whereHas   whereHas(),关联模型，二维数组
     * @param array $where      where(),查询，二维数组
     * @param array $orderBy    排序，一维或二维数组  ['id','DESC']或[['id','DESC'],['sort','ASC']]
     * @param array $page       forPage(),页码，获取指定页的指定条数，一维数组
     * @param array $fields     get(),要查询的字段，一维数组
     * @return mixed
     */
    final public function scopeSelectListPageByWithAndWhereAndWhereHas($query, Array $with = [], Array $whereHas = [], Array $where = [], Array $orderBy = [], Array $page = [], Array $fields = [])
    {
        return $this -> queryInit($query) -> withInit($with) -> whereHasInit($whereHas) -> whereInit($where) -> orderByInit($orderBy) -> forPageInit($page) -> getInit($fields);
    }

    /**
     * with()->has()->where()->orWhere()->orderBy()->forPage()->get()
     * @param $query
     * @param array $with       with(),关联查询模型，二维数组
     * @param array $has        has(),关联模型，一维数组
     * @param array $where      where(),查询，二维数组
     * @param array $orWhere    orWhere(),查询，二维数组
     * @param array $orderBy    排序，一维或二维数组  ['id','DESC']或[['id','DESC'],['sort','ASC']]
     * @param array $page       forPage(),页码，获取指定页的指定条数，一维数组
     * @param array $fields     get(),要查询的字段，一维数组
     * @return mixed
     */
    final public function scopeSelectListPageByWithAndWhereAndHas($query, Array $with = [], Array $has = [], Array $where = [], Array $orWhere = [], Array $orderBy = [], Array $page = [], Array $fields = [])
    {
        return $this -> queryInit($query) -> withInit($with) -> hasInit($has) -> whereInit($where) -> orWhereInit($orWhere) -> orderByInit($orderBy) -> forPageInit($page) -> getInit($fields);
    }




    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////       封装底层，禁止修改       /////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * $query
     * @param $query
     * @return $this
     */
    final function queryInit($query)
    {
        $this -> query = '';
        $this -> query = $query;

        return $this;
    }

    /**
     * @param array $fields 一维数组，要查询字段
     */
    final function firstInit()
    {
        return $this -> query -> first();
    }

    /**
     * @param array $fields 一维数组，要查询字段
     */
    final function getInit($fields)
    {
        if(empty($fields)) return $this -> query -> get();

        return $this -> query -> get($fields);
    }

    /**
     * orderBy 排序
     * @param array $orderBy 一维或二维数组  ['id','DESC']或[['id','DESC'],['sort','ASC']]
     * @return mixed
     */
    final function orderByInit($orderBy)
    {
        if(empty($orderBy)) return $this;

        foreach($orderBy as $value) {

            if(!is_array($value)){
                $this -> query -> orderBy($orderBy[0], $orderBy[1]);

                return $this;
            }

            $this -> query -> orderBy($value[0], $value[1]);
        }

        return $this ;
    }

    /**
     * 页码
     * @param array $page 一维数组
     * @return mixed
     */
    final function forPageInit($page)
    {
        if(empty($page)) return $this;

        $this -> query -> forPage($page[0],$page[1]);

        return $this;
    }

    /**
     * where 条件遍历 二维数组
     * @param $where
     * @return mixed
     */
    final function whereInit($where)
    {
        if(empty($where)) return $this;

        foreach($where as $value) {
            isset($value[2]) ? $this -> query -> where($value[0],$value[1],$value[2]) : $this -> query -> where($value[0],$value[1]);
        }

        return $this ;
    }

    /**
     * orWhere 条件遍历 二维数组
     * @param $orWhere
     * @return mixed
     */
    final function orWhereInit($orWhere)
    {
        if(empty($orWhere)) return $this;

        foreach($orWhere as $value) {
            isset($value[2]) ? $this -> query -> orWhere($value[0],$value[1],$value[2]) : $this -> query -> orWhere($value[0],$value[1]);
        }

        return $this ;
    }

    /**
     * with() 多表联查 查询时的查询字段
     * @param array $with 二维数组，支持多个模型  [['关联模型',[查询字段--一维数组]],['关联模型',[查询字段--一维数组]]]
     * @return $this
     */
    final function withInit($with)
    {
        if(empty($with)) return $this;

        foreach($with as $value){

            $this -> query -> with([$value[0]=>function($q)use($value) {

                $q->select(array_merge(['id'], $value[1]));
            }]);
        }

        return $this;
    }

    /**
     * whereHas() 的封装
     * @param array $where  二维数组，支持多个模型 [['关联模型',二维条件数组],['关联模型',二维条件数组]]
     * @return $this
     */
    final function whereHasInit($where)
    {
        if(empty($where)) return $this;

        foreach($where as $value){

            $this -> query -> whereHas($value[0],function($q)use($value){

                if(empty($value[1])) return $q;

                foreach($value[1] as $key=>$v) {
                    isset($v[2]) ? $q -> where($v[0],$v[1],$v[2]) : $q -> where($v[0],$v[1]);
                }

                return $q;
            });
        }

        return $this;
    }

    /**
     * has() 的封装
     * @param array $where  一维数组
     * @return $this
     */
    final function hasInit($where)
    {
        if(empty($where)) return $this;

        foreach($where as $value){

            $this -> query -> has($value);
        }

        return $this;
    }
}