<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/27
 * Time: 11:35
 */

namespace App\Api\Controllers;

use App\Api\Transformer\ProfileTransformer;
use App\Api\Transformer\UserInfomationTransformer;
use App\Api\Transformer\UsersTransformer;
use App\Api\Transformer\UsersSearchTransformer;
use App\Api\Transformer\NearbyUsersTransformer;
use App\Models\Blacklist;
use App\Models\Make\MakeTemplateDownloadLog;
use App\Models\Notification;
use App\Models\PrivateLetter;
use App\Models\Subscription;
use App\Models\TweetLike;
use App\Models\User;
use App\Models\Adcode;
use App\Models\UserCollections;
use App\Models\UserSex;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use Auth;
use EaseMob;
use DB;
use Carbon\Carbon;
use Cache;
/**
 * 用户信息
 *
 * @Resource("Users",uri="/users")
 */
class UserController extends BaseController
{
    protected $reg = array(
        'ymd'=>"/^\d{4}[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/",
    );

    // 页码
    protected $paginate = 20;

    protected $usersTransformer;

    protected $usersSearchTransformer;

    protected $nearbyUsersTransformer;

    protected $profileTransformer;

    protected $userInfomationTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        ProfileTransformer $profileTransformer,
        UserInfomationTransformer $userInfomationTransformer,
        UsersSearchTransformer $usersSearchTransformer,
        NearbyUsersTransformer $nearbyUsersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->usersSearchTransformer = $usersSearchTransformer;
        $this->nearbyUsersTransformer = $nearbyUsersTransformer;
        $this->profileTransformer = $profileTransformer;
        $this->userInfomationTransformer = $userInfomationTransformer;
    }

    /**
     * 上传头像后的回调函数
     *
     * 返回状态码 请参照 http://developer.qiniu.com/article/developer/responsebody.html#http-code
     * 只有返回200时，可在第一个参数中查询{"success" : true}
     * @Post("/{id}/avatar?token=token")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("token",required=true, description="由于七牛提供的回调，不提供在headers中传参，所以token要在url拼接上."),
     *     @Parameter("id",required=true, description="用户id.")
     * })
     */
    public function avatar(Request $request,$id)
    {
        try {
            $avatar_url = asset($request->getRequestUri());

            \Log::info($avatar_url);
            if(!CloudStorage::verityCallback($avatar_url)){
                throw new \Exception('storage_unauthorized',401);
            }
            
            $user = User::findOrFail($id);
            CloudStorage::delete($user->avatar);
            $user->avatar = $request->get('key');
            $user->hash_avatar = $request->get('hash');
            $user->save();

            return response()->json($this->usersTransformer->transform($user),201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }
    
    /**
     * 通过username查询ID   旧，已废弃
     *
     * @Get("/{id}/usernames/{username}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id",required=true, description="使用者的ID"),
     *     @Parameter("username",required=true, description="需要查询的username或ID")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={"id": 10000,"nickname":"nickname"}),
     *     @Response(404,body={"error":"user_not_found"}),
     *     @Response(401,body={"error":"unauthorized"})
     * })
     */
    public function searchUsername($id,$username)
    {
        try {
            // 验证用户登陆信息
            if($id != Auth::guard('api')->user()->id){
                throw new \Exception('unauthorized',401);
            }

            // 用户与本地认证 一对一关系
            $user = User::whereHas('hasOneLocalAuth',function($q) use($username){
                $q->where('username',$username);
            })->orWhere('id',$username)->firstOrFail();

            // 返回过滤后的数据
            return response()->json($this->usersTransformer->transform($user));

        // 异常
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'user_not_found'],404);
        } catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 修改个人信息
     * @Put("/{id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("nickname", description="昵称",default="默认为上次保存的昵称"),
     *     @Parameter("avatar", description="头像key值，请以temp/开头",default="默认为上次保存的头像key"),
     *     @Parameter("hash_avatar", description="头像hash值",default="默认为上次保存的hash值"),
     *     @Parameter("signature", description="签名",default="空"),
     *     @Parameter("sex", description="性别",default="0(女)"),
     *     @Parameter("background", description="背景key值",default="空"),
     *     @Parameter("birthday", description="生日，格式xxxx-xx-xx",default="空"),
     *     @Parameter("location", description="地点",default="空")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization":"Bearer TOKEN"}),
     *     @Response(201,body={"id":10029,"nickname":"100280","avatar":"http://7xtg0b.com1.z0.glb.clouddn.com/users/10029/1231231.jpg","hash_avatar":null,"signature":null,"sex":0,"background":null,"location":null,"birthday":null,"follower_count":3,"following_count":0,"likes_count":0}),
     *     @Response(400,body={"error":"bad_request"}),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"user_not_found"}),
     *     @Response(409,body={"error":"nickname has existed"})
     * })
     */
    public function update($id,Request $request)
    {
        try {
            $user = User::findOrFail($id);;

            $nickname   = $request->get('nickname');
            $avatar     = $request->get('avatar');
            $birthday   = $request->get('birthday');
            $sex        = (int)$request->get('sex');
            $xmpp       = (int)$request->get('xmpp');
            $advertisement    = (int)$request->get('advertisement');
            $signature = removeXSS( $request ->get ('signature'));
            $location = $request->get('location');

            $time = getTime();

            // 判断昵称是否存在 写入集合
            if($nickname && $nickname != $user -> nickname){

                // 用户昵称长度在6到30个字符，或者2到10个汉字 匹配昵称的格式是否符合要求 匹配用户昵称中是否有特殊字符
                if(strlen($nickname)<6 || strlen($nickname)>30 || !regex_name($nickname) ||regex_forbid($nickname))
                    throw new \Exception('bad_request', 408);

                // 昵称已存在
                if (User::where('id','!=',$id)->where('nickname',$nickname)->first())
                    throw new \Exception($nickname . ' has existed', 409);

                // 存入集合
                $user -> nickname = $nickname;
            }


            // 生日 正则匹配
            if($birthday && $birthday != $user -> birthday){

                if( $birthday>$time) throw new \Exception('bad_request', 400);

                // 存入集合
                $user -> birthday = date('Y-m-d',$birthday);
            }

            // 性别
            if($sex && ($sex == 0 || $sex == 1) && $sex != $user -> sex){

                // 如果此次修改为注册时修改
                if($user -> sex === 2) {

                    // 存入集合
                    $user -> sex = $sex;
                }

                // 判断性别是否已做过一次修改
                $user_sex = UserSex::where('user_id',$id)->first();

                // 如果已经修改过，返回错误信息
                if($user_sex) throw new \Exception('sex_already_changed_once', 403);

                // 将信息保存
                UserSex::create([
                    'user_id'   => $id,
                    'time_add'  => $time
                ]);
            }

            // xmpp
            if($xmpp && ($xmpp == 0 || $xmpp == 1) && $xmpp != $user -> xmpp){

                // 存入集合
                $user -> xmpp = $xmpp;
            }

            // advertisement
            if($advertisement && ($advertisement == 0 || $advertisement == 1) && $advertisement != $user -> advertisement){

                // 存入集合
                $user -> advertisement = $advertisement;
            }


            if (! empty($location) && $location != $user->location) {

                $user->location =  $location;
            }

            if (! empty($avatar) && $avatar != $user->avatar) {

                $user->avatar =  $avatar;
            }

            if (! empty($signature) && $signature != $user->signature) {

                $user->signature =  $signature;

            }

            // 暂时没有背景图设置，后期需要再修改
//            if (! empty($background) && $background != $user->background){
//                $arr = explode('/', $background);
//                $new_background = 'users/' . $id . '/background/' . $arr[sizeof($arr) - 1];
//                CloudStorage::rename($background, $new_background);
//                ! empty($user->background) ? CloudStorage::delete($user->background) : null;
//                $user->background = $new_background;
//            }

            // EaseMob::editNickname($id,$nickname);    // 搜索号：备忘录  环信业务暂停

            // 保存集合
            $user->save();

            return response()->json($this->profileTransformer->transform($user),201);

        } catch(ModelNotFoundException $e) {
            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 查看个人设置  -- 隐私、提醒
     * @param int $id   用户id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setting($id)
    {
        try {
            $user = User::findOrFail($id);;

            $fields = ['stranger_comment','stranger_at','stranger_private_letter','location_recommend','search_phone','new_message_comment','new_message_fans','new_message_like'];

            $data = [];

            foreach($fields as $field){
                $data[$field] = $user -> $field;
            }

            return response()->json(['data'=>$data],200);

        } catch(ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'],404);
        }
    }

    /**
     * 修改个人设置
     * @param $id   用户id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function set($id,Request $request)
    {
        try {
            $user = User::findOrFail($id);

            $settings = $request -> all();

            // 判断是否为空
            if(empty($settings)){
                return response()->json(['error' => 'not_empty'],403);
            }

            // 初始化数组
            $set = [
                'stranger_comment',
                'stranger_at',
                'stranger_private_letter',
                'location_recommend',
                'search_phone',
                'new_message_comment',
                'new_message_fans',
                'new_message_like'
            ];
            $keys = [1,1];

            foreach($settings as $key => $setting){
                if(in_array($key,$set) && $keys[$setting]){
                    $user -> $key = $setting;
                    $user -> updated_at = new Carbon();
                    $user -> save();
                }
            }

            return response()->json(['status'=>'ok'],200);

        } catch(ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'],404);
        }
    }

    /**
     * 获取个人信息
     * @Get("/{id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id", description="添加者的ID即登录即时通讯的用户名(为URL中的id)"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization":"Bearer TOKEN"}),
     *     @Response(200,body={"id":10029,"nickname":"100280","avatar":"http://7xtg0b.com1.z0.glb.clouddn.com/users/10029/1231231.jpg","hash_avatar":null,"signature":null,"sex":0,"background":null,"location":null,"birthday":null,"follower_count":3,"new_follower_count":3,"following_count":0,"likes_count":0}),
     *     @Response(401,body={"error":"unauthorized"})
     * })
     */
    public function show($id)
    {

        $user = User::where('status', 0) -> findOrFail($id);

        return $this->userInfomationTransformer->transform($user);
    }

    /**
     * 用户中心首页接口
     * type ：  0 : tweet @ ,别人发的动态 被@ ,
     *          1 : tweet_like自己发的动态 被点赞,
     *          2 : tweet_comment 自己发的动态 被评论,
     *          3 : tweet_comment @任何动态在评论中被@,
     *          4 : tweet_comment_reply 自己发的评论被回复，
     *          5：新增粉丝
     *          6：被送奖杯，
     *          7：投诉处理成功，
     *          8：资金方面
     *
     * @param $id
     */
    public function center($id)
    {
        try{
            // 获取用户自己的信息
            $user = User::where('status', 0) -> findOrFail($id);

            // 获取用户的未读的各类提醒消息数量
            $notification = Notification::where('notice_user_id', $id)
                -> where('status', 0)
                -> select('type', DB::raw('count(id) as total'))
                -> groupBy('type')
                -> get();

            $data = [
                'at' => 0,
                'like' => 0,
                'reply' => 0,
                'message' => PrivateLetter::where('to', $id) -> where('type', 0) -> count(),    // 未读私信数量
                'templates' => MakeTemplateDownloadLog::where('user_id', $id) -> count(),    // 模板
                'my_like' => $user -> like_count,    // 我点赞的
                'collection' => $user -> collection_count,    // 收藏
            ];

            foreach($notification as $key => $value) {

                switch($value -> type) {
                    case 0:
                    case 3:
                        $data['at'] += $value -> total;
                        break;
                    case 1:
                        $data['like'] += $value -> total;
                        break;
                    case 2:
                    case 4:
                        $data['reply'] += $value -> total;
                        break;

                }
            }

            return response() -> json([
                'user' => $this->userInfomationTransformer->transform($user),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 模糊搜索用户
     *
     * @Get("users/search/?{name,limit,timestamp}")
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
            $users = User::ofSearch($name)
                ->status()
                ->orderBy('id','desc')
                ->ofSecond((int)$request -> get('last_id'))
                ->take($this->paginate)
                ->get();

            // 判断是否为第一次请求
            if(!$request -> get('last_id')){

                // 是否有精确完全相等的数据
                $name_users = User::ofName($name)->status()->take($this->paginate)->get();

                // 如果能精确匹配成功数据，将数据添加至总数据集合中
                if ($name_users !== null) {

                    $name_users->each(function($name_user)use($users){

                        $users->prepend($name_user);
                    });
                }
            }

            // 返回数据
            return response()->json([

                // 数据
                'data' => count($users) ? $this->usersSearchTransformer->transformCollection($users->all()) : null,

                // 总数量
                'count' => count($users),

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link' => count($users)
                    ? $request->url() .
                    '?name=' . $name .
                    '&last_id='.$users -> last() -> id
                    : null      // 如果数量为0，则不附带搜索条件
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 附近用户接口
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function nearby(Request $request)
    {
        try {
            // 获取页码
            $page = $request -> get('page',1);

            // 判断页码，最多5页
            if(!in_array($page,[1,2,3,4,5])) {

                return response()->json([
                    'data'  => [],
                    'count' => 0,
                    'url'   => ''
                ]);
            }

            // 判断用户是否为登录状态
            $user = Auth::guard('api')->user();

            // 黑名单
            $blacklist = $user ? Blacklist::where('from',$user->id)->pluck('to') : [];

            // 每页数量
            $limit = 10;

            // 获取用户所在区的编码
            $adcode = $request -> get('adcode','');

            // 获取用户所在市的编码
            $citycode = $request -> get('citycode','');

            // 判断是否为空
            if(!$adcode || !$citycode)  throw new \Exception('bad_request',403);

            // 获取在 zx_adcode 表中的id 表新增字段，此处未做修改
            $nearby_id = Adcode::where('citycode',$citycode)->where('adcode',$adcode) -> get() -> pluck('id');

            // 获取附近 区县级
            $nearby_users = User::whereHas('hasManyTweet',function ($q) {
                    $q->active()->visible();
                })
                -> with(['hasManyTweet' => function($query){
                    $query -> active() -> visible() -> orderBy('id','desc') -> get();
                }])
                -> whereIn('nearby_id',$nearby_id)
                -> whereIn('verify',[1,2])
                -> where('status',0)
                -> where('location_recommend',1)
                -> whereNotNull('avatar')
                -> whereNotNull('signature')
                -> ofRemoveSelf($user)  // 如果用户为登录状态，则将自己排除在外
                -> ofRemoveBlack($blacklist)  // 如果用户为登录状态，则将黑名单的人排除在外
                -> orderBy('id','desc')
                -> get();

            // 遍历判断动态是否超过5条
            foreach($nearby_users as $key => $value){

                if($value -> hasManyTweet -> count() < 5){

                    $nearby_users -> forget($key);
                }
            }

            // 分页
            $nearby_users = $nearby_users -> forPage($page, $limit) -> values();

            // 判断数量
            if(!$nearby_users->count()){

                return response()->json([
                    'data'  => [],
                    'count' => 0,
                    'url'   => ''
                ]);
            }

            return response()->json([

                // 获取的数据
                'data' => $nearby_users ? $this->nearbyUsersTransformer->transformCollection($nearby_users->all()) : [],

                // 本次获取的总数量
                'count' => count($nearby_users),

                // url
                'url'   => $request -> url() .
                    '?page='.($page+1) .
                    '&adcode=' . $adcode .
                    '&citycode=' . $citycode
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 发现页面热门用户排行榜,共有50个，发现页面先显示14个，详情页再具体显示全部，只是一次性全部传完
     * @return \Illuminate\Http\JsonResponse
     */
    public function ranking()
    {
        try {
            // 判断用户是否为登录状态
            $user_from = Auth::guard('api')->user();

            $users = User::where('status',0)
                -> orderBy('like_count','DESC')
                -> take(50)
                -> get(['id', 'nickname', 'avatar', 'verify', 'signature', 'verify_info']);

            if($user_from) {

                foreach ($users as $value) {

                    $value -> avatar = CloudStorage::downloadUrl($value -> avatar);

                    // 判断登录用户是否关注对方
                    $already_follow = Subscription::ofAttention($user_from->id, $value->id)->first();

                    // 判断对方是否为登录用户粉丝
                    $already_fans = Subscription::ofAttention($value->id, $user_from->id)->first();

                    $value -> already_like = $already_follow ?  ($already_fans ?  '2' : '1') : '0';
                }
            } else {

                foreach($users as $value){
                    $value -> avatar = CloudStorage::downloadUrl($value -> avatar);
                    $value -> already_like = '0';
                }
            }

            return response()->json($users->all(), 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'bad_request'], 403);
        }
    }

    /**
     * 好友推荐 临时测试 待正式确实再改
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function recommend(Request $request)
    {
        try {
            // 获取页码
            $page = $request -> get('page',1);

            // 判断页码，最多5页
            if($page > 5) {

                return response()->json([
                    'data'  => [],
                    'count' => 0,
                    'url'   => ''
                ]);
            }

            // 判断用户是否为登录状态
            $user = Auth::guard('api')->user();

            // 每页数量
            $limit = 10;

            // 获取附近 区县级
            $nearby_users = User::whereHas('hasManyTweet',function ($q) {
                $q->active()->visible();
            })
                -> with(['hasManyTweet' => function($query){
                    $query -> active() -> visible() -> orderBy('id','desc') -> get();
                }])
                -> where('status',0)
                -> orderBy('id','desc')
                -> whereIn('verify',[1,2])
                -> whereNotNull('avatar')
                -> whereNotNull('signature');


            // 如果用户为登录状态，则将自己排除在外
            if($user){
                $nearby_users = $nearby_users -> whereNotIn('id',[$user->id]);
            }

            // 获取集合
            $nearby_users = $nearby_users -> get();

            // 遍历判断动态是否超过5条
            foreach($nearby_users as $key => $value){

                if($value -> hasManyTweet -> count() < 5){

                    $nearby_users -> forget($key);
                }
            }

            // 分页
            $nearby_users = $nearby_users -> forPage($page, $limit) -> values();

            // 判断数量
            if(!$nearby_users->count()){

                return response()->json([
                    'data'  => [],
                    'count' => 0,
                    'url'   => ''
                ]);
            }

            return response()->json([

                // 获取的数据
                'data' => $nearby_users ? $this->nearbyUsersTransformer->transformCollection($nearby_users->all()) : [],

                // 本次获取的总数量
                'count' => count($nearby_users),

                // url
                'url'   => $request -> url() .
                    '?page='.($page+1)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 明星达人 临时测试 待正式确实再改
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function star(Request $request)
    {
        try {
            // 获取页码
            $page = $request -> get('page',1);

            // 判断页码，最多5页
            if(!in_array($page,[1,2,3,4,5])) {

                return response()->json([
                    'data'  => [],
                    'count' => 0,
                    'url'   => ''
                ]);
            }

            // 判断用户是否为登录状态
            $user = Auth::guard('api')->user();

            // 每页数量
            $limit = 10;

            // 获取附近 区县级
            $nearby_users = User::whereHas('hasManyTweet',function ($q) {
                $q->active()->visible();
            })
                -> with(['hasManyTweet' => function($query){
                    $query -> active() -> visible() -> orderBy('id','desc') -> get();
                }])
                -> whereIn('verify',[1,2])
                -> where('status',0)
                -> whereNotNull('avatar')
                -> whereNotNull('signature')
                -> orderBy('id','desc');

            // 如果用户为登录状态，则将自己排除在外
            if($user){

                $nearby_users = $nearby_users -> whereNotIn('id',[$user->id]);
            }

            // 获取集合
            $nearby_users = $nearby_users -> get();

            // 遍历判断动态是否超过5条
            foreach($nearby_users as $key => $value){

                if($value -> hasManyTweet -> count() < 5){

                    $nearby_users -> forget($key);
                }
            }

            // 分页
            $nearby_users = $nearby_users -> forPage($page, $limit) -> values();

            // 判断数量
            if(!$nearby_users->count()){

                return response()->json([
                    'data'  => [],
                    'count' => 0,
                    'url'   => ''
                ]);
            }

            return response()->json([

                // 获取的数据
                'data' => $nearby_users ? $this->nearbyUsersTransformer->transformCollection($nearby_users->all()) : [],

                // 本次获取的总数量
                'count' => count($nearby_users),

                // url
                'url'   => $request -> url() .
                    '?page='.($page+1)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    // 遍历将多个可能为空的数组合并为一个数组
    public function getMerge(&$array,$data){
        // 遍历存值
        if($data){
            foreach($data['data'] as $value){
                $array[] = $value;
            }
        }
    }

    /**
     * 判断请求中条数及时间戳的格式，并将时间戳转格式
     * @param Request $request
     * @return array
     */
    public function transformerTimeAndLimit(Request $request)
    {
        $limit = $request->get('limit');
        $timestamp = $request->get('timestamp');

        $limit = isset($limit) && is_numeric($limit) ? $limit : 20;
        $timestamp = isset($timestamp) && is_numeric($timestamp) ? $timestamp : time();

        // 将获取的时间转格式
        $date = Carbon::createFromTimestamp($timestamp)->toDateTimeString();

        return array($date, $limit);
    }

    public function person(Request $request)
    {
        $nickname = $request->get('username');
        $user = User::where('nickname','=',$nickname)->first();
        return $this->userInfomationTransformer->transform($user);
    }

}