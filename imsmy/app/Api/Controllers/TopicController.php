<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/4
 * Time: 10:24
 */

namespace App\Api\Controllers;


use App\Api\Transformer\SearchTopicsTransformer;
use App\Api\Transformer\TopicDetailsTransformer;
use App\Api\Transformer\UsersWithFansTransformer;
use App\Api\Transformer\TopicNewTransformer;
use App\Facades\CloudStorage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Topic;
use App\Models\User;
use App\Models\TopicUser;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Cache;

/**
 * 话题相关接口
 *
 * @Resource("Topics")
 */
class TopicController extends BaseController
{
    // 页码
    protected $paginate = 20;

    private $searchTopicsTransformer;
    private $topicDetailsTransformer;
    private $usersWithFansTransformer;
    private $topicNewTransformer;

    public function __construct(
        SearchTopicsTransformer $searchTopicsTransformer,
        TopicDetailsTransformer $topicDetailsTransformer,
        UsersWithFansTransformer $usersWithFansTransformer,
        TopicNewTransformer $topicNewTransformer
    )
    {
        $this->searchTopicsTransformer = $searchTopicsTransformer;
        $this->topicDetailsTransformer = $topicDetailsTransformer;
        $this->usersWithFansTransformer = $usersWithFansTransformer;
        $this->topicNewTransformer = $topicNewTransformer;
    }

    public function index(Request $request)
    {
        try {
            $type = $request->get('type');
            $result = [];

            if ($type == 'popular') {
                //TODO return popular topics
            } else if ($type == 'search') {
                $result = $this->search($request);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 模糊搜索话题
     *
     * @Get("topics/search/?{name,limit,timestamp}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("name", required=true, description="要搜索的昵称")
     * })
     * @Transaction({
     *     @Response(201,body={{}),
     *     @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function search(Request $request)
    {
        try{
            // 获取所搜名称
            if (!$name = removeXSS($request->get('name')))
                throw new \Exception('bad_request',400);

            // 获取数据集合
            $topics = Topic::ofSearch($name)
                ->able()
                ->orderBy('id','desc')
                ->ofSecond((int)$request -> get('last_id'))
                ->take($this->paginate)
                ->get();

            // 判断是否为第一次请求
            if(!$request -> get('last_id')){

                // 是否有精确完全相等的数据
                $name_topics = Topic::ofName($name)->able()->take($this->paginate)->get();

                // 如果能精确匹配成功数据，将数据添加至总数据集合中
                if ($name_topics !== null) {

                    $name_topics->each(function($name_topic)use($topics){

                        $topics->prepend($name_topic);
                    });
                }
            }

            // 返回数据
            return response()->json([

                // 数据
                'data' => count($topics) ? $this->searchTopicsTransformer->transformCollection($topics->all()) : null,

                // 总数量
                'count' => count($topics),

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link' => count($topics)
                    ? $request->url() .
                    '?name=' . $name .
                    '&last_id='.$topics -> last() -> id
                    : null      // 如果数量为0，则不附带搜索条件
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 话题详情
     * @param $id 话题id
     * @return array
     */
    public function details($id)
    {
        try{

            $topic = Cache::remember('topic_'.$id, 120, function() use($id) {

                return Topic::with(['belongsToCompere' => function($q){
                    $q -> with(['belongsToUser' => function($q){
                        $q -> select('id','nickname');
                    }]) -> select('id','topic_id','user_id');
                }])
                    -> able()
                    -> findOrFail($id);
            });

            // 返回数据
            return response()->json($this->topicNewTransformer->transform($topic));

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'], 404);
        } catch (\Exception $e){

            return response()->json(['error'=>'bad_request'], 403);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function  afterdetails(Request $request)
    {
        try{

            $name = $request->get('name');

            if(!$name){
                return [
                    'status_code'=> 400,
                    'error'=> 'name is empty'
                ];
            }

            $topic = Topic::with(['belongsToCompere' => function($q){
                    $q -> with(['belongsToUser' => function($q){
                        $q -> select('id','nickname');
                    }]) -> select('id','topic_id','user_id');
                }])
                    -> able()
                    ->where('name','=',$name)
                    -> first();


            // 返回数据
            return response()->json($this->topicNewTransformer->transform($topic));

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'], 404);
        } catch (\Exception $e){

            return response()->json(['error'=>'bad_request'], 403);
        }
    }


    /**
     * 话题参与者
     * @param Request $request
     * @return array
     */
    public function participants(Request $request)
    {
        try{
            // 获取话题id
            if(!$topic_id = $request -> get('id')) throw new \Exception('bad_request',403);

            // 查询话题是否为屏蔽话题
            if(Topic::unable()->find($topic_id)) throw new \Exception('bad_request',403);

            // 参与话题的用户详情集合
            $topics = TopicUser::where('topic_id', $topic_id)
                -> status()
                -> orderBy('id', 'desc')
                -> ofSecond((int)$request -> get('last_id'))
                -> take($this->paginate)
                -> get();

            // 如果不存在
            if(!$topics->count()) return response()->json([
                'data'  => [],
                'count' => 0,
                'url'   => ''
            ], 201);

            // 参与话题的用户id 数组
            $user_arr = $topics -> pluck('user_id') -> all();

            // 参与话题的用户id 字符串
            $user_str = implode(',',$user_arr);

            // 获取用户信息
            $users = User::whereIn('id', $user_arr)
                ->orderByRaw(DB::raw("FIELD(id,$user_str)"))
                ->get();

            // 返回数据
            return response()->json([
                'data'  => $this->usersWithFansTransformer->transformCollection($users->all()),
                'count' => $topics->count(),
                'url'   => $request -> url() . '?id='.$topic_id.'&last_id=' . $topics->last()->id
            ],201);

        }catch(\Exception $e){

            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 主页 热门 热门话题
     * @return \Illuminate\Http\JsonResponse
     */
    public function hotTopic()
    {
        try{

            // 查询所有话题 缓存
            $topics = Cache::remember('topic_hot', 120, function() {

                $topics = Topic::able()
                    -> orderBy('work_count', 'DESC')
                    -> take(5)
                    -> get(['id', 'name', 'icon', 'comment'])
                    -> all();

                foreach($topics as $value){
                    $value -> icon = CloudStorage::downloadUrl($value -> icon);
                }

                return $topics;
            });

            return response()->json($topics, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'bad_request'],403);
        } catch(\Exception $e){
            return response()->json(['error' => 'bad_request'],403);
        }
    }

    /**
     * 推荐话题
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommendTopic()
    {
        try{
            // 查询所有话题 缓存
            $topics = Cache::remember('topic_recommend', 120, function() {

                $topics = Topic::recommend()
                    -> orderBy('work_count', 'DESC')
                    -> take(20)
                    -> get(['id', 'name', 'icon', 'comment'])
                    -> all();

                foreach($topics as $value){
                    $value -> icon = CloudStorage::downloadUrl($value -> icon);
                }

                return $topics;
            });

            return response()->json($topics, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'bad_request'],403);
        } catch(\Exception $e){
            return response()->json(['error' => 'bad_request'],403);
        }
    }

    /**
     * 首页 全部话题
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function allTopics(Request $request)
    {
        try{
            // 获取页码
            $page = (int)$request -> get('page',1);

            // 查询所有话题
            $topics = Cache::remember('topic_all_'.$page, 120, function() use($page) {

                $topics = Topic::able()
                    -> orderBy('work_count', 'DESC')
                    -> forPage($page, $this->paginate)
                    -> get(['id', 'name', 'icon', 'users_count', 'like_count', 'forwarding_time'])
                    -> all();

                foreach($topics as $value){
                    $value -> icon = CloudStorage::downloadUrl($value -> icon);
                }

                return $topics;
            });


            return response()->json([
                'data'  => $topics,
                'count' => $this -> paginate
            ],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'bad_request'],403);
        } catch (\Exception $e) {
            return response()->json(['error'=>'bad_request'],403);
        }
    }



}