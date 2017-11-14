<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * DB 基类 [封装 laravel DB]
 * Class BaseModel
 * @package App\Models
 * @notice 继承该类的方法 静止调用laravel DB::类
 */
class BaseModel extends Model
{
    // 定义当前模型关联的表名
    protected $table;

    //字段值说明
    protected $confFieldValueDesc;//[ 'status'=>[0=>'申请中',1=>'已通过',2=>'未通过',3=>'已注销',], ...]

    //存储 sql结构拼接
    private $query;

    //指定是否模型应该被戳记时间
    public $timestamps = false;




    /*
   * 生成关联 表的信息
   * @param Array $data 非原表的数据列表
   * @param string $relationField 关联表的字段名称 多个已逗号分隔
   * @param string $needFields  需要的关联表的字段
   * @return array
   */
    public function productRelation($data, $relationField, $needFields = '')
    {
        if (!$data) return [];

        //获取用户ID数组
        $ids = [];
        $relationField = explode(',', $relationField);

        foreach ($data as $k => $v) {
            foreach ($relationField as $rel) {
                if (in_array($v[$rel], $ids)) continue;

                $ids[] = $v[$rel];
            }
        }


        //获取用户信息 并Key=id
        $list = $this->selectListByWhereIn('id', $ids, $needFields);

        if (!$list) return $data;

        foreach ($list as $v) $listT[$v['id']] = $v;

        //数据组合信息
        foreach ($data as $k => $v)
            foreach ($relationField as $rel)
                $data[$k]['_' . $rel . '_' . $this->table] = isset($listT[$v[$rel]]) ? $listT[$v[$rel]] : [];

        return $data;

    }

    /**
     * 获取最sql记录
     * @return mixed
     */
    final public function lastSql()
    {
        return DB::getQueryLog();

    }

    /**
     * 添加
     * @param array $array
     * @return int
     */
    final public function add($array)
    {
        if (isset($array['pwd'])) $array['pwd'] = strEncrypt($array['pwd']);

        return $this->tableInit()->insertGetIdInit($array);
    }

    /**
     * 修改
     * @param int $id 主键ID
     * @param array $array 数组
     * @return mixed
     */
    final public function updateById($id, $array)
    {
        if (isset($array['pwd'])) $array['pwd'] = strEncrypt($array['pwd']);
        return $this->tableInit()->whereInit([['id', $id]])->updateInit($array);
    }

    /**
     * 修改 唯一字段值
     * @param string $uniqueField 字段名
     * @param string $uniqueValue 字段值
     * @param array $array 数组
     * @return mixed
     */
    final public function updateByUniqueField($uniqueField, $uniqueValue, $array)
    {
        if (isset($array['pwd'])) $array['pwd'] = strEncrypt($array['pwd']);

        return $this->tableInit()->whereInit([[$uniqueField, $uniqueValue]])->updateInit($array);
    }

    /**
     * 修改多个  条件且
     * @param array $where eg:[['id','=',1],['username','=','j']]
     * @param array $array
     * @return mixed
     */
    final public function updateListWhere(array $where, $array)
    {
        if (isset($array['pwd'])) $array['pwd'] = strEncrypt($array['pwd']);
        return $this->tableInit()->whereInit($where)->updateInit($array);
    }

    /**
     * 修改多个  条件或
     * @param array $where eg:[['id','=',1],['username','=','j']]
     * @param array $array
     * @return mixed
     */
    final public function updateListWhereOr(array $where, $array)
    {
        if (isset($array['pwd'])) $array['pwd'] = strEncrypt($array['pwd']);

        return $this->tableInit()->whereInit([array_shift($where)])->orWhereInit($where)->updateInit($array);
    }

    /**
     * 获取单个 通过ID
     * @param int $id
     * @param string $fields
     * @return mixed
     */
    final public function selectOneById($id, $fields = '')
    {
        return $this->tableInit()->selectInit($fields)->whereInit([['id', $id]])->firstInit();
    }

    /**
     * 查询 唯一字段值
     * @param string $uniqueField 字段名
     * @param string $uniqueValue 字段值
     * @param array $array 数组
     * @return mixed
     */
    final public function selectOneByUniqueField($uniqueField, $uniqueValue, $field = '')
    {
        return $this->tableInit()->whereInit([[$uniqueField, $uniqueValue]])->selectInit($field)->firstInit();
    }

    /**
     * 获取单个 通过Where
     * @param array $where eg:[['id','=',1],['username','=','j']]
     * @param string $fields
     * @return mixed
     */
    final public function selectOneByWhere(array $where, $fields = '')
    {
        return $this->tableInit()->selectInit($fields)->whereInit($where)->firstInit();

    }

    /**
     * 获取单个 条件或
     * @param array $where
     * @param string $fields
     * @return mixed
     */
    final public function selectOneByWhereOr(array $where, $fields = '')
    {
        return $this->tableInit()->selectInit($fields)->whereInit([array_shift($where)])->orWhereInit($where)->firstInit();
    }

    /**
     * 获取单个 通过WhereIn
     * @param string $fieldName 字段名称
     * @param string $fieldValue 字段值
     * @param string $fields 结果字段
     * @return mixed
     */
    final public function selectOneByWhereIn($fieldName, $fieldValue, $fields = '')
    {
        return $this->tableInit()->selectInit($fields)->whereInInit($fieldName, $fieldValue)->firstInit();

    }

    /**
     * 获取多个 通过WhereIn
     * @param string $fieldName 字段名称
     * @param array $fieldValue 字段值
     * @param string $fields 结果字段
     * @return mixed
     */
    final public function selectListByWhereIn($fieldName, $fieldValue, $fields = '')
    {
        return $this->tableInit()->selectInit($fields)->whereInInit($fieldName, $fieldValue)->getInit();
    }


    /**
     * 获取多个 通过Where WhereIn  WhereOr
     * @param array $where
     * @param array $whereIn
     * @param array $whereOr
     * @param array $orderBy
     * @param string $fields
     * @return mixed
     */
    final public function selectListByWhereAndWhereInAndWhereOr($where = [], $whereIn = [], $whereOr = [], $orderBy = [], $fields = '')
    {
        return $this->tableInit()->selectInit($fields)->whereInit($where)->whereInArrInit($whereIn)->orWhereInit($whereOr)->orderByInit($orderBy)->getInit();
    }

    /**
     * 查询列表  限制查询调试
     * @param array $where
     * @param array $whereIn
     * @param array $whereOr
     * @param array $orderBy
     * @param string $fields
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    final public function limitListByWhereAndWhereInAndWhereOr($offset = 0, $limit = 10, $where = [], $whereIn = [], $whereOr = [], $orderBy = [], $fields = '')
    {
        return $this->tableInit()->selectInit($fields)->whereInit($where)->whereInArrInit($whereIn)->orWhereInit($whereOr)->orderByInit($orderBy)
            ->skipInit($offset)->takeInit($limit)->getInit();
    }

    /**
     * 统计满足条件的记录个数
     * @param array $where
     * @param array $whereIn
     * @param array $whereOr
     * @return mixed
     */
    final public function countByWhereAndWhereInAndWhereOr($where = [], $whereIn = [], $whereOr = [])
    {
        return $this->tableInit()->whereInit($where)->whereInArrInit($whereIn)->orWhereInit($whereOr)->countInit();
    }

    /**
     * 获取分页多个 通过Where WhereIn  WhereOr
     * @param array $where
     * @param array $whereIn
     * @param array $whereOr
     * @param array $orderBy
     * @param string $fields
     * @param int $pageSize
     * @return mixed
     */
    final public function selectListPageByWhereAndWhereInAndWhereOr($where = [], $whereIn = [], $whereOr = [], $orderBy = [], $fields = '', $pageSize = 10)
    {
        return $this->tableInit()->selectInit($fields)->whereInit($where)->whereInArrInit($whereIn)->orWhereInit($whereOr)->orderByInit($orderBy)->paginateInit($pageSize);
    }

    /**
     * 查询分页列表
     * @param array $where [['id','=',1],['username','=','j']]
     * @param array $orderBy
     * @param int $paginate
     * @param string $fields
     * @return mixed
     */
    final public function selectListPage(array $where = [], array $orderBy = [], $paginate = 10, $fields = '', $whereIn = [])
    {
        return $this->tableInit()->selectInit($fields)->whereInit($where)->whereInArrInit($whereIn)->orderByInit($orderBy)->paginateInit($paginate);
    }

    /**
     * 查询列表
     * @param array $where
     * @param array $orderBy
     * @param string $fields
     * @return mixed
     */
    final public function selectList(array $where = [], array $orderBy = [], $fields = '')
    {
        return $this->tableInit()->orderByInit($orderBy)->whereInit($where)->selectInit($fields)->getInit();
    }

    /**
     * 字段值说明
     * @param string $filedName
     * @param string $filedValue
     * @return string
     */
    public function confDescField($filedName = '', $filedValue = '')
    {
        $conf = $this->confFieldValueDesc;
        if (!$filedName) return $conf;

        if (!$filedValue) return isset($conf[$filedName]) ? $conf[$filedName] : '';

        return isset($conf[$filedName][$filedValue]) ? $conf[$filedName][$filedValue] : '';
    }

    //===============================================================================================================//
    //===============||         下面是laravel 封装方法,不允许做任何修改 (静止写入 Delete)         ||=================//
    //===============================================================================================================//

    /**
     * paginate
     * @param $paginate
     * @return mixed
     */
    final public function paginateInit($paginate)
    {
        return $this->query->paginate($paginate);

    }


    /**
     * DB::update()
     * @param $array
     * @return mixed
     */
    final public function updateInit($array)
    {
//        return $this->query->update($array);
        // 下面为原装，尚有争议
        return $this->query->update(dbToHtmlSpecialChars($array));
    }

    /**
     * insertGetId
     * @param $array
     * @return mixed
     */
    final public function insertGetIdInit($array)
    {
        return $this->query->insertGetId(dbToHtmlSpecialChars($array));
    }

    /**
     * table
     * @param string $table
     * @return $this
     */
    final public function tableInit($table = '')
    {
        $this->query = '';
        $this->query = DB::table($table ? $table : $this->table);

        return $this;
    }

    /**
     * select(DB::raw()
     * @param string $fields eg: 1,2,3
     * @return $this
     */
    final function selectInit($fields)
    {
        if (!$fields) return $this;

        $this->query->select(DB::raw($fields));

        return $this;
    }

    /**
     * where
     * @param array $where eg:[[id,=,2],[id,'>',3]]
     * @return $this
     */
    final function whereInit($where)
    {
        if (!$where) return $this;

        foreach ($where as $v) {
            isset($v[2]) ? $this->query->where($v[0], $v[1], $v[2]) : $this->query->where($v[0], $v[1]);
        }

        return $this;
    }

    /**
     * orWhere
     * @param array $where eg:[[id,=,2],[id,'>',3]]
     * @return $this
     */
    final function orWhereInit($where)
    {
        if (!$where) return $this;

        foreach ($where as $v) {
            isset($v[2]) ? $this->query->orWhere($v[0], $v[1], $v[2]) : $this->query->orWhere($v[0], $v[1]);
        }

        return $this;
    }

    /**
     * 获取分页多个 通过Where WhereIn  WhereOr Fun
     * @param array $where
     * @param array $whereIn
     * @param array $whereOr
     * @param array $orderBy
     * @param string $fields
     * @param int $pageSize
     * @return mixed
     */
    final public function selectListPageByWhereAndWhereInAndWhereOrFun($where = [], $whereIn = [], $whereOr = [], $orderBy = [], $fields = '', $pageSize = 10)
    {
        return $this->tableInit()->selectInit($fields)->whereInit($where)->whereInArrInit($whereIn)->orWhereFunInit($whereOr)->orderByInit($orderBy)->paginateInit($pageSize);
    }

    /**
     * orWhereFun
     *   DB::table('users')
     * //            ->where('name', '=', 'John')
     * //            ->orWhere(function ($query) {
     * //                $query->where('votes', '>', 100)
     * //                    ->where('title', '<>', 'Admin');
     * //            })
     * @param $where
     * @return $this
     */
    final function orWhereFunInit($where)
    {
        if (!$where) return $this;
        $this->query->orWhere(function ($query) use ($where) {
            foreach ($where as $v) {
                isset($v[2]) ? $query->where($v[0], $v[1], $v[2]) : $query->where($v[0], $v[1]);
            }
        });
        return $this;
    }

    /**
     * whereIn
     * @param string $fieldName eg:id
     * @param array $fieldValue eg:[1,2,3]
     * @return $this
     */
    final function whereInInit($fieldName, $fieldValue)
    {
        $this->query->whereIn($fieldName, $fieldValue);

        return $this;
    }


    /**
     * whereIn Arr
     * @param $whereInArr eg:[ [id,[1,2,3]],[uid,[4,5,6] ]
     * @return $this
     */
    final function whereInArrInit($whereInArr)
    {
        if (!$whereInArr) return $this;

        foreach ($whereInArr as $v) $this->query->whereIn($v[0], $v[1]);

        return $this;
    }

    /**
     * orderBy
     * @param array $orderBy eg:[id,desc]
     * @return $this
     */
    final function orderByInit($orderBy)
    {

        if (!$orderBy) return $this;

        foreach ($orderBy as $k => $v) {
            if (!is_array($v)) {
                $this->query->orderBy($orderBy[0], $orderBy[1]);
                return $this;
            }
            $this->query->orderBy($v[0], $v[1]);
        }

        return $this;
    }

    /**
     * get
     * @return mixed
     */
    final function getInit()
    {
        return $this->query->get();
    }

    /**
     * first
     * @return mixed
     */
    final function firstInit()
    {
        return $this->query->first();
    }

    /**
     * increment
     * @param array $array eg:[[fileName1,1],[fileName2,2]] 或 eg:[[fileName1],[fileName2]]
     * @return $this
     */
    final function incrementInit(array $array)
    {
        foreach($array as $k=>$v){
            if(!is_array($v)){
                isset($array[1]) ?  $this->query->increment($array[0],$array[1]) : $this->query->increment($array[0]);
                return $this;
            }

            isset($v[1]) ?  $this->query->increment($v[0],$v[1]) : $this->query->increment($v[0]);

        }
        return $this;
    }

    /**
     * decrement
     * @param array $array eg:[[fileName1,1],[fileName2,2]] 或 eg:[[fileName1],[fileName2]]
     * @return $this
     */
    final function decrementInit(array $array)
    {
        foreach($array as $k=>$v){
            if(!is_array($v)){
                isset($array[1]) ?  $this->query->decrement($array[0],$array[1]) : $this->query->decrement($array[0]);
                return $this;
            }

            isset($v[1]) ?  $this->query->decrement($v[0],$v[1]) : $this->query->decrement($v[0]);

        }
        return $this;
    }



    /**
     * 指定要查询个数
     * @return mixed
     */
    final function takeInit($num)
    {
        $this->query->take($num);
        return $this;
    }

    /**
     * 指定偏移量
     * @return mixed
     */
    final function skipInit($num)
    {
        $this->query->skip($num);
        return $this;
    }


    final function countInit()
    {
        return $this->query->count();
    }

    /**
     * beginTransaction
     */
    final function beginTransactionInit()
    {
        DB::beginTransaction();
    }

    /**
     * rollBack
     */
    final function rollBackInit()
    {
        DB::rollBack();
    }

    /**
     * rollBack
     */
    final function commitInit()
    {
        DB::commit();
    }


}