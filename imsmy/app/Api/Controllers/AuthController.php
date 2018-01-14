<?php

namespace App\Api\Controllers;

use App\Api\OAuth\OAuthManager;
use App\Api\Transformer\AuthTransformer;
use App\Library\aliyun\SmsDemo;
use App\Models\Channel;
use App\Models\LocalAuth;
use App\Models\OAuth;
use App\Models\Test\TestUser;
use App\Models\User;
use App\Models\GoldAccount;
use App\Models\UserChannel;
use App\Models\Subscription;
use App\Models\StatisticsUsers;
use App\Models\Tigase\TigUsers;
use App\Models\Tigase\UserJid;
use App\Models\Tigase\TigNodes;
use App\Models\UserToken;
use App\Services\SMSVerify;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Log;
use DB;
use Validator;
use Illuminate\Support\Facades\Cache;
//use EaseMob;  // 暂停环信业务 搜索号：备忘录

use GuzzleHttp\Client;

/**
 * 用户注册、认证
 *
 * @Resource("Users",uri="/users")
 */
class AuthController extends BaseController
{
    private $authTransformer;
    private $usersSelfTransformer;

    public function __construct(AuthTransformer $authTransformer,User $usersSelfTransformer)
    {
        $this->authTransformer = $authTransformer;
        $this->usersSelfTransformer = $usersSelfTransformer;
    }

    /**
     * [短信验证](http://wiki.mob.com/webapi2-0/ "短信验证")
     *
     *
     * @Post("/sms-verify")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("phone",required=true, description="电话号码"),
     *     @Parameter("zone",required=true, description="时区"),
     *     @Parameter("code",required=true, description="验证码"),
     * })
     *  @Transaction({
     *     @Response(200),
     *     @Response(405),
     *     @Response(406),
     *     @Response(456),
     *     @Response(457),
     *     @Response(466),
     *     @Response(467),
     *     @Response(468),
     *     @Response(474),
     * })
     */
    public function smsVerify(Request $request,SMSVerify $verify)
    {
        $data = [
            'phone' => $request->get('phone'),
            'zone'  => $request->get('zone'),
            'code'  => $request->get('code')
        ];
        $response = $verify->verify($data);

        return response()->json('',$response->status);
    }

    /**
     * 用户认证
     *
     * @get("/authenticate?&location=location&phone_id=phone_id{username=username&password=password}||{oauth_name=oauth_name&oauth_id=oauth_id&oauth_access_token=oauth_access_token}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("location",required=false,description="地理位置"),
     *      @Parameter("phone_id",required=true,description="json格式的手机型号、序列号、手机系统版本" 如： {"model":"hjdhfkjsd","serial":"45545","sdk_int":"5.0.0"}"),
     *      @Parameter("username",required=true,description="用户名==>手机号"),
     *      @Parameter("password",required=true,description="密码"),
     *
     *      @Parameter("oauth_name",required=true,description="前两者与后三者可以不同时出现,weibo,weixin,qq"),
     *      @Parameter("oauth_id",required=true,description="oauth_id"),
     *      @Parameter("oauth_access_token",required=true,description="oauth_access_token"),
     * })
     * @Transaction({
     *     @Response(200,body={"id":"用于登录即时通讯的账号","nickname":"nickname","avatar":null,"hash_avatar":null,"token_expire":"有效时间，是数字不是字符串","token":"token"}),
     *     @Response(401,body={"error":"invalid_credentials"}),
     *     @Response(403,body={"error":"invalid_phone_model"}),
     *     @Response(500,body={"error":"could_not_create_token"}),
     * })
     */
    public function authenticate(Request $request)
    {
        try{
            // 判断是否为第三方登录
            if ($request->has(['oauth_name','oauth_id','oauth_access_token'])) {
                $oauth_name         = $request->get('oauth_name');
                $oauth_id           = $request->get('oauth_id');
                $oauth_access_token = $request->get('oauth_access_token');

                // 获取当前时间
                $date = Carbon::now()->toDateTimeString();
                try {
                    // 通过匹配用户输入信息从表中获取用户数据
                    $auth = User::WhereHas('hasManyOAuth',function($q) use ($oauth_name, $oauth_id, $oauth_access_token, $date) {
                            $q->where('oauth_name',$oauth_name)
                                ->where('oauth_id',$oauth_id)
                                ->where('oauth_access_token',$oauth_access_token)
                                ->where('oauth_expires','>=',$date);
                        })->firstOrFail();

                    // 通过获取的用户信息生成token
                    $token = JWTAuth::fromUser($auth);

                } catch (ModelNotFoundException $e) {
                    return response()->json(['error' => 'invalid_credentials'],401);
                }
            } else {
                try {
                    // 获取用户名及phone_id
                    $username = $request->get('username');

                    // 通过用户名获取用户信息，如果没有则返回错误信息
                    $auth = User::whereHas('hasOneLocalAuth',function ($q) use ($username) {
                        $q->where('username',$username);
                    })->firstOrFail();

                    // 获取验证所需要的密码及生成凭证token需要的数据
                    $credentials = [
                        'id' => $auth->id,                          // 用户id
                        'password' => $request->get('password')     // 用户输入的密码
                    ];

                    // attempt to verify the credentials and create a token for the user
                    // 验证用户登录信息，如果验证成功则生成唯一凭证token，否则返回错误
                    if (! $token = JWTAuth::attempt($credentials)) {

                        return response()->json(['error' => 'invalid_credentials'], 401);
                    }

                } catch (ModelNotFoundException $e) {
                    return response()->json(['error' => 'invalid_credentials'],401);
                } catch (JWTException $e) {
                    // something went wrong whilst attempting to encode the token
                    return response()->json(['error' => 'could_not_create_token'], 500);
                }
            }

            // 通过验证接收的手机本身信息，判断用户是否更换手机登录APP，需要再完善为空的操作
            $phone_id = $request -> get('phone_id');

            // 判断接收信息是否为空，如果不为空，执行下面操作
            if($phone_id){

                // 将json格式的 phone_id 解析成变量
                $phone_data = json_decode($phone_id,true);

                // 匹配手机型号及序列号是否与数据库所存一致,不一致则返回错误，需要验证短信验证码才能进行登录
                if($auth->phone_model != $phone_data['model'] || $auth->phone_serial != $phone_data['serial']){

                    // 判断缓存中是否有此用户名，有说明已通过短信验证，为空则返回403错误
                    if(null === Cache::get('SMS'.$request->get('username'))){

                        // 如果手机信息不匹配，也没有通过手机验证，则返回手机信息不一致的错误提示
                        return response()->json(['error' => 'invalid_phone_model'], 403);
                    }

                    // 将用户新的手机信息写入集合
                    $auth->phone_model = $phone_data['model'];
                    $auth->phone_serial = $phone_data['serial'];
                    $auth->phone_sdk_int = $phone_data['sdk_int'];

                    // 清除缓存中的用户数据
                    Cache::forget('SMS'.$request->get('username'));
                }
            }

            //记录登陆信息
            DB::table('user_login_log')->insert([
                'user_id'       =>  $auth->id ?: '',
                'login_time'    =>  time(),
                'way'           =>  $request->get('phone_type') ?: '',
                'ip'            =>  getIP() ?: null,
            ]);

            // 获取用户地理位置信息
            $location = $request -> get('location','');

            // 将地理位置信息存入user表
            $auth -> location = $location;

            // 更新 last_token
            $auth->last_token = new Carbon;

            // 保存
            $auth->save();

            // 将用户登录的地理位置信息存入日志文件
//            Log::info('User successful to login.', ['User_ID' => $auth->id,'IP' => getIP(),'Location' => $location]);

            // 保存token
            $auth->token = $token;

            // 获取用户关注人数
            $auth -> attention = Subscription::where('from',$auth->id)->count();

//            UserToken::create([
//                'user_id'   =>  $auth->id,
//                'token'     =>  $auth->token,
//                'create_time'   => time(),
//            ]);

            // all good so return the token
            return response()->json($this->authTransformer->transform($auth));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'invalid_credentials'],401);
        } catch(\Exception $e){
            return response()->json(['error'=>'bad_request'],$e->getCode());
        }
    }

    /**
     * 刷新token 方式1
     *
     * @return \Illuminate\Http\JsonResponse
     */
//    public function refresh(){
//
//        return ['status' => 'ok'];
//    }

    /**
     * 刷新token 方式2
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(){

        try {
            $old_token = JWTAuth::getToken();
            $token = JWTAuth::refresh($old_token);
//            JWTAuth::invalidate($old_token);
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json(compact('token'));
    }

    /**
     * 用户名是否已存在
     *
     * @Get("/check")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request("username=用户名"),
     *     @Response(200,body={"status":"OK"}),
     *     @Response(400,body={"error":"invalid_credentials"}),
     *     @Response(401,body={"error":"user_already_exists"}),
     *     @Response(408,body={"error"})
     * })
     */
    public function check(Request $request)
    {
        try{
            // 获取用户名
            $username = (int)$request -> get('username');

            // 如果用户已存在，将抛出错误
            if(LocalAuth::where('username',$username)->first()){
                throw new \Exception('user_already_exists',401);
            }

            // 如果没有被注册，返回status
            return response()->json(['status'=>'OK']);

        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 用户注册
     *
     * @Post("/register")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request("username=用户名&password=密码&location=地理位置&phone_id=json格式的手机型号、序列号、手机系统版本 如： {"model":"hjdhfkjsd","serial":"45545","sdk_int":"5.0.0"}"),
     *     @Response(201,body={"id":"用于登录即时通讯的账号","nickname":"nickname","token_expire":"有效时间，是数字不是字符串","token":"token"}),
     *     @Response(400,body={"error":"invalid_credentials"}),
     *     @Response(401,body={"error":"user_already_exists"}),
     *     @Response(408,body={"error"})
     * })
     */
    public function register(Request $request)
    {
        try{
            // 获取用户名和密码,手机信息，地理位置
            $username = (int)$request -> get('username');
            $password = trim($request -> get('password'));
            $phone_id = trim($request -> get('phone_id'));
            $location = removeXSS($request -> get('location'));

            // 判断,
            if(!regex_pwd($password) || strlen($username)>20) throw new \Exception('bad_request',403);

            // 如果用户已注册，将抛出错误
            if(LocalAuth::where('username',$username)->first()){
                throw new \Exception('user_already_exists',401);
            }

            // 判断缓存中是否有此用户名，有说明已通过短信验证，为空则抛出错误
            if(null == Cache::get('SMS'.$request->get('username'))){
              //  throw new \Exception('request_timeout',408);
            }

            $time = getTime();

            // 开启事务
            DB::beginTransaction();

            // 将用户信息存入 user 表
            $user = User::create([
                'last_token' => new Carbon,
                'location'   => $location,
                'is_phonenumber' => 1,
            ]);

            // 判断接收信息是否为空，如果不为空，执行下面操作  用于下次判断用户是否更换手机登录APP，需要再完善为空的操作
            if($phone_id) {

                // 将json格式的 phone_id 解析成变量
                $phone_data = json_decode($phone_id, true);

                // 将用户信息存入 user 集合
                $user -> phone_model = $phone_data['model'];
                $user -> phone_serial = $phone_data['serial'];
                $user -> phone_sdk_int = $phone_data['sdk_int'];
            }

            //TODO  逻辑问题严重，需要改！！！
            // 自动生成用户的昵称，目前采用用户的id
            $user->nickname = 'ZhuiXi_' . $user->id;

            //TODO 邀请码 ==> 目前办法 sprintf('%X',crc32(microtime().'ID'))

            // 保存用户信息
            $user->save();

            // 生成token
            $token = JWTAuth::fromUser($user);

            // 对密码进行哈希加密
            $password_new = bcrypt($password);

            // 将信息存入 local_auth 表
            LocalAuth::create([
                'user_id'       => $user->id,
                'username'      => $username,
                'password'      => $password_new
            ]);

            //添加到测试用户表
            TestUser::create([
                'id'    =>  $user->id,
                'name'  =>  $username,
                'password'  =>  $password_new,
            ]);

            //添加注册时，要给用户添加所有频道
            $channels_data = Channel::active()->get()->pluck('id')->all();

            // 处理成字符串
            $channels = implode(',',$channels_data);

            # TODO 初始化表中数据 Start
            // 为新注册用户添加频道信息
            UserChannel::create([
                'user_id'       => $user->id,
                'channel_id'    => $channels,
                'time_add'      => $time,
                'time_update'   => $time
            ]);

            // 为用户添加至 gold_account 用户金币表
            GoldAccount::create([
                'user_id'       => $user->id,
                'time_add'      => $time,
                'time_update'   => $time
            ]);

            // 为用户创建 statistics_users 表中数据
            StatisticsUsers::create([
                'user_id'           => $user->id,
                'time_add'          => $time,
                'time_update'       => $time
            ]);

            # TODO 初始化表中数据 End

            //添加IM用户信息
//            EaseMob::createUser($user->id,$password);  // 暂停环信业务 搜索号：备忘录

            # TODO 即时通讯 Start
            // 将用户相关信息存入tig_user表 对用户名进行sha1加密
            $sha1_user_id = sha1($user->id.'@goobird');
            $tig_user_data = [
                'user_id' => $user->id.'@goobird',
                'sha1_user_id' => $sha1_user_id,
                'user_pw' => $password_new,
                'acc_create_time' => new Carbon(),
            ];

            $tigase_user = DB::table('tig_users')->insertGetId($tig_user_data);

            // 将用户信息存入user_jid表
//            UserJid::create([
//                'jid_sha' => sha1($user->id.'@goobird'),
//                'jid' => $user->id.'@goobird'
//            ]);

            // 将用户信息存入user_jid表，返回jid_id
            $jid_id = DB::table('user_jid')->insertGetId([
                'jid_sha' => $sha1_user_id,
                'jid' => $user->id.'@goobird',
            ]);

            // 将用户信息存入 tig_nodes 表
            // 1. node字段值为 root
            $nid_root = DB::table('tig_nodes')->insertGetId([
                'uid' => $tigase_user,
                'node' => 'root',
            ]);

            // 2. node字段值为 privacy
            $nid_privacy = DB::table('tig_nodes')->insertGetId([
                'uid' => $tigase_user,
                'parent_nid' => $nid_root,
                'node' => 'privacy',
            ]);

            // 3. node字段值为 invisible
            $nid_invisible = DB::table('tig_nodes')->insertGetId([
                'uid' => $tigase_user,
                'parent_nid' => $nid_privacy,
                'node' => 'invisible',
            ]);

            // 将管理员 100000@goobird 信息写入用户的 tig_pairs 表
            // 1. pkey字段值为 roster
            DB::table('tig_pairs')->insert([
                'nid' => $nid_root,
                'uid' => $tigase_user,
                'pkey' => 'roster',
                'pval' => "<contact jid='100000@goobird' preped='simple' weight='1.0' activity='1.0' subs='both' last-seen=".time()." name='100000'/>",
            ]);

            // 2. pkey字段值为 privacy-list
            DB::table('tig_pairs')->insert([
                'nid'  => $nid_invisible,
                'uid'  => $tigase_user,
                'pkey' => 'privacy-list',
                'pval' => '<list name="invisible"><item action="deny" order="1"><presence-out/></item></list>',
            ]);

            // 管理员 方面写入用户信息 tig_pairs 表 100000@goobird pkey字段值为 roster
            $pval = DB::table('tig_pairs')->where('nid','811')->first();

            DB::table('tig_pairs')->where('nid','811')->update([
                'pval' => $pval->pval."<contact jid='".$user->id."@goobird' preped='simple' weight='1.0' activity='1.0' subs='both' last-seen=".$time." name='".$user->id."'/>",
            ]);

            // 将注册成功信息写入 msg_history 表
            $time_now = Carbon::now()->toDateTimeString();  // 获取格式化后的时间
            DB::table('msg_history')->insert([
                'ts'           => $time_now,
                'sender_uid'   => 80,
                'receiver_uid' => $jid_id, // user_jid表中的jid_id
                'msg_type'     => 1,
                'message'      => '<message to="'.$user->id.'@goobird" type="chat" id="ZT3lV-23" xmlns="jabber:client" from="100000@goobird/Smack"><body>{&quot;content&quot;:&quot;欢迎使用！&quot;,&quot;time&quot;:&quot;'.$time_now.' +0800&quot;,&quot;type&quot;:0}</body><thread>70131a01-4ab7-4f95-98ed-cca8e2505b97</thread><delay from="goobird" stamp="2016-11-19T05:58:21.994Z" xmlns="urn:xmpp:delay">Offline Storage - localhost</delay></message>'
            ]);
            # TODO 即时通讯 End

            //删除缓存中的验证信息
            Cache::forget('SMS'.$username);

            // 事务提交
            DB::commit();

            $user->token = $token;
            $user->tigase = $password_new;

            // 存入日志
            \Log::info('User successful to register.', [
                'User_id' => $user->id,
                'Username' => $username,
                'IP' => getIP(),
                'Channels' => $channels,
                'Location' => $location
            ]);

            return response()->json($this->authTransformer->transform($user),201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }

    }

    /**
     * 重置密码
     *
     * @Post("/reset-password")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request("username=用户名&password=密码"),
     *     @Response(201,body={"id":"用于登录即时通讯的账号","nickname":"nickname","token_expire":"有效时间，是数字不是字符串","token":"token"}),
     *     @Response(400,body={"error":"invalid_credentials"}),
     *     @Response(404,body={"error":"user_not_found"}),
     *     @Response(408,body={"error":"request_timeout"})
     * })
     */
    public function passwordReset(Request $request)
    {
        try{
            // 获取用户输入的用户名和密码
            $username = $request->get('username');
            $password = $request->get('password');

            // 判断密码格式
            if(!regex_pwd($password) || strlen($username)>20) throw new \Exception('bad_request',403);

            // 开启事务
            DB::beginTransaction();

            // 判断缓存中是否有此用户名，如果为空则抛出错误
            if(null == Cache::get('SMS'.$username)){
                throw new \Exception('request_timeout',408);
            }

            // 从 local_auth 表中获取相应用户名的信息
            $auth = LocalAuth::where('username',$username)->firstOrFail();

            // 哈希加密
            $new_password = bcrypt($password);

            // 新密码
            $auth->password = $new_password;

            // 保存用户修改后的密码信息
            $auth->save();

            // 将用户信息保存至xmpp表中
            DB::table('tig_users')->where('user_id',$auth->user_id.'@goobird')->update(['user_pw'=>$new_password]);

            // 设置last_token为最新时间
            User::findOrFail($auth->user_id) -> update(['last_token' => new Carbon]);

            $user = User::find($auth->user_id);
            // 生成新的 token
            $token = JWTAuth::fromUser($user);

//            EaseMob::resetPassword($user->id,$password); //暂停环信业务

            // 删除缓存中的用户名信息
            Cache::forget('SMS'.$username);

            // 提交事务
            DB::commit();

            // 将token存入user集合中
            $user->token = $token;

            // 返回
            return response()->json($this->authTransformer->transform($user),201);

        } catch (ModelNotFoundException $e){

            DB::rollBack();
            return response()->json(['error' => 'user_not_found'],404);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 第三方登录
     * @Post("/third-party-auth")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("oauth_name",required=true,description="登录方式weibo\weixin\qq"),
     *     @Parameter("oauth_id",required=true,description="第三方返回的oauth_id"),
     *     @Parameter("oauth_access_token",required=true,description="第三方返回的oauth_access_token"),
     *     @Parameter("oauth_expires",required=true,description="第三方返回的oauth_expires"),
     *     @Parameter("oauth_nickname",required=true,description="第三方返回的oauth_nickname"),
     * })
     * @Transaction({
     *     @Request(body={
     *                  "oauth_name":"qq",
     *                  "oauth_id":"id",
     *                  "oauth_access_token":"access_token",
     *                  "oauth_expires":15668794,
     *                  "oauth_nickname":"nickname",
     *                  "location":"location",
     *                  "phone_id":"phone_id"
     *     }),
     *     @Response(201,body={"id":"用于登录即时通讯的账号及密码","nickname":"nickname","token_expire":"有效时间，是数字不是字符串","token":"token"}),
     *     @Response(400,body={"error":"bad_request"}),
     *     @Response(500,body={"error":"unknown error"})
     * })
     */
    public function thirdPartyAuth(Request $request)
    {
        try {
            // 验证提交的信息
            $validator = Validator::make($request->all(), [
                'oauth_name'         => 'required | in:weibo,weixin,qq',
                'oauth_id'           => 'required',
                'oauth_access_token' => 'required',
                'oauth_expires'      => 'required',
                'oauth_nickname'     => 'required',
            ]);

            // 验证失败，返回错误信息
            if ($validator->fails()) {
                throw new \Exception('bad_request',400);
            }

            $time = getTime();
            $now = new Carbon();

            // 获取所有输入数据
            $input = $request->all();

            $oauth_mgr = new OAuthManager($input['oauth_name']);

            $result = $oauth_mgr->verify($input['oauth_id'],$input['oauth_access_token']);

            if (! $result) {
                throw new \Exception('bad_request',400);
            }

            // 判断用户是否已经存在
            $user = User::with('hasManyOAuth')->WhereHas('hasManyOAuth',function($q) use ($input) {
                $q->where('oauth_name',$input['oauth_name'])
                    ->where('oauth_id',$input['oauth_id']);
            })->first();

            // 如果用户可以注册，执行将数据存入数据库的操作
            if ($user === null) {

                // 开启事务
                DB::beginTransaction();

                // 将用户信息存入 user 表
                $user = User::create([
                    'last_token' => $now,
                    'nickname' => $input['oauth_nickname'],
                    'location' => $request->get('location'),
                    'is_thirdparty' => 1,
                ]);

                // 判断接收信息是否为空，如果不为空，执行下面操作  用于下次判断用户是否更换手机登录APP，需要再完善为空的操作
                if($request->get('phone_id')) {

                    // 将json格式的 phone_id 解析成变量
                    $phone_data = json_decode($input['phone_id'], true);

                    // 将用户信息存入 user 集合
                    $user -> phone_model = $phone_data['model'];
                    $user -> phone_serial = $phone_data['serial'];
                    $user -> phone_sdk_int = $phone_data['sdk_int'];
                }

                // 保存
                $user->save();

                // 生成token
//                $user->token = JWTAuth::fromUser($user);

                // 将用户信息存入 oauth 表中
                OAuth::create([
                    'user_id'            => $user->id,
                    'oauth_name'         => $input['oauth_name'],
                    'oauth_id'           => $input['oauth_id'],
                    'oauth_access_token' => $input['oauth_access_token'],
                    'oauth_expires'      => Carbon::createFromTimestampUTC($input['oauth_expires'])
                ]);

                //添加注册时，要给用户添加所有频道
                $channels_data = Channel::active()->pluck('id')->all();

                // 处理成字符串
                $channels = implode(',',$channels_data);

                // 存入日志
                \Log::info('Register => oauth_id: ' . $input['oauth_id'] . 'Location: '.$request -> get('location'));
                \Log::info($channels);

                // 为新注册用户添加频道信息
                UserChannel::create([
                    'user_id'       => $user->id,
                    'channel_id'    => $channels,
                    'time_add'      => $time,
                    'time_update'   => $time
                ]);

                //添加IM用户信息
//                EaseMob::createUser($user->id,$input['oauth_id']); // 暂停环信业务

                # 将用户信息存入tigase表中 start
                // 将用户相关信息存入tig_user表
                $sha1_user_id = sha1($user->id.'@goobird');
                $tig_user_data = [
                    'user_id' => $user->id.'@goobird',
                    'sha1_user_id' => $sha1_user_id,
                    'user_pw' => bcrypt($user->id),
                    'acc_create_time' => $now,
                ];

                $tigase_user = DB::table('tig_users')->insertGetId($tig_user_data);

                // 将用户信息存入user_jid表
//            UserJid::create([
//                'jid_sha' => sha1($user->id.'@goobird'),
//                'jid' => $user->id.'@goobird'
//            ]);

                // 将用户信息存入user_jid表，返回jid_id
                $jid_id = DB::table('user_jid')->insertGetId([
                    'jid_sha' => $sha1_user_id,
                    'jid' => $user->id.'@goobird',
                ]);

                // 将用户信息存入 tig_nodes 表
                // 1. node字段值为 root
//                $nid_root = DB::table('tig_nodes')->insertGetId([
//                    'uid' => $tigase_user,
//                    'node' => 'root',
//                ]);

                // 2. node字段值为 privacy
//                $nid_privacy = DB::table('tig_nodes')->insertGetId([
//                    'uid' => $tigase_user,
//                    'parent_nid' => $nid_root,
//                    'node' => 'privacy',
//                ]);

                // 3. node字段值为 invisible
//                $nid_invisible = DB::table('tig_nodes')->insertGetId([
//                    'uid' => $tigase_user,
//                    'parent_nid' => $nid_privacy,
//                    'node' => 'invisible',
//                ]);

                // 将管理员 100000@goobird 信息写入用户的 tig_pairs 表
                // 1. pkey字段值为 roster
//                DB::table('tig_pairs')->insert([
//                    'nid' => $nid_root,
//                    'uid' => $tigase_user,
//                    'pkey' => 'roster',
//                    'pval' => "<contact jid='100000@goobird' preped='simple' weight='1.0' activity='1.0' subs='both' last-seen=".time()." name='100000'/>",
//                ]);

                // 2. pkey字段值为 privacy-list
//                DB::table('tig_pairs')->insert([
//                    'nid'  => $nid_invisible,
//                    'uid'  => $tigase_user,
//                    'pkey' => 'privacy-list',
//                    'pval' => '<list name="invisible"><item action="deny" order="1"><presence-out/></item></list>',
//                ]);

                // 管理员 方面写入用户信息 tig_pairs 表 100000@goobird pkey字段值为 roster
//                $pval = DB::table('tig_pairs')->where('nid','811')->first();

//                DB::table('tig_pairs')->where('nid','811')->update([
//                    'pval' => $pval->pval."<contact jid='".$user->id."@goobird' preped='simple' weight='1.0' activity='1.0' subs='both' last-seen=".time()." name='".$user->id."'/>",
//                ]);

                // 将注册成功信息写入 msg_history 表
//                $time_now = Carbon::now()->toDateTimeString();  // 获取格式化后的时间，使用两次
//                DB::table('msg_history')->insert([
//                    'ts'           => $time_now,
//                    'sender_uid'   => 80,
//                    'receiver_uid' => $jid_id, // user_jid表中的jid_id
//                    'msg_type'     => 1,
//                    'message'      => '<message to="'.$user->id.'@goobird" type="chat" id="ZT3lV-23" xmlns="jabber:client" from="100000@goobird/Smack"><body>{&quot;content&quot;:&quot;欢迎使用！&quot;,&quot;time&quot;:&quot;'.$time_now.' +0800&quot;,&quot;type&quot;:0}</body><thread>70131a01-4ab7-4f95-98ed-cca8e2505b97</thread><delay from="goobird" stamp="2016-11-19T05:58:21.994Z" xmlns="urn:xmpp:delay">Offline Storage - localhost</delay></message>'
//                ]);
                # 将用户信息存入tigase表中 end

                // 为用户添加至 zx_user_golds 表
                GoldAccount::create([
                    'user_id'       => $user->id,
                    'time_add'      => $time,
                    'time_update'   => $time
                ]);

                // 为用户创建 statistics_users 表中数据
                StatisticsUsers::create([
                    'user_id'           => $user->id,
                    'time_add'          => $time,
                    'time_update'       => $time
                ]);

                DB::commit();
            } else {
                $oauth = OAuth::where('user_id',$user->id)->first();
                $oauth->oauth_access_token = $input['oauth_access_token'];
                $oauth->oauth_expires      = Carbon::createFromTimestampUTC($input['oauth_expires']);
                $oauth->save();
            }

            $user->last_token = $now;
            $user->save();
            $user->token = JWTAuth::fromUser($user);
            // 获取用户关注人数
            $user -> attention = Subscription::where('from',$user->id)->count();

            return response()->json($this->authTransformer->transform($user),201);
        } catch (\Exception $e) {
            \Log::info($e);
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 绑定第三方登录
     * @param $id   用户id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function thirdRelatedAdd($id,Request $request)
    {
        try{
            // 验证提交的信息
            $validator = Validator::make($request->all(), [
                'oauth_name'         => 'required | in:weibo,weixin,qq',
                'oauth_id'           => 'required',
                'oauth_access_token' => 'required',
                'oauth_expires'      => 'required',
                'oauth_nickname'     => 'required',
            ]);

            // 验证失败
            if($validator->fails())
                throw new \Exception('bad_request',400);

            // 获取所有输入数据
            $input = $request->all();

            $oauth_mgr = new OAuthManager($input['oauth_name']);

            $result = $oauth_mgr->verify($input['oauth_id'],$input['oauth_access_token']);

            if (! $result) {
                throw new \Exception('bad_request',400);
            }

            // 如果已经存在，则返回错误信息
            if (OAuth::where('oauth_name',$input['oauth_name'])->where('oauth_id',$input['oauth_id'])->first())
                return response()->json(['error'=>'already_exists'],403);

            // 将用户信息存入 oauth 表中
            $user = OAuth::create([
                'user_id'            => $id,
                'oauth_name'         => $input['oauth_name'],
                'oauth_nickname'     => $input['oauth_nickname'],
                'oauth_id'           => $input['oauth_id'],
                'oauth_access_token' => $input['oauth_access_token'],
                'oauth_expires'      => Carbon::createFromTimestampUTC($input['oauth_expires'])
            ]);

            return response()->json(['user'=>$user],201);

        } catch (ModelNotFoundException $e){
            return response()->json(['error'=>'bad_request'],400);
        } catch (\Exception $e){
            return response()->json(['error'=>'bad_request'],400);
        }
    }

    /**
     * 解绑第三方登录
     * @param $id   用户id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function thirdRelatedDelete($id,Request $request)
    {
        try{

            // 获取要解除的登录方式
            $oauth_name = $request -> get('oauth_name');

            // 判断用户是否已经存在
            $user = User::with('hasManyOAuth','hasOneLocalAuth')
                ->whereHas('hasManyOAuth',function($q) use ($oauth_name) {
                    $q->where('oauth_name',$oauth_name);
                }) -> findOrFail($id);

            // 判断是否为唯一绑定账户
            if(!$user->hasOneLocalAuth){

                // 第三方绑定的数量是否不超过一个
                if($user->hasManyOAuth->count() <=1 ){
                    return response()->json(['error'=>'only_account'],403);
                }
            }

            // 删除用户第三方登录信息
            OAuth::where('user_id',$id)
                -> where('oauth_name',$oauth_name)
                -> first()
                -> delete();

            return response()->json(['status'=>'ok'],201);

        } catch (ModelNotFoundException $e){
            return response()->json(['error'=>'bad_request'],400);
        } catch (\Exception $e){
            return response()->json(['error'=>'bad_request'],400);
        }
    }

    /**
     * 用户账号明细管理
     * @param $id   用户id
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountManage($id)
    {
        try{

            // 判断用户是否已经存在
            $user = User::with('hasManyOAuth','hasOneLocalAuth') -> where('id',$id) -> firstOrFail(['id']);

            $data = [
                'qq'     => ['status' => 0, 'nickname' => '',],
                'weibo'  => ['status' => 0, 'nickname' => '',],
                'weixin' => ['status' => 0, 'nickname' => '',],
                'phone'  => ['status' => 0, 'nickname' => '',],
            ];

            // 第三方登录的账号管理
            if($user->hasManyOAuth->count()){

                foreach($user->hasManyOAuth as $oauth){
                    $data[$oauth -> oauth_name] = [
                        'status' => 1,
                        'nickname'  => $oauth -> oauth_nickname,
                    ];
                }
            }

            // 手机号的账号管理
            if($user->hasOneLocalAuth){

                $data['phone'] = [
                    'status' => 1,
                    'nickname' => $user->hasOneLocalAuth -> username,
                ];
            }

            return response()->json(['data'=>$data],200);

        } catch (ModelNotFoundException $e){
            return response()->json(['error'=>'bad_request'],400);
        } catch (\Exception $e){
            return response()->json(['error'=>'bad_request'],400);
        }
    }

    /**
     * 通过token获取用户信息
     *
     * @Get("/me")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={"user":{"id": 1,"username": "goo001","created_at": "2016-03-05 10:09:33","updated_at": "2016-03-05 10:09:33"}}),
     *     @Response(404,body={"error":"user_not_found"}),
     *     @Response(400,body={"error":"token_expired"}),
     *     @Response(400,body={"error":"token_invalid"}),
     *     @Response(400,body={"error":"token_absent"})
     * })
     */
    public function getAuthenticatedUser(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        // 获取
        // the token is valid and we have found the user via the sub claim
        return response()->json($this->usersSelfTransformer->transform($user));
    }

    /**
     * 修改手机号
     *
     * @Post("/reset-phone")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request("username=用户名&phone=新手机号"),
     *     @Response(201,body={"id":"用于登录即时通讯的账号","nickname":"nickname","token_expire":"有效时间，是数字不是字符串","token":"token"}),
     *     @Response(400,body={"error":"invalid_credentials"}),
     *     @Response(404,body={"error":"user_not_found"}),
     *     @Response(408,body={"error":"request_timeout"})
     * })
     */
    public function phoneReset(Request $request)
    {
        try{
            // 获取用户输入的用户名和需要更换的手机号
            $username = (int)$request->get('username');
            $phone = (int)$request->get('phone');

            // 判断格式
            if(strlen($phone)>20 || strlen($username)>20) throw new \Exception('bad_request',403);

            // 判断该手机号是否已经注册过
            if(LocalAuth::where('username',$phone)->first())
                throw new \Exception('phone_already_exists',409);

            // 判断缓存中是否有此用户名，如果为空则抛出错误
            if(null == Cache::get('SMS'.$phone)){
                throw new \Exception('request_timeout',408);
            }

            // 从 local_auth 表中获取相应用户名的信息
            $auth = LocalAuth::where('username',$username)->firstOrFail();

            // 开启事务
            DB::beginTransaction();

            // 新用户名
            $auth->username = $phone;

            // 保存用户修改后的密码信息
            $auth->save();

            // 设置last_token为最新时间
            $user = User::findOrfail($auth->user_id) -> update(['last_token' => new Carbon]);

            // 生成新的 token
            $token = JWTAuth::fromUser($user);
//            EaseMob::resetPassword($user->id,$password); //暂停环信业务

            // 删除缓存中的用户名信息
            Cache::forget('SMS'.$phone);

            // 提交事务
            DB::commit();

            // 将token存入user集合中
            $user->token = $token;

            // 返回
            return response()->json($this->authTransformer->transform($user),201);

        } catch (ModelNotFoundException $e){

            DB::rollBack();
            return response()->json(['error' => 'user_not_found'],404);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 修改phone_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function phoneinfo(Request $request)
    {
        try{
            // 获取用户名及phone_id
            $username = (int)$request->get('username');

            // 通过用户名获取用户信息，如果没有则返回错误信息
            $auth = User::whereHas('hasOneLocalAuth',function ($q) use ($username) {
                $q->where('username',$username);
            })->first();

            $phone_id = $request->get('phone_id');

            if($phone_id){

                // 将json格式的 phone_id 解析成变量
                $phone_data = json_decode($phone_id,true);

                // 匹配手机型号及序列号是否与数据库所存一致,不一致则返回错误，需要验证短信验证码才能进行登录
                if($auth->phone_model != $phone_data['model'] || $auth->phone_serial != $phone_data['serial']){

                    // 将用户新的手机信息写入集合
                    $auth->phone_model = $phone_data['model'];
                    $auth->phone_serial = $phone_data['serial'];
                    $auth->phone_sdk_int = $phone_data['sdk_int'];
                    $res = $auth->save();
                }
                if ($res){
                    return response()->json(['message'=>'Success'],200);
                }
            }

        } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    public function sendCode(Request $request)
    {
        $username = $request ->get('username');
        //生成验证码
        $code = mt_rand(1000,9999);

        //将验证码放入缓存
        Cache::put('SMS'.$request->get('username'),$code,'5');

        //将验证码发送给用户
        $response = SmsDemo::sendSms(
            "嗨视频",
            "SMS_110830042",
            $username,
            Array(
                "code"=>$code,
                "product"=>"dsd"
            )
        );

        if($response->Message == 'OK'){
 		return response()->json(['message'=>'Send success'],200);
         }else{
            return response()->json(['Send failure']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifycode(Request $request)
    {
        try {

            if(!is_numeric($code = $request->get('code'))){
                return response()->json(['message'=>'bad_request'],403);
            }

            $code_s = Cache::get('SMS' . $request->get('username'));

            if($code_s == $code){
                return response()->json(['message'=>'success'],200);
            }else{
                return response()->json(['message'=>'failed'],403);
            }

        }catch (\Exception $e){
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }

    }

}






