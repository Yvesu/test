<?php

namespace App\Api\Controllers;

use App\Api\Transformer\LettersDetailsTransformer;
use App\Api\Transformer\LetterUserTransformer;
use App\Api\Transformer\TweetsPreviewTransformer;

use App\Api\Transformer\UsersTransformer;
use App\Api\Transformer\LettersTransformer;
use App\Models\PrivateLetter;
use App\Models\Blacklist;
use App\Models\Notification;
use App\Models\Friend;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;

/**
 * 私信相关接口
 *
 * @Resource("")
 */
class PrivateLetterController extends BaseController
{
    protected $usersTransformer;

    protected $lettersTransformer;

    protected $letterUserTransformer;

    protected $lettersDetailsTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        LettersTransformer $lettersTransformer,
        LetterUserTransformer $letterUserTransformer,
        LettersDetailsTransformer $lettersDetailsTransformer
        )
    {
        $this->usersTransformer = $usersTransformer;
        $this->lettersTransformer = $lettersTransformer;
        $this->letterUserTransformer = $letterUserTransformer;
        $this->lettersDetailsTransformer = $lettersDetailsTransformer;
    }

    /**
     * 获取某个用户的未读私信
     *
     * @Get("users/{id}/letters?{limit,timestamp,type}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("limit", description="每次返回最大条数",default=20),
     *      @Parameter("timestamp", description="每次起始时间点",default="当前时间"),
     *      @Parameter("type", description="0代表未读，1代表已读")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={"data":{
     *                          {
     *                              "id":106,
     *                              "from":"发信人",
     *                              "to":"收信人",
     *                              "type":0,
     *                              "content":"content",
     *                              "created_at":1464250271
     *                          },
     *                          {
     *                              "id":107,
     *                              "from":"发信人",
     *                              "to":"收信人",
     *                              "type":0,
     *                              "content":"content",
     *                              "created_at":1464250271
     *                          }},
     *                          "timestamp":123456,
     *                          "count":20,
     *                          "link":"url"
     *     }),
     *     @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function index($id,Request $request)
    {
        try {
            // 获取要查询的类型，0=>未读，1=>已读,默认为0
//            $type = $request->input('type',0);

            $user_type = $request->get('user_type','0');

            // 自定义函数，判断是否为数字，并返回包含 时间date和条数limit 的数组
            list($date, $limit) = $this->transformerTimeAndLimit($request);

            // 获取登录用户信息
            $user = Auth::guard('api')->user();

            // 按时间倒序获取前20条数据
            if ($user_type === '0'){            //用户的私信
                $letters = PrivateLetter::with('belongsToUser')
//                        ->ofData($type,$date)
                    ->where('pid',0)
                    ->where('user_type','0')
                    ->orderBy('created_at','desc')
                    ->where('to',$user->id)
                    ->take($limit)
                    ->get();

            }else{              //官方的私信

                $letters = PrivateLetter::with('belongsToUser')
//                        ->ofData($type,$date)
                    ->where('user_type','1')
                    ->where('pid',0)
                    ->orderBy('created_at','desc')
                    ->where('to',$user->id)
                    ->take($limit)
                    ->get();
            }

            $official_count = PrivateLetter::where('user_type','1')
                ->where('type',0)
                ->where('to',$user->id)
                ->count();

            // 统计或获取数据的数量
            $count = $letters->count();

            // 将所取数据状态设置为已读     调试期间，暂时注释，调试结束打开
//            $letters -> each(function($letter){
//                $letter -> type = 1;
//                $letter -> save();
//            });

            foreach ($letters as $v){
                if ($v->type === 0){
                    PrivateLetter::find($v->id)->update(['type'=>1]);
                }
            }

            // 返回数据
            return [

                // 所获取的数据
                'data'       => $count ? $this->lettersTransformer->transformCollection($letters->all()) : [],

                // 最后一条信息的时间戳
                'timestamp'  => $count ? (int)strtotime($letters->last()->created_at) : null,

                // 本次获取数据的总数量
                'count'      => $count,

                'official_count'    => $official_count,

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
//                'link'       => $count
//                    ? $request->url() .
//                    '?limit=' . $limit .
//                    '&timestamp=' . strtotime($letters->last()->created_at)  // 最后一条信息的时间戳
//                    : null      // 如果数量为0，则不附带搜索条件
            ];
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 发送私信
     *
     * @Post("users/{id}/letter/send")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id", description="发送私信人的id"),
     *      @Parameter("to_id", description="接收私信人的id"),
     *      @Parameter("content", description="内容信息，不能同时为空"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"id":6,}),
     *     @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function create($id,Request $request)
    {
        try {
            $id_to = (int)$request->get('to');

            // 判断是否在黑名单内
            if(Blacklist::ofBlackIds($id,$id_to)->first()){

                // 在自己的黑名单中
                return response()->json(['error'=>'in_own_black_list'],431);
            }elseif(Blacklist::ofBlackIds($id_to,$id)->first()){

                // 在对方的黑名单中
                return response()->json(['error'=>'in_his_black_list'],432);
            }

            // 判断是否允许私信
//            $personalAllow = new CommonController();
//            if(!$personalAllow->personalAllow($id,$id_to,'stranger_private_letter'))
            $user = User::find($id_to);

            $users_id = Subscription::where('from',$id_to)->pluck('to');

            if (!$user->stranger_private_letter && !in_array($id,$users_id->all()))
                return response()->json(['error'=>'stranger_cannot_letter'],433);

            $time = new Carbon();

            // 新私信内容
            $newLetter = [
                'from' => $id,
                'to' => $id_to,
                // 防止SQL注入处理
                'content' => removeXSS($request->get('content') === null ? null : $request->get('content')),
                'created_at' => $time,
                'updated_at' => $time,
                'read_from'  => '1',
            ];

            // 查询user表中是否有收发私信者的信息
            $user_from = User::findOrFail($newLetter['from']);
            $user_to = User::findOrFail($newLetter['to']);

            // 如果所传参数有其中一个为空，则返回错误信息
            if (is_null($user_from) || is_null($user_to) || is_null($newLetter['content'])) {
                return response()->json([
                    'error' => 'bad_request'
                ], 401);
            }

            // 判断他们之间是否为好友关系
            $friends = Friend::where('from',$user_from)->where('to',$user_to)->first();

            if($friends) return response()->json(['error' => 'bad_request'], 401);

            // 将私信存入私信表中 private_letter
            $letter = PrivateLetter::create($newLetter);

            return response()->json([
                'letter_id' => $letter->id
            ]);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    // 对获取的条数及时间戳进行格式化处理
    public function transformerTimeAndLimit(Request $request)
    {
        $limit = $request->get('limit');
        $timestamp = $request->get('timestamp');

        $limit = isset($limit)  && is_numeric($limit) ? $limit : 20;
        $timestamp = isset($timestamp) && is_numeric($timestamp) ? $timestamp : time();

        // 将获取时间转格式
        $date = Carbon::createFromTimestamp($timestamp)->toDateTimeString();

        return array($date,$limit);
    }

    /**
     * 获取某个用户的未读私信的数量
     *
     * @Get("users/{id}/letters/count")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={"data":{
     *                          {
     *                              "id":107,
     *                              "from":"发信人",
     *                              "to":"收信人",
     *                              "type":0,
     *                              "content":"content",
     *                              "created_at":1464250271
     *                          }},
     *                          "timestamp":123456,
     *                          "count":20,
     *     }),
     *     @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function count(Request $request)
    {
        try {
            // 获取登录用户信息
            $user = Auth::guard('api')->user();

            // 按时间倒序获取第一条数据
            $letters = PrivateLetter::with('belongsToUser')
                ->where('type',0)
                ->where('to',$user->id)
                ->orderBy('created_at','desc')
                ->limit(1)
                ->get();

            // 统计未读私信的数量
            $counts = PrivateLetter::where('to',$user->id)->where('type',0)->count();

            // 统计未读评论的数量
            $notifications = Notification::where('notice_user_id',$user->id)->where('status','0')->get();

            // 统计未读评论的数量
            $reply_count = $notifications -> whereIn('type',[2,4])->count();

            // 统计@我的数量
            $at_count = $notifications -> whereIn('type',[0,3])->count();

            // 统计点赞数量
            $like_count = $notifications -> where('type','1') -> count();

            // 返回数据
            return [

                // 所获取的数据
                'data'       => $counts ? $this->lettersTransformer->transformCollection($letters->all()) : [],

                // 最后一条信息的时间戳
                'timestamp'  => $counts ? (int)strtotime($letters->last()->created_at) : null,

                // 未读消息的总数量
                'count'      => $counts,

                // 统计未读评论的数量
                'reply_count' => $reply_count,

                // 统计@我的数量
                'at_count'    => $at_count,

                // 统计点赞数量
                'like_count'  => $like_count,
            ];
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 删除私信
     *
     * @Post("users/{id}/letters/delete")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Parameter("letters", description="要删除私信的ID数组")
     *     @Response(200,body={"data":{
     *                          {
     *                              "id":107,
     *                              "from":"发信人",
     *                              "to":"收信人",
     *                              "type":0,
     *                              "content":"content",
     *                              "created_at":1464250271
     *                          }},
     *                          "timestamp":123456,
     *                          "count":20,
     *     }),
     *     @Response(400,body={"error":"bad_request"}),
     * })
     */
    public function delete(Request $request)
    {
        try {
            // 获取登录用户信息
            $user = Auth::guard('api')->user();

            // 获取用户要删除私信的ID 数组
            $letter_ids = json_decode($request->get('letters'));

            // 获取私信集合
            $letters = PrivateLetter::whereIn('id',$letter_ids)->get();

            // 判断是否为空
            if(!$letters) return response()->json(['error'=>'not_found'],404);

            // 判断用户为发信人还是收信人
            $letters->each(function($letter) use($user){

                // 如果为发件人
                if($letter->from === $user->id){
                    if($letter -> delete_to === 0 && $letter -> delete_from === 0) $letter -> delete_from = 1;
                    if($letter -> delete_to === 1 && $letter -> delete_from === 0) $letter -> delete();

                // 如果为收件人
                }elseif($letter->to === $user->id){
                    if($letter -> delete_to === 0 && $letter -> delete_from === 0) $letter -> delete_to = 1;
                    if($letter -> delete_to === 0 && $letter -> delete_from === 1) $letter -> delete();
                }

                $letter -> save();
            });

            // 返回数据
            return response()->json(['status'=>'ok'],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return array
     */
    public function userletter($id,Request $request)
    {
        $user_type = $request->get('user_type','0');
        $page = (int)$request->get('page',1);

        // 获取登录用户信息
        $user = Auth::guard('api')->user();
        $user_id = (int)$id;

        if ($user_type === '0'){
            $letters_1 = PrivateLetter::where(function ($q) use ($user_id){
                $q->where('from',$user_id)
                    ->where('delete_from',0);
            })
                ->where('user_type','0')
                ->orderBy('created_at','DESC')
                    ->distinct()
                ->pluck('to');

            $letters_2 = PrivateLetter::where(function ($q) use ($user_id){
                    $q->where('to',$user_id)
                        ->where('delete_to',0);
                })
                ->where('user_type','0')
                ->orderBy('created_at','DESC')
                ->distinct()
                ->pluck('from');

            $arr = array_unique( array_merge($letters_1->toArray(),$letters_2->toArray()) );

            $data = array_map(function ($uid)use ($user_id,$arr){
                $data = PrivateLetter::where(function ($q) use ($user_id,$uid){
                    $q->where('from',$user_id)
                        ->where('delete_from',0)
                        ->where('to',$uid);
                })
                    ->orWhere(function ($q) use ($user_id,$uid){
                        $q->where('to',$user_id)
                            ->where('delete_to',0)
                            ->where('from',$uid);
                    })
                    ->orderBy('created_at','DESC')
                    ->first();

                return $this->letterUserTransformer->transform($data);
            },$arr);

            $official_count = PrivateLetter::where('user_type','1')
                ->where('read_to','0')
                ->where('to',$user->id)
                ->count();


            // 返回数据
            return [
                // 所获取的数据
                'data'              =>  array_values($data),

                'official_count'    => $official_count,

            ];

        }else{
            $letters = PrivateLetter::with('belongsToUser')
                ->where('user_type','1')
//                ->where('pid',0)
                ->where(function ($q) use ($user_id){
                    $q->where('to',$user_id)
                        ->where('delete_to',0);
                })
                ->forPage($page,20)
                ->orderBy('created_at','desc')
                ->where('to',$user->id)
                ->get();

            $count = $letters->count();

            foreach ($letters as $v){
                if ($v->read_to === '0'){
                    PrivateLetter::find($v->id)->update(['read_to'=>'1']);
                }
            }

            // 返回数据
            return [
                // 所获取的数据
                'data'       => $count ? $this->lettersTransformer->transformCollection($letters->all()) : [],

            ];
        }

    }

    public function details($id,Request $request)
    {
        //接收私信用户的属性
        if ( is_null( $user_from_id = (int)$request->get('user') )) return response()->json(['message'=>'bad request'],403);

        $page = (int)$request->get('page',1);

        $user_to_id = (int)$id;

        $letters = PrivateLetter::with('belongsToUser')
            ->where(function ($q) use($user_from_id,$user_to_id){
                $q->where('from',$user_from_id)
                    ->where('to',$user_to_id)
                    ->where('user_type','0')
                        ->where('delete_to',0);
            })
            ->orWhere(function($q) use($user_from_id,$user_to_id){
                $q->where('from',$user_to_id)
                    ->where('to',$user_from_id)
                    ->where('user_type','0')
                        ->where('delete_from',0);
            })
            ->orderBy('created_at','ASC')
            ->get();

        return response()->json([
            'data'=> $this->lettersDetailsTransformer->transformCollection($letters->all()),
        ]);
    }

    public function newdelete(Request $request)
    {
        try{
           if (is_null($type = $request ->get('type')))  return response()->json(['message'=>'bad request'],403);

           $user = Auth::guard('api')->user();

            if ( $type === '0'){
                //删除某用户的记录
                if (is_null($user_from_id = (int)$request->get('from'))) return response()->json(['message'=>'bad request'],403);

                \DB::beginTransaction();

                //当该用户为接收者

                $result_to_data = PrivateLetter::where('from',$user_from_id)
                    ->where('to',$user->id)
                    ->pluck('id');

                $result_to = 1;
                if($result_to_data->all()){
                    $result_to = PrivateLetter::whereIn('id',$result_to_data->all())->update(['delete_to'=>1]);
                }

                //当该用户为发送者
                $result_from_data = PrivateLetter::where('from',$user->id)
                    ->where('to',$user_from_id)
                    ->pluck('id');

                $result_from = 1;
                if($result_from_data->all()){
                    $result_from = PrivateLetter::whereIn('id',$result_from_data->all())->update(['delete_from'=>1]);
                }

                if ($result_to && $result_from){
                    \DB::commit();
                    return response()->json(['message'=>'success'],201);
                }else{
                    \DB::rollBack();
                    return response()->json(['message'=>'failed'],500);
                }
            }elseif ( $type === '1'){
                //删除单条
                if (is_null($letter_id = (int)$request->get('letter'))) return response()->json(['message'=>'bad request'],403);

                $letter = PrivateLetter::find($letter_id);

                \DB::beginTransaction();

                if ($letter->from === $user->id){
                    $result = PrivateLetter::where('id',$letter_id)->update(['delete_from'=>1]);
                }elseif($letter->to === $user->id){
                    $result = PrivateLetter::where('id',$letter_id)->update(['delete_to'=>1]);
                }

                if ($result){
                    \DB::commit();
                    return response()->json(['message'=>'success'],201);
                }else{
                    \DB::rollBack();
                    return response()->json(['message'=>'failed'],500);
                }

            }elseif( $type === '2'){
                //删除多条
                if (is_null($letters=$request->get('letters'))) return response()->json(['message'=>'bad request'],403);
                $ids = explode(',',$letters);
                \DB::beginTransaction();
                $result = array_map(function($id) use ($user){
                    $letter = PrivateLetter::find($id);

                    if ($letter->from === $user->id){
                        $result = PrivateLetter::where('id',$id)->update(['delete_from'=>1]);
                    }elseif($letter->to === $user->id){
                        $result = PrivateLetter::where('id',$id)->update(['delete_to'=>1]);
                    }
                    return $result;
                },$ids);

                if ($result){
                    \DB::commit();
                    return response()->json(['message'=>'success'],201);
                }else{
                    \DB::rollBack();
                    return response()->json(['message'=>'failed'],500);
                }
            }else{
                if (is_null($users=$request->get('users'))) return response()->json(['message'=>'bad request'],403);
                $users_ids = explode(',',$users);

                $result = array_map(function ($user_from_id) use ($user){
                    \DB::beginTransaction();

                    //当该用户为接收者

                    $result_to_data = PrivateLetter::where('from',$user_from_id)
                        ->where('to',$user->id)
                        ->pluck('id');

                    $result_to = 1;
                    if($result_to_data->all()){
                        $result_to = PrivateLetter::whereIn('id',$result_to_data->all())->update(['delete_to'=>1]);
                    }

                    //当该用户为发送者
                    $result_from_data = PrivateLetter::where('from',$user->id)
                        ->where('to',$user_from_id)
                        ->pluck('id');

                    $result_from = 1;
                    if($result_from_data->all()){
                        $result_from = PrivateLetter::whereIn('id',$result_from_data->all())->update(['delete_from'=>1]);
                    }

                    if ($result_to && $result_from){
                        \DB::commit();
                        return true;
                    }else{
                        \DB::rollBack();
                        return false;
                    }

                },$users_ids);

                if ($result){
                    \DB::commit();
                    return response()->json(['message'=>'success'],201);
                }else{
                    \DB::rollBack();
                    return response()->json(['message'=>'failed'],500);
                }
            }
        }catch (\Exception $e){
            return response()->json(['message'=>'bad request'],500);
        }
    }

}