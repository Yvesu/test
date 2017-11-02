<?php

namespace App\Api\Controllers;


use App\Api\Transformer\ChannelsTransformer;
use App\Api\Transformer\UsersTransformer;
use App\Api\Transformer\UsersWithSubTransformer;
use App\Models\Channel;
use App\Models\Subscription;
use App\Models\Topic;
use App\Models\Tweet;
use App\Models\User;
use App\Models\UserChannel;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use DB;
use Auth;
use JWTAuth;
use CloudStorage;

/**
 * 频道相关接口
 *
 * @Resource("Channels")
 */
class ChannelController extends BaseController
{
    protected $channelsTransformer;

    protected $usersTransformer;

    protected $usersWithSubTransformer;

    public function __construct(
        ChannelsTransformer $channelsTransformer,
        UsersTransformer $usersTransformer,
        UsersWithSubTransformer $usersWithSubTransformer
    )
    {
        $this->channelsTransformer = $channelsTransformer;
        $this->usersTransformer = $usersTransformer;
        $this->usersWithSubTransformer = $usersWithSubTransformer;
    }

    /**
     * 获取全部频道
     *
     * @Get("/channels")
     * @Versions({"v1"})
     * @Transaction({
     *     @Response(200,body={{"id":1,"name":"男神","icon":"http://7xtg0b.com1.z0.glb.clouddn.com/channel/1/Firefox Mac.png","created_at":1463738535},{"id":2,"name":"女神","icon":"http://7xtg0b.com1.z0.glb.clouddn.com/channel/2/home_list_lady.png","created_at":1463740727}})
     * })
     */
    public function index()
    {
        $channels = Channel::active()->orderBy('sort')->get(['id', 'name', 'ename', 'icon']);
        return response()->json($this->channelsTransformer->transformCollection($channels->all()));
    }

    /**
     * 频道详情 20170922版
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details($id)
    {
        $channel = Channel::active() -> findOrFail($id);

        $topic = Topic::active()->take(6)->get(['id','name']);

        return response() -> json([
            'channel'   => $this -> channelsTransformer -> transform($channel),
            'topic'     => $topic -> all()
        ], 200);
    }

    /**
     * 获取一个用户保存的频道
     *
     * @Get("/users/{id}/channels")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id",required=true, description="<<URL中的ID>> 为当前用户ID"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={"selected":{{"id":1,"name":"男神","icon":"http://7xtg0b.com1.z0.glb.clouddn.com/channel/1/Firefox Mac.png","created_at":1463738535}},
     *                         "unselected":{{"id":2,"name":"女神","icon":"http://7xtg0b.com1.z0.glb.clouddn.com/channel/2/home_list_lady.png","created_at":1463740727}}})
     * })
     */
    public function userIndex($id)
    {
        try{

            // 获取用户在数据库的频道信息
            $channels = UserChannel::where('user_id',$id)->firstOrFail(['channel_id']);

            // 获取频道id数组
            $channel_ids = explode(',',$channels->channel_id);

            // 获取各频道具体信息
            $channels_data = Channel::whereIn('id',$channel_ids)
                -> active()
                -> orderByRaw("field (id, ".$channels->channel_id.")")
                -> get(['id', 'name', 'ename', 'icon']);

            // 获取用户是否有关注好友
            $subscription = Subscription::where('from',$id)->get(['id'])->count();

            // 返回数据
            return response() -> json([
                'data'          => $this->channelsTransformer->transformCollection($channels_data->all()),
                'subscription'  => $subscription
            ]);

        } catch (\Exception $e) {

            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }
    }

    /**
     * 更新用户保存的频道
     *
     * @Post("/users/{id}/channels")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id",required=true, description="<<URL中的ID>> 为当前用户ID"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"},body={"channels":"[1,2]"}),
     *     @Response(200,body={"selected":{{"id":1,"name":"男神","icon":"http://7xtg0b.com1.z0.glb.clouddn.com/channel/1/Firefox Mac.png","created_at":1463738535}},
     *                         "unselected":{{"id":2,"name":"女神","icon":"http://7xtg0b.com1.z0.glb.clouddn.com/channel/2/home_list_lady.png","created_at":1463740727}}})
     * })
     */
    public function userStore($id,Request $request)
    {
        try {
            // 获取所有要更新的频道id
            $channels_ids = removeXSS($request->get('channels'));

            // 解码，返回数组（true）
            $channels_ids = json_decode($channels_ids,true);

            // 获取该用户的在user_channel 表中所有的id信息，按照升序排序
            $channels_old = UserChannel::where('user_id',$id)->firstOrFail(['id','channel_id','time_update']);

            // 获取数组
            $channels_arr = explode(',',$channels_old->channel_id);

            // 获取差集
            $diff = array_diff($channels_arr,$channels_ids);

            // 判断是否都为空
            if(count($channels_ids) !== count($channels_arr) || !empty($diff))
                return response()->json(['error' => 'bad_request'],403);

            // 用户新的频道信息
            $channels_new = implode(',', $channels_ids);

            // 更新表
            $channels_old -> update([
                'channel_id' => $channels_new,
                'time_update' => getTime()
            ]);

            // 存入日志文件
            \Log::info('Change User '.$id.' channels_id:'.$channels_new);

            return response()->json(['status'=>'OK'],201);

        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 获取某个频道的排行榜，以点赞数量排名
     *
     * @GET("channels/{channel_id}/ranking")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("channel_id",required=true, description="<<URL中的Channel_ID>> 要获取的频道ID"),
     * })
     * @Transaction({
     *     @Response(200,body={{"id":10000,"nickname":"从结局。 法国好。     广告画","avatar":null,"hash_avatar":null,"signature":null},{"id":10001,"nickname":"test","avatar":null,"hash_avatar":null,"signature":null,"follower":false,"following":false}})
     * })
     */
//    public function ranking($id,Request $request)
//    {
//        try {
//            // 获取limit
//            $limit = (int)$request -> get('limit',50);
//
//            // 判断所取用户数量
//            if($limit !== 5 && $limit !== 50) return response()->json(['error'=>'bad_request'],403);
//
//            $user_id = Tweet::whereHas('hasManyChannelTweet', function ($q) use ($id) {
//                $q -> where('channel_id', $id);
//            })  -> groupBy('user_id')                                            //根据用户id进行分组
//            -> orderBy('most_like','desc')                                   //按点赞数倒叙排序
//            -> get(['user_id', DB::raw('MAX(like_count) as most_like')])     //取用户ID和分组时的最大点赞数
//            -> take($limit)              // 一次性查询出n个
//            -> pluck('user_id')      // pluck 方法是取得结果集第一列特定字段的值
//            -> all();
//
//            // 判断是否有数据
//            if(empty($user_id)) return response()->json([],204);
//
//            // 将用户id拼成字符串
//            $arr = implode (',', $user_id);
//
//            // 查询所有用户信息，按获取的id排序
//            $users = User::whereIn('id', $user_id)
//                ->orderByRaw(DB::raw("FIELD(id,$arr)"))     // 获取所有基本属性
//                ->get();
//
//            // 用户登录状态，判断双发关注关系
//    //        if ($user = Auth::guard('api')->user()) {
//    //            $users->load([
//    //                // 是否关注对方
//    //                'hasManySubscriptions' => function ($q) use ($user) {
//    //                    $q->where('from', $user->id);
//    //                },
//    //                // 是否被对方关注，关闭
//    //                'hasManySubscriptionsFrom' => function ($q) use ($user) {
//    //                    $q->where('to', $user->id);
//    //                }
//    //            ]);
//    //        }
//            return response()->json($this->usersWithSubTransformer->transformCollection($users->all()));
//        } catch (\Exception $e) {
//
//            return response()->json(['error' => $e->getMessage()],$e->getCode());
//        }
//    }
}