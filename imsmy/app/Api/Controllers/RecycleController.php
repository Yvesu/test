<?php

namespace App\Api\Controllers;


use App\Api\Transformer\TweetsTransformer;
use App\Api\Transformer\SubTransformer;
use App\Api\Transformer\UsersWithSubIndexTransformer;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Topic;
use Illuminate\Http\Request;
use Auth;
use DB;
use Carbon\Carbon;
/**
 * 此为回收站，回收砍掉功能的代码备份
 *
 */
class RecycleController extends BaseController
{
    protected $tweetsTransformer;

    protected $usersWithSubTransformer;

    protected $usersWithIndexSubTransformer;

    public function __construct(
        TweetsTransformer $tweetsTransformer,
        SubTransformer $usersWithSubTransformer,
        UsersWithSubIndexTransformer $usersWithIndexSubTransformer
    )
    {
        $this->tweetsTransformer = $tweetsTransformer;
        $this->usersWithSubTransformer = $usersWithSubTransformer;

        $this->usersWithIndexSubTransformer = $usersWithIndexSubTransformer;
    }



    /** 首页关注页面 顶部关注用户  及话题（项目中已砍掉）
     * @param $id   自己id
     * @return array
     */
    public function attention($id)
    {
        try {
            // 获取条数，暂时固定
            $limit = 20;

            // 数据集合，统计总数使用
            $subscriptions_all = Subscription::where('from', $id);

            // 获取关注的集合
            $subscriptions = $subscriptions_all -> orderBy('updated_at', 'desc') -> take($limit) -> get();

            // 判断所取数据数量
            if($subscriptions -> count() <= 0) return ['data' => []];

            // 获取id，并拼接字符串
            $arr = $subscriptions->pluck('unread','to')->all();
            $str = implode(',', array_keys($arr));
            $arr_type = $subscriptions->pluck('type','to')->all();

            // 根据id获取用户信息
            $users = User::whereIn('id', array_keys($arr))
                ->orderByRaw(DB::raw("FIELD(id,$str)"))
                ->get();

            // 将未查看的动态信息存入集合
            foreach($users as $key=>$value){

                $users[$key]['unread'] = $arr[$value->id];
                $users[$key]['type'] = $arr_type[$value->id];
            }

            return response()->json([
                'data'         => $subscriptions->count() ? $this->usersWithIndexSubTransformer->transformCollection($users->sortByDesc('unread')->values()->all()) : [],
                'users_count'  => $subscriptions_all->count(),
            ],201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 添加关注话题  停用（项目中已砍掉）
     *
     * @Post("/{sub_id}")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"id":"该订阅ID"}),
     *     @Response(404,body={"error":"topic_not_found"}),
     * })
     */
    public function add($id,$sub_id)
    {
        try {

            // 将关注双方存入数组中
            $newSubscription = [
                'from' => $id,
                'to'   => $sub_id,
                'type' => 1
            ];

            // 监测话题表中是否存在要关注的用话题id，如果没有则报错，如果有则继续下一步
            $topic = Topic::findOrFail($newSubscription['to']);

            // 检测是否已关注对方，防止恶意一些行为
            $num = Subscription::where('from', $newSubscription['from'])
                ->where('to', $newSubscription['to'])
                ->where('type',1)
                ->first();

            // 如果已存在，返回已存在的错误信息
            if ($num) return response()->json(['error' => 'already_exists'], 403);

            // 开启事务
            DB::beginTransaction();

            // 将信息存入subscription表中
            $subscription = Subscription::create($newSubscription);

            // 将 topic 表中的相应总关注量+1
            $topic -> sub_count ++;

            $topic -> save();

            // 提交事务
            DB::commit();

            return response()->json(['id' => $subscription -> id],201);

        // 异常处理
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 取消关注话题  停用（项目中已砍掉）
     *
     * @Post("/{sub_id}")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"id":"该订阅ID"}),
     *     @Response(404,body={"error":"topic_not_found"}),
     * })
     */
    public function delete($id,$sub_id)
    {
        try {

            // 获取关注的集合
            $subscription = Subscription::where('from', $id)
                ->where('to', $sub_id)
                ->where('type',1)
                ->first();

            // 监测话题表中是否存在要关注的用话题id，如果没有则报错，如果有则继续下一步
            $topic = Topic::where('id',$sub_id)->first();

            // 如果不存在，返回错误信息
            if (!$subscription) return response()->json(['error' => 'not_found'], 404);

            // 开启事务
            DB::beginTransaction();

            // 将 topic 表中的相应总关注量-1
            $topic -> sub_count --;

            // 保存
            $topic -> save();

            // 删除集合
            $subscription -> delete();

            // 提交事务
            DB::commit();

            return response()->json('',204);

            // 异常处理
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }


}