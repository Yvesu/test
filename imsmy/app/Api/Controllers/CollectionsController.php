<?php

namespace App\Api\Controllers;

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

    public function __construct(
        TweetsSearchTransformer $tweetsSearchTransformer,
        TopicCollectionsTransformer $topicCollectionsTransformer
    )
    {
        $this->tweetsSearchTransformer = $tweetsSearchTransformer;
        $this->topicCollectionsTransformer = $topicCollectionsTransformer;
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

            // 话题集合
            $topic_data = Topic::whereHas('belongsToCollection',function($query)use($id){
                $query -> where('user_id',$id) -> status();
            })  -> able()
                -> forPage((int)$request->get('page'),$this->paginate)
                -> get();

            // 返回合并后的数据
            return response()->json([
                'data' => $this->topicCollectionsTransformer->transformCollection($topic_data->all()),
                'count'=> $this->paginate
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
                    $data = Topic::findOrFail($type_id);
                    break;
//                case 2:
//                    $data = Activity::where('id',$type_id) -> first();
//                    break;
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
            if(!in_array($type,[1,2])) return response() -> json(['error'=>'bad_request'],403);

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

