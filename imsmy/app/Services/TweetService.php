<?php

namespace App\Services;


use App\Models\TweetModel;
use App\Models\LabelModel;
use App\Models\UserModel;
use App\Models\TweetLikeModel;
use App\Models\TweetReplyModel;


/**
 * 频道 服务类
 * Class TweetService
 * @package App\Services
 */
class TweetService
{
    protected $tweetModel;
    protected $label;


    public function __construct()
    {
        $this->tweetModel = new TweetModel();
        $this->label = new LabelModel();
        $this->user = new UserModel();
        $this->tweet_like = new TweetLikeModel();
        $this->tweet_reply = new TweetReplyModel();
    }

    /**
     * 查询列表
     * @param array $where
     * @param array $orderBy
     * @param string $fields
     * @return mixed
     */
    public function selectList($where,$orderBy,$fields)
    {
        return $this -> tweetModel -> selectList($where,$orderBy,$fields);
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
        return $this->tweetModel->selectListPage($where, $orderBy, $paginate);
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
    final public function selectListPageByWhere($where = [], $whereIn = [], $whereOr = [], $orderBy = [], $fields = '', $pageSize = 10)
    {
        return $this->tweetModel-> selectListPageByWhereAndWhereInAndWhereOr($where,$whereIn,$whereOr,$orderBy,$fields,$pageSize);
    }

    /**
     * 获取单个标签信息 通过ID
     * @param int $id
     * @param string $fields
     * @return mixed
     */
    final public function selectOneByLabelId($id, $fields = '')
    {
        return $this->label->selectOneById($id, $fields);
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
     * 查询列表 tweet_like表
     * @param array $where
     * @param array $orderBy
     * @param string $fields
     * @return mixed
     */
    public function selectTweetLikeList($where,$orderBy,$fields)
    {
        return $this -> tweet_like -> selectList($where,$orderBy,$fields);
    }

    /**
     * 查询列表 tweet_reply表
     * @param array $where
     * @param array $orderBy
     * @param string $fields
     * @return mixed
     */
    public function selectTweetReplyList($where,$orderBy,$fields)
    {
        return $this -> tweet_reply -> selectList($where,$orderBy,$fields);
    }















}