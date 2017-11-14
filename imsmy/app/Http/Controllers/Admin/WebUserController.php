<?php
/**
 * Created by PhpStorm.
 * User: 马骉
 * Date: 2016/3/7
 * Time: 20:35
 */

namespace App\Http\Controllers\Admin;

//use App\Http\Transformer\ProfileTransformer;
//use App\Http\Transformer\UsersTransformer;
use App\Http\Controllers\Controller;
use App\Models\UserManageLog;
use App\Models\OAuth;
use App\Models\LocalAuth;
use App\Models\GoldAccount;
use App\Models\StatisticsUsers;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Storage;
use CloudStorage;
use JWTAuth;
use DB;
use Auth;
use Illuminate\Support\Facades\Cache;

class WebUserController extends BaseSessionController
{

//    protected $usersTransformer;
//
//    protected $profileTransformer;
//
//    public function __construct(UsersTransformer $usersTransformer, ProfileTransformer $profileTransformer)
//    {
//        $this->usersTransformer = $usersTransformer;
//        $this->profileTransformer = $profileTransformer;
//    }

    protected $page = 10;

    /**
     *   本地用户列表get方式
     */
    public function local(Request $request)
    {

        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search',''));

        // 从user表中取出一定数量的数据
        if($search){

            // 条件
            switch($condition){
                // id
                case 1:
                    $users = User::whereHas('hasOneLocalAuth',function($q) use ($search){
                        $q -> where('user_id','like','%'.$search.'%');
                    });
                    break;
                // 手机号
                case 2:
                $users = User::whereHas('hasOneLocalAuth',function($q) use ($search){
                        $q -> where('username','like','%'.$search.'%');
                    });
                    break;
                // 昵称
                case 3:
                    $users = User::has('hasOneLocalAuth')->where('nickname','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }

            $users = $users -> orderBy('id','DESC')
                            -> status()
                            -> paginate((int)$request->input('num',10));

        }else{

            $users = User::has('hasOneLocalAuth')
                ->orderBy('id','DESC')
                ->status()
                ->paginate((int)$request->input('num',10));
        }

        // 遍历集合，从local_user表中取数据
        $users->each(function($user){
            $info = LocalAuth::where('user_id',$user->id)->first();
            $user -> username = $info->username;
            $user -> user_id = $info->user_id;
        });

        // 用下面方法，分页就不太好做，暂时停用，
//        $users = $this->profileTransformer->transformCollection($users->all());

        // 搜索条件
        $cond = [1=>'ID',2=>'用户名',3=>'昵称'];

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$request->input('num',10),
            'search'=>$request->input('search',''),
        ];
        return view('/admin/management/user/local',['users'=>$users,'request'=>$res,'condition'=>$cond]);
    }

    /**
     *   第三方用户列表get方式
     */
    public function oauth(Request $request)
    {
        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search',''));

        // 从user表中取出一定数量的数据
        if($search){

            // 条件
            switch($condition){
                case 1:
                    $users = User::whereHas('hasManyOAuth',function($q) use ($search){
                        $q -> where('user_id','like','%'.$search.'%');
                    });
                    break;
                case 2:
                    $users = User::whereHas('hasManyOAuth',function($q) use ($search){
                        $q -> where('oauth_name','like','%'.$search.'%');
                    });
                    break;
                case 3:
                    $users = User::has('hasManyOAuth')->where('nickname','like','%'.$search.'%');
                    break;
                case 4:
                    $users = User::whereHas('hasManyOAuth',function($q) use ($search){
                        $q -> where('oauth_id','like','%'.$search.'%');
                    });
                    break;
                default:
                    return back();
            }

            $users = $users -> orderBy('id','DESC')
                -> status()
                -> paginate((int)$request->input('num',10));

        }else{

            $users = User::has('hasManyOAuth')
                ->orderBy('id','DESC')
                ->status()
                ->paginate((int)$request->input('num',10));
        }

        // 遍历集合，从 oauth 表中取数据
        $users->each(function($user){
            $info = OAuth::where('user_id',$user->id)->first();
            $user -> oauth_name = $info->oauth_name;
            $user -> oauth_id = $info->oauth_id;
            $user -> user_id = $info->user_id;
        });


//        dd($users);
//
//        // where条件，后期加搜索条件
//        $where = [['status','<>','1']];
//        $userService = new UserService();
//
//        // 从local_auth表中索引数据
//        $data = $userService->selectOauthListPage($where, ['id','DESC'],$request->input('num',10));
//
//        // 定义空数组
//        $info = [];
//
//        foreach($data as $k=>$v){
//
//            // 查询oauth表中数据
//            $dat = $userService->selectOneById($v->user_id,'nickname,avatar,hash_avatar,video_avatar,sex,signature,background,location');
//
//            // 对象转数组
//            $dat = objectToArray($dat);
//            $v = objectToArray($v);
//
//            // 合并数组
//            $info[$k] = array_merge($v,$dat);
//        }

        // 搜索条件
        $cond = [1=>'ID',2=>'登录方式',3=>'昵称',4=>'第三方id'];

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$request->input('num',10),
            'search'=>$request->input('search',''),
        ];

        return view('/admin/management/user/oauth',['users'=>$users,'request'=>$res,'condition'=>$cond]);
    }

    /**
     *   用户屏蔽或启用
     */
    public function delete(Request $request)
    {
        try{

            // 查询用户是否存在，获取集合
            $user = User::findOrFail((int)$request -> input('id'));

            $status = 1 == $user -> status ? 0 :1;

            User::findOrfail((int)$request -> input('id')) -> update(['status' => $status]);

            // 写入日志 管理日志
            UserManageLog::create([
                'admin_id'  => Auth::guard('web')->user() -> id,
                'data_id'   => $user -> id,
                'active'    => $user -> status == 1 ? 2 :1,
                'time_add'  => getTime()
            ]);

            return back()->with('success','操作成功');
        } catch (ModelNotFoundException $e){
            return back()->with('success','操作失败');
        } catch (\Exception $e){
            return back()->with('success','操作失败');
        }
    }

    /**
     *   本地用户回收站
     */
    public function localrecycle(Request $request)
    {
        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search',''));

        // 从user表中取出一定数量的数据
        if($search){

            // 条件
            switch($condition){
                case 1:
                    $users = User::whereHas('hasOneLocalAuth',function($q) use ($search){
                        $q -> where('user_id','like','%'.$search.'%');
                    });
                    break;
                case 2:
                    $users = User::whereHas('hasOneLocalAuth',function($q) use ($search){
                        $q -> where('username','like','%'.$search.'%');
                    });
                    break;
                case 3:
                    $users = User::has('hasOneLocalAuth')->where('nickname','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }

            $users = $users -> orderBy('id','DESC')
                ->where('status',1)
                -> paginate((int)$request->input('num',10));

        }else{

            $users = User::has('hasOneLocalAuth')
                ->orderBy('id','DESC')
                ->where('status',1)
                ->paginate((int)$request->input('num',10));
        }

        // 遍历集合，从local_user表中取数据
        $users->each(function($user){
            $info = LocalAuth::where('user_id',$user->id)->first();
            $user -> username = $info->username;
            $user -> user_id = $info->user_id;
        });

//        // 从user表中取出一定数量的数据
//        $users = User::has('hasOneLocalAuth')->where('status',1)->orderBy('id','DESC')->paginate($request->get('num',10));
//
//        // 遍历集合，从local_user表中取数据
//        $users->each(function($user){
//            $info = LocalAuth::where('user_id',$user->id)->first();
//            $user -> username = $info->username;
//            $user -> user_id = $info->user_id;
//        });

        // 搜索条件
        $cond = [1=>'ID',2=>'用户名',3=>'昵称'];

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$request->input('num',10),
            'search'=>$request->input('search',''),
        ];

        return view('/admin/management/user/localrecycle',['users'=>$users,'request'=>$res,'condition'=>$cond]);
    }

    /**
     *   第三方用户回收站
     */
    public function oauthrecycle(Request $request)
    {

        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search',''));

        // 从user表中取出一定数量的数据
        if($search){

            // 条件
            switch($condition){
                case 1:
                    $users = User::whereHas('hasManyOAuth',function($q) use ($search){
                        $q -> where('user_id','like','%'.$search.'%');
                    });
                    break;
                case 2:
                    $users = User::whereHas('hasManyOAuth',function($q) use ($search){
                        $q -> where('oauth_name','like','%'.$search.'%');
                    });
                    break;
                case 3:
                    $users = User::has('hasManyOAuth')->where('nickname','like','%'.$search.'%');
                    break;
                case 4:
                    $users = User::whereHas('hasManyOAuth',function($q) use ($search){
                        $q -> where('oauth_id','like','%'.$search.'%');
                    });
                    break;
                default:
                    return back();
            }

            $users = $users -> orderBy('id','DESC')
                ->where('status',1)
                -> paginate((int)$request->input('num',10));

        }else{

            $users = User::has('hasManyOAuth')
                ->orderBy('id','DESC')
                ->where('status',1)
                ->paginate((int)$request->input('num',10));
        }

        // 遍历集合，从 oauth 表中取数据
        $users->each(function($user){
            $info = OAuth::where('user_id',$user->id)->first();
            $user -> oauth_name = $info->oauth_name;
            $user -> oauth_id = $info->oauth_id;
            $user -> user_id = $info->user_id;
        });


        // 搜索条件
        $cond = [1=>'ID',2=>'登录方式',3=>'昵称',4=>'第三方id'];

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$request->input('num',10),
            'search'=>$request->input('search',''),
        ];

        return view('/admin/management/user/oauthrecycle',['users'=>$users,'request'=>$res,'condition'=>$cond]);
    }

    /**
     * 注册用户 统计
     */
    public function statistics(){

        // 获取手机注册用户集合
        $phone_users = LocalAuth::get();

        // 获取第三方用户集合
        $oauth_users = OAuth::get();

        // 获取今日日期
        $date = date('Y-m-d');

        // 获取今日注册量
//        $today_users = LocalAuth::where('created_at',)->get();


    }

    /**
     *   手机用户添加页面
     */
    public function add()
    {
        // 返回用户添加页面
        return view('/admin/management/user/addUser');
    }

    /**
     *   手机用户添加保存
     */
    public function insert(Request $request)
    {
        try{
            $this->validate($request, [
                'username' => 'required|min:6',
                'password' => 'required|min:6|max:18',
            ],
            [
                'username.required' => '用户名不能为空',
                'password.required' => '密码不能为空',
            ]);

            // 获取添加的用户名和密码
            $username = (int)$request -> get('username');
            $password = trim($request -> get('password'));
            $nickname = trim($request -> get('nickname'));
            $avatar = $request -> file('avatar');
            $sex = (int)$request -> get('sex',0);

            // 判断
            if($nickname){

                if(!regex_name($nickname)) return back()->with('error','昵称格式不对');
            }

            if(!regex_pwd($password) || !pregTP($username)) return back()->with(['error'=>'bad_request']);

            // 判断用户是否已经存在
            if(LocalAuth::where('username',$username)->count()){
                \Session::flash('username', '"'.$username.'"'.trans('common.has_been_existed'));
                return redirect('/admin/user/list/add')->withInput();
            }

            // 对密码进行哈希加密
            $password_new = bcrypt($password);

            // 开启事务
            DB::beginTransaction();

            // 时间
            $time = new Carbon;

            // 将用户信息存入 user 表
            $user = User::create([
                'last_token' => $time,
                'nickname'   => $nickname,
                'sex'        => $sex,
                'verify'        => 1,
                'created_at' => $time,
                'updated_at' => $time,
            ]);

            // 昵称
//            $user->nickname = 'ZhuiXi_' . $user->id;

            // 用户头像
            if (isset($avatar)) {

                // 获取图片尺寸
                $size = getimagesize($avatar)[0].'*'.getimagesize($avatar)[1];

                // 获取随机数
                $rand = mt_rand(1000000,9999999);

                $result = CloudStorage::putFile(
                    'users/' . $user->id . '/' . getTime() . $rand.'_'.$size.'_.'.$avatar->getClientOriginalExtension(),
                    $avatar);

                $user->hash_avatar = $result[0]['hash'];
                $user->avatar = $result[0]['key'];
            }

            // 保存用户信息
            $user->save();

            // 将信息存入 local_auth 表
            LocalAuth::create([
                'user_id'       => $user->id,
                'username'      => $username,
                'password'      => $password_new
            ]);

            //添加注册时，要给用户添加所有频道
            $data = [];
            $channels_data = Channel::active()->pluck('id')->all();

            // 处理成字符串
            $channels = implode(',',$channels_data);

            // 为新注册用户添加频道信息
            $data[] = [
                'user_id'       => $user->id,
                'channel_id'    => $channels,
                'time_add'      => getTime(),
                'time_update'   => getTime()
            ];

            // 存入 user_channel 表
            DB::table('user_channel')->insert($data);

            // 从user表中取出一定数量的数据
            $users = User::has('hasOneLocalAuth')
                ->where('status',0)
                ->orderBy('id','DESC')
                ->paginate($this->page);

            // 遍历集合，从local_user表中取数据
            $users->each(function($user){
                $info = LocalAuth::where('user_id',$user->id)->first();
                $user -> username = $info->username;
                $user -> user_id = $info->user_id;
            });

            // 将用户相关信息存入tig_user表 对用户名进行sha1加密
            $sha1_user_id = sha1($user->id.'@goobird');
            $tig_user_data = [
                'user_id' => $user->id.'@goobird',
                'sha1_user_id' => $sha1_user_id,
                'user_pw' => $password_new,
                'acc_create_time' => new Carbon(),
            ];

            $tigase_user = DB::table('tig_users')->insertGetId($tig_user_data);

            // 将用户信息存入user_jid表，返回jid_id
            $jid_id = DB::table('user_jid')->insertGetId([
                'jid_sha' => $sha1_user_id,
                'jid' => $user->id.'@goobird',
            ]);

            // 为用户添加至 gold_account 表
            GoldAccount::create([
                'user_id'       => $user->id,
                'time_add'      => getTime(),
                'time_update'   => getTime()
            ]);

            // 为用户创建 statistics_users 表中数据
            StatisticsUsers::create([
                'user_id'           => $user->id,
                'time_add'          => getTime(),
                'time_update'       => getTime()
            ]);

            // 事务提交
            DB::commit();

            // 重定向
            return redirect('/admin/user/list/local');

        }catch(\Exception $e){

            // 抛出404错误
            abort(404);
        }
    }


}