<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/22
 * Time: 11:07
 */

namespace App\Api\Controllers;


use App\Api\Transformer\TweetsTransformer;
use App\Api\Transformer\SubTransformer;
use App\Api\Transformer\UsersWithSubIndexTransformer;
use App\Models\Blacklist;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Friend;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Auth;
use DB;
use Carbon\Carbon;
/**
 * 订阅相关接口
 *
 * @Resource("Subscriptions",uri="users/{id}/subscriptions")
 */
class SubscriptionController extends BaseController
{
    protected $tweetsTransformer;

    protected $subTransformer;

    protected $usersWithIndexSubTransformer;

    public function __construct(
        TweetsTransformer $tweetsTransformer,
        SubTransformer $subTransformer,
        UsersWithSubIndexTransformer $usersWithIndexSubTransformer
    )
    {
        $this->tweetsTransformer = $tweetsTransformer;
        $this->subTransformer = $subTransformer;

        $this->usersWithIndexSubTransformer = $usersWithIndexSubTransformer;
    }


    /** 为下面两个接口取数据服务
     * @param $id   自己id
     * @param Request $request
     * @param $type  类型，follower为粉丝，following为关注
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function index($id, Request $request, $type)
    {
        try {
            // 获取用户登录信息
            $user_self = Auth::guard('api')->user();

            // 如果非请求自己信息，返回错误。后期可能会允许看别人的粉丝数据，再打开就OK了
           // if($user_self->id != $id) return response()->json(['error' => 'bad_request'],403);

            // 获取时间戳和条数
//            $limit = $request->get('limit');
            $limit = 20;
            $timestamp = $request->get('timestamp');

            // 判断与转换格式
//            $limit = isset($limit) && is_numeric($limit) ? $limit : 20;
            $timestamp = isset($timestamp) && is_numeric($timestamp) ? $timestamp : time();

            $date = Carbon::createFromTimestamp($timestamp)->toDateTimeString();

            // 判断是获取关注还是粉丝数据
            if ($type === 'follower') {
                $subscriptions_all = Subscription::where('to', $id);

                // 获取关注的集合
                $subscriptions = $subscriptions_all -> where('updated_at', '<', $date) -> orderBy('updated_at', 'desc') -> take($limit) -> get();

                // 判断所取数据数量
                if($subscriptions -> count() <= 0) return ['data' => []];

                // 获取id，并拼接字符串
                $arr = $subscriptions->pluck('unread','from')->all();
                $str = implode(',', array_keys($arr));

                // 获取关注对象类型的数组
                $arr_type = $subscriptions->pluck('type','from')->all();

                // 根据id获取用户信息
                $users = User::whereIn('id', array_keys($arr))
                    ->orderByRaw(DB::raw("FIELD(id,$str)"))
                    ->get();

                // 将未查看的动态信息存入集合
                foreach($users as $key=>$value){

                    // 未读动态数量
                    $users[$key]['unread'] = $arr[$value->id];

                    // 关注用户的类型，0为用户，1为话题
                    $users[$key]['type'] = $arr_type[$value->id];
                }

                // 统计所取数据的数量
                $count = $subscriptions->count();
                return [
                    'data'       => $count ? $this->subTransformer->transformCollection($users->sortByDesc('unread')->values()->all()) : [],
                    'data_count'      => $count,
                    'users_count'  => $subscriptions_all->count(),
                    'link'       => $count
                        ? $request->url() .
                        '?channel=subscription&limit=' . $limit .
                        '&timestamp=' . strtotime($subscriptions->last()->created_at)
                        : null
                ];

            } else {
                $subscriptions_all = Subscription::where('from', $id);
            }

            // 获取关注的集合
            $subscriptions = $subscriptions_all -> where('updated_at', '<', $date) -> orderBy('updated_at', 'desc') -> take($limit) -> get();

            // 判断所取数据数量
            if($subscriptions -> count() <= 0) return ['data' => []];

            // 获取id，并拼接字符串
            $arr = $subscriptions->pluck('unread','to')->all();
            $str = implode(',', array_keys($arr));

            // 获取关注对象类型的数组
            $arr_type = $subscriptions->pluck('type','to')->all();

            // 根据id获取用户信息
            $users = User::whereIn('id', array_keys($arr))
                ->orderByRaw(DB::raw("FIELD(id,$str)"))
                ->get();

            // 获取登录用户数据
//            $user = Auth::guard('api')->user();

            // 将未查看的动态信息存入集合
            foreach($users as $key=>$value){

                // 未读动态数量
                $users[$key]['unread'] = $arr[$value->id];

                // 关注用户的类型，0为用户，1为话题
                $users[$key]['type'] = $arr_type[$value->id];
            }

            // 判断双方的关注关系
//            $users->load([
//                'hasManySubscriptions' => function ($q) use ($user) {
//                    $q->where('from', $user->id);
//                },
//                'hasManySubscriptionsFrom' => function ($q) use ($user) {
//                    $q->where('to', $user->id);
//                }
//            ]);

            // 统计所取数据的数量
            $count = $subscriptions->count();
            return [
                'data'       => $count ? $this->subTransformer->transformCollection($users->sortByDesc('unread')->values()->all()) : [],
                'data_count'      => $count,
                'users_count'  => $subscriptions_all->count(),
                'link'       => $count
                    ? $request->url() .
                    '?channel=subscription&limit=' . $limit .
                    '&timestamp=' . strtotime($subscriptions->last()->created_at)
                    : null
            ];
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 获取某个用户的粉丝
     *
     * @Get("/follower?{limit,timestamp}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="用户的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("limit",type="integer",description="返回数量",default="20"),
     *     @Parameter("timestamp",type="integer",description="从该时间戳时间前的订阅",default="当前时间戳")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={"data":{{
     *                                  "id" : 100048,
     *                                  "nickname" : "nickname",
     *                                  "avatar" : null,
     *                                  "hash_avatar" : null,
     *                                  "created_at" : 1464228516,
     *                                  "follower"  : "true || false 不带引号 代表 该用户与当前请求用户的关系",
     *                                  "following" : "true || false 不带引号 代表 该用户与当前请求用户的关系"
     *                                }},
     *                         "timestamp":1464228576,
     *                         "count":20,
     *                         "link":"http://goobird.dev/api/users/10000/subscriptions?limit=20&timestamp=1464227376",
     *     }),
     *     @Response(401,body={"error":"unauthorized"}),
     * })
     */
    public function follower($id, Request $request)
    {
        return $this->index($id, $request, 'follower');
//         return response()->json($this->index($id, $request, 'follower'));
    }

    /**
     * 获取某个用户的关注
     * follower following 代表 该用户与当前请求用户的关系
     * @Get("/following?{limit,timestamp}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true,description="用户的ID即登录环信的用户名(为URL中的id)"),
     *     @Parameter("limit",type="integer",description="返回数量",default="20"),
     *     @Parameter("timestamp",type="integer",description="从该时间戳时间前的订阅",default="当前时间戳")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={"data":{{
     *                                  "id" : 100048,
     *                                  "nickname" : "nickname",
     *                                  "avatar" : null,
     *                                  "hash_avatar" : null,
     *                                  "created_at" : 1464228516,
     *                                  "follower"  : "true || false 不带引号 代表 该用户与当前请求用户的关系",
     *                                  "following" : "true || false 不带引号 代表 该用户与当前请求用户的关系"
     *                                }},
     *                         "timestamp":1464228576,
     *                         "count":20,
     *                         "link":"http://goobird.dev/api/users/10000/subscriptions?limit=20&timestamp=1464227376",
     *     }),
     *     @Response(401,body={"error":"unauthorized"}),
     * })
     */
    public function following($id, Request $request)
    {
        return $this->index($id, $request, 'following');
//        return response()->json($this->index($id, $request, 'following'));
    }

    /** 首页关注页面 顶部关注用户
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
     * 添加关注用户
     *
     * @Post("/{sub_id}")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"id":"该订阅ID","mutual":"添加订阅完成后，两人是否相互订阅"}),
     *     @Response(404,body={"error":"user_not_found"}),
     * })
     */
    public function create($id,$sub_id)
    {
        try{

            // 判断是否为自己关注自己
            if($id == $sub_id) return response()->json(['error'=>'cannot_subscribe_to_self'],401);

            // 要添加的关注关系
            $newSubscription = [
                'from' => $id,
                'to'   => $sub_id
            ];

            // 监测用户表中是否存在要关注的用户id，如果没有则报错，如果有则继续下一步
            $user_to = User::findOrFail($newSubscription['to']);

            // 检测是否已关注对方
            $num = Subscription::ofAttention($newSubscription['from'],$newSubscription['to']) -> first();

            // 如果已存在，返回已存在的id及错误信息
            if($num) {
                return response()->json(
                    [
                        'id'     => $num->id,
                        'error' => 'already_exists'
                    ],403);
            }

            // 判断是否在黑名单内
            if(Blacklist::ofBlackIds($id,$sub_id)->first()){

                // 在自己的黑名单中
                return response()->json(['error'=>'in_own_black_list'],431);
            }elseif(Blacklist::ofBlackIds($sub_id,$id)->first()){

                // 在对方的黑名单中
                return response()->json(['error'=>'in_his_black_list'],432);
            }

            // 以开启事务模式
            DB::beginTransaction();

            // 将信息存入subscription表中
            $subscription = Subscription::create($newSubscription);

            // 判断是否开启了新粉丝提醒
            if(1 === $user_to -> new_message_comment){

                $time = new Carbon();

                // 将信息存入提醒表中
                Notification::create([
                    'user_id'        => $newSubscription['from'],
                    'notice_user_id' => $newSubscription['to'],
                    'type'           => 5,
                    'type_id'        => $subscription->id,
                    'created_at'     => $time,
                    'updated_at'     => $time
                ]);
            }

            // 从缓存中获取集合
            User::find($id) -> increment('follow_count');
            User::find($sub_id) -> increment('fans_count');

//            User::findOrfail($newSubscription['from']) -> increment('follow_count');

    //        $newSubscription_to = User::findOrfail($newSubscription['to']);

  //          $newSubscription_to -> update([
     //           'fans_count'        => $newSubscription_to -> fans_count ++,
   //             'new_fans_count'    => $newSubscription_to -> new_fans_count ++
   //         ]);

            // 统计对方是否也已关注自己
            $friend = Subscription::where('from',$newSubscription['to'])
                -> where('to',$newSubscription['from'])
                -> first();

            // 如果相互关注，则将双方信息写入XMPP
            if($friend){

                # 写入xmpp部分暂时关闭，暂时不需要  开始
//                // 关注者信息 tig_users 表
//                $from_tig_users = DB::table('tig_users')->where('user_id',$from_id.'@goobird')->first();
//
//                // 关注者信息 tig_pairs 表
//                $from_pairs = DB::table('tig_pairs')->where('uid',$from_tig_users->uid)->where('pkey','roster')->first();
//
//                // 关注者 写入对方信息 tig_pairs 表 用户名@goobird pkey字段值为 roster
//                DB::table('tig_pairs')->where('nid',$from_pairs->nid)->update([
//                    'pval' => $from_pairs->pval."<contact jid='".$sub_id."@goobird' preped='simple' weight='1.0' activity='1.0' subs='both' last-seen=".time()." name='".$sub_id."'/>",
//                ]);
//
//                // 被关注者信息 tig_users 表
//                $to_tig_users = DB::table('tig_users')->where('user_id',$sub_id.'@goobird')->first();
//
//                // 被关注者信息 tig_pairs 表
//                $to_pairs = DB::table('tig_pairs')->where('uid',$to_tig_users->uid)->where('pkey','roster')->first();
//
//                // 被关注者 写入关注方信息 tig_pairs 表 用户名@goobird pkey字段值为 roster
//                DB::table('tig_pairs')->where('nid',$to_pairs->nid)->update([
//                    'pval' => $to_pairs->pval."<contact jid='".$from_id."@goobird' preped='simple' weight='1.0' activity='1.0' subs='both' last-seen=".time()." name='".$from_id."'/>",
//                ]);

                # 写入xmpp部分暂时关闭，暂时不需要  结束

                $time = Carbon::now()->toDateTimeString();

                // 将相互关注的用户信息存入 friend 表中
                Friend::create([
                    'from'   => $sub_id,
                    'to'     => $id,
                    'created_at' => $time,
                    'updated_at' => $time
                ]);

                Friend::create([
                    'from'   => $id,
                    'to'     => $sub_id,
                    'created_at' => $time,
                    'updated_at' => $time
                ]);
            }

            DB::commit();

            // 将subscription表中新产生的id及是否相互关注返回json字符串
            return response()->json(
                [
                    'id'     => $subscription->id,
                    'mutual' => $friend ? 'true' : 'false'
                ],201
            );

            // 异常处理
        } catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error' => 'user_not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 取消订阅
     * @Delete("/{id}")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(403,body={"error":"forbidden"}),
     *     @Response(404,body={"error":"subscription_not_found"}),
     * })
     *
     */
    public function delete($id,$sub_id)
    {
        try{

            // 查询用户的订阅信息
            $subscription = Subscription::where('from',$id)->where('to',$sub_id)->first();

            // 判断是否存在
            if(!$subscription) return response()->json(['error' => 'subscription_not_found'],404);

            // 判断是否为相互关注
            $result = Subscription::where('from',$sub_id)->where('to',$id)->first();

            // 开启事务
            DB::beginTransaction();

            //取消提醒
             Notification::where('type_id',$subscription->id)->where('type',5)->delete();

            // 从缓存中将被关注的用户的粉丝数量-1 关注者的关注总数-1
            User::find($id) -> decrement('follow_count');

            User::find($sub_id) -> decrement('fans_count');

            // 如果为相互订阅，需同时删除在XMPP系统的在 tig_pairs 表中的信息
            if($result){

                # 取消者 数据操作 start 暂时关闭xmpp数据写入
//                // 先从tig_users表中取 取消者 的数据
//                $from_user = DB::table('tig_users')->where('user_id',$id.'@goobird')->first();
//
//                // 获取在 tig_pairs 表中 roster 字段的值
//                $from_data = DB::table('tig_pairs')->where('uid',$from_user->uid)->where('pkey','roster')->value('pval');
//
//                // 将数据进行拆分，用空格
//                $from_data_array = explode('><',$from_data);
//
//                // 对拆分后的数组进行遍历
//                foreach($from_data_array as $value){
//
//                    // 如果数据中没有要取消的用户的id，则写入另一个新数组
//                    if(!substr_count($value,$sub_id)) $from_data_array_new[] = '<'.trim(trim($value,'<'),'<').'>';
//                }
//
//                // 对新数据用空格重新拼接
//                $from_date_new = implode('',$from_data_array_new);
//
//                // 将新数据重新写入 tig_pairs 表中
//                DB::table('tig_pairs')->where('uid',$from_user->uid)->where('pkey','roster')->update([
//                    'pval' => $from_date_new,
//                ]);
//                # 取消者 数据操作 end
//
//                # 被取消者 数据操作 start
//                // 先从tig_users表中取 取消者 的数据
//                $to_user = DB::table('tig_users')->where('user_id',$sub_id.'@goobird')->first();
//
//                // 获取在 tig_pairs 表中 roster 字段的值
//                $to_data = DB::table('tig_pairs')->where('uid',$to_user->uid)->where('pkey','roster')->value('pval');
//
//                // 将数据进行拆分，用空格
//                $to_data_array = explode('><',$to_data);
//
//                // 对拆分后的数组进行遍历
//                foreach($to_data_array as $value){
//
//                    // 如果数据中没有要取消的用户的id，则写入另一个新数组
//                    if(!substr_count($value,$id)) $to_data_array_new[] = '<'.trim(trim($value,'<'),'<').'>';
//                }
//
//                // 对新数据用空格重新拼接
//                $to_date_new = implode('',$to_data_array_new);
//
//                // 将新数据重新写入 tig_pairs 表中
//                DB::table('tig_pairs')->where('uid',$to_user->uid)->where('pkey','roster')->update([
//                    'pval' => $to_date_new,
//                ]);

                # 被取消者 数据操作 end

                // 删除在friend 表中的数据
                Friend::where(['from'=>$id,'to'=>$sub_id])->orWhere(['to'=>$id,'from'=>$sub_id])->delete();
            }

            // 删除订阅信息
            $subscription->delete();

            // 事务提交
            DB::commit();

            return [
                'status' => 'success',
                'status_code' => 204,
            ];

        } catch (ModelNotFoundException $e) {

            DB::rollBack();
            return response()->json(['error' => 'subscription_is_found'],404);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }
}