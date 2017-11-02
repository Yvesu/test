<?php

namespace App\Services;

use App\Models\Topic;
use App\Models\TopicModel;

class TopicService
{
    protected $topic;

    public function __construct(){

        $this -> topicModel = new TopicModel();
    }

    /**
     * 查询分页列表
     * @param array $where [0=>[fieldName,how,fieldVal],1=>[fieldName,how,fieldVal], ....]
     * @param array $orderBy [0=>fieldName, 1=>how]
     * @param int $paginate 每页显示的条数
     * @return mixed
     */
    public function selectTopicListPage(array $where = [], array $orderBy = [], $paginate = 10, $fields = '', $whereIn = [])
    {
        return $this->topicModel->selectListPage($where, $orderBy, $paginate,$fields);
    }


}

