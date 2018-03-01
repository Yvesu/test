<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ChannelTweetsTransformer;
use App\Api\Transformer\Discover\HotActivityTransformer;
use App\Models\Activity;
use App\Models\Tweet;
use App\Models\UserCollections;
use App\Models\Topic;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Api\Transformer\TweetsSearchTransformer;
use App\Api\Transformer\TopicCollectionsTransformer;

/** 收藏相关接口
 * Class CollectionsController
 * @package App\Http\Controllers
 */
class CollectionsController extends BaseController
{
    protected $paginate = 20;
    protected $tweetsSearchTransformer;
    protected $topicCollectionsTransformer;
    protected $channelTweetsTransformer;
    protected $hotActivityTransformer;

    public function __construct(
        TweetsSearchTransformer $tweetsSearchTransformer,
        TopicCollectionsTransformer $topicCollectionsTransformer,
        ChannelTweetsTransformer $channelTweetsTransformer,
        HotActivityTransformer $hotActivityTransformer
    )
    {
        $this->tweetsSearchTransformer = $tweetsSearchTransformer;
        $this->topicCollectionsTransformer = $topicCollectionsTransformer;
        $this->channelTweetsTransformer = $channelTweetsTransformer;
        $this->hotActivityTransformer = $hotActivityTransformer;
    }

    /**
     * 收藏详情
     * @param $id   用户id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($id,Request $request)
    {
        try {
            $page = $request->get('page',1);

            // 话题集合
            $topic_data = Topic::whereHas('belongsToCollection',function($query)use($id){
                $query -> where('user_id',$id) -> status();
            })  -> able()
                -> forPage((int)$request->get('page'),$this->paginate)
                -> get();

            //动态集合
            $tweet_ids = UserCollections::where('user_id',$id)
                ->where('type',3)
                ->where('status',1)
                ->forPage($page,20)
                ->pluck('type_id');

            $tweets_data = Tweet::where('type', 0)
                ->where('active', 1)
                ->where('visible', 0)
                ->with(['belongsToManyChannel' => function ($q) {
                    $q->select(['name']);
                }, 'hasOneContent' => function ($q) {
                    $q->select(['content', 'tweet_id']);
                }, 'belongsToUser' => function ($q) {
                    $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                },'hasOnePhone' =>function($q){
                    $q->select(['id','phone_type','phone_os','camera_type']);
                }])
                ->orderBy('created_at', 'DESC')
                ->whereIn('id',$tweet_ids->all())
                ->forPage($page, $this->paginate)
                ->get();

            //收藏的赛事
            $activity_ids = UserCollections::where('user_id',$id)
                ->where('type',2)
                ->where('status',1)
                ->forPage($page,20)
                ->pluck('type_id');

            $activity_data = Activity::with(['belongsToUser' => function($q){
                $q -> select('id','nickname','avatar','cover','verify','signature','verify_info');
            }, 'hasManyTweets'=>function($q){
                $q->select(['tweet_id','screen_shot']);
            }])
                ->whereIn('id',$activity_ids->all())
                -> paginate($this->paginate, ['id','user_id','bonus','comment','expires','time_add','icon','work_count'], 'page', $page);


            // 返回合并后的数据
            return response()->json([
                'topic'     => $this->topicCollectionsTransformer->transformCollection($topic_data->all()),
                'activity'  => $this -> hotActivityTransformer->transformCollection($activity_data->all()),
                'tweet'     => $this->channelTweetsTransformer->transformCollection($tweets_data->all()),
            ],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /** 添加收藏
     * @param $id   用户id
     * @param $type_id  数据所在类型的id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create($id,$type_id,Request $request)
    {
        try{
            // 获取要收藏的数据 所属的类型话题1
            $type = (int)$request -> get('type', 1);

            $time = getTime();

            // 判断该用户是否已经收藏，
            $collection = UserCollections::where('user_id',$id)
                ->where('type_id',$type_id)
                ->where('type',$type)
                ->first();

            // 如果已经收藏，则直接返回收藏的提醒
            if($collection && $collection->status == 1)
                return response() -> json(['error'=>'already_exists'],401);

            // 验证所要收藏的数据是否存在
            switch($type){
                case 1:
                    $data = Topic::findOrFail((int)$type_id);
                    break;
                case 2:         //赛事
                    $data = Activity::where('id',(int)$type_id) -> first();
                    break;
                case 3 :        //动态
                    $data = Tweet::find((int)$type_id);
                    break;
                default :
                    $data = '';
            }

            // 如果数据不存在，则返回错误信息
            if(!$data) return response()->json(['error' => 'not_found'], 404);

            // 如果之前已经收藏，直接修改状态
            if($collection) {

                // 未删除的收藏，修改状态
                if($collection -> status === 0) {

                    // 修改集合
                    $collection -> status = 1;
                    $collection -> time_update = $time;

                    // 保存集合
                    $collection -> save();

                    // 返回收藏数据id
                    return response()->json(['id' => $collection->id],201);
                }
            }

            // 存储用户的收藏信息
            $collection_data = [
                'user_id'       => $id,
                'type_id'       => $type_id,
                'type'          => $type,
                'time_add'      => $time,
                'time_update'   => $time,
            ];

            // 存储
            $userCollections = UserCollections::create($collection_data);

            // 返回新生成的收藏数据id
            return response()->json([
                'id' => $userCollections->id

                // 201(已创建)请求成功并且服务器创建了新的资源
            ],201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /** 取消收藏
     * @param $id   用户id
     * @param $type_id  数据所在类型的id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id,$type_id,Request $request)
    {
        try{
            // 获取要收藏的数据 所属的类型 话题1 活动2
            $type = $request -> get('type');

            // 判断类型的规范性
            if(!in_array($type,[1,2,3])) return response() -> json(['error'=>'bad_request'],403);

            // 判断该用户是否收藏
            $collection = UserCollections::where('user_id',$id)
                ->where('type_id',$type_id)
                ->where('type',$type)
                ->where('status',1)
                ->first();

            // 如果不存在，则直接返回未收藏的提醒
            if(!$collection) return response() -> json(['error'=>'not_found'],404);

            // 修改集合数据
            $collection -> status = 0;
            $collection -> time_update = getTime();

            // 保存
            $collection -> save();

            // 返回已取消的成功提醒
            return response()->json(['status' => 'ok'],201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

}

