<?php

namespace App\Api\Controllers;

use App\Api\Transformer\FragCollectTransformer;
use App\Api\Transformer\FragmentDetailTransformer;
use App\Api\Transformer\UserIntegralTransformer;
use App\Api\Transformer\UsersTransformer;
use App\Models\Fragment;
use App\Models\User;
use App\Models\FragmentType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Facades\CloudStorage;

/**
 * 片段相关接口
 *
 * @Resource("Discovery")
 */
class FragmentController extends BaseController
{

    // 热门片段
    protected $hotFragmentTransformer;

    // 条数
    protected $paginate = 20;

    private $usersTransformer;

    protected $fragCollectTransformer;

    protected $userIntegralTransform;

    protected  $fragmentDetailTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        FragCollectTransformer $fragCollectTransformer,
        UserIntegralTransformer $userIntegralTransformer,
        FragmentDetailTransformer $fragmentDetailTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->fragCollectTransformer = $fragCollectTransformer;
        $this->userIntegralTransform = $userIntegralTransformer;
        $this->fragmentDetailTransformer = $fragmentDetailTransformer;
    }

    /**
     * 首页
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // 获取页数
            if(!is_numeric($page = $request -> get('page',1)))
                return response()->json(['error'=>'bad_request'],403);

            //街道
            $address_street = $request->get('address_street');
                //区县
            $address_county = $request->get('address_county');

            //城市
            $address_city = $request->get('address_city');

            //省份
            $address_province = $request->get('address_province');

            //国家
            $address_country = $request->get('address_country');
            //搜索官方推荐
            $official_fragments = Fragment::with(['keyWord'=>function($q){
                $q->select('keyword');
            }])
                ->where('recommend','=','1')
                ->where('active','!=','2')
                ->take(3)
                ->get();

            //随机取出数据
            $rand_fragment = Fragment::with(['keyWord'=>function($q){
                $q->select('keyword');
            }])
                ->where('active','!=','2')
                -> forPage($page,$this->paginate)
                ->get();


            //按街道进行搜索
            $address_street_fragments = Fragment::with(['keyWord'=>function($q){
                $q->select('keyword');
            }])
                ->where('address_street','=',$address_street)
                ->where('active','!=','2')
                ->orderBy('count', 'desc')
                ->take(3)
                ->get();

            //如果搜索街道有内容
            $fragment_data = [];
            if (count($address_street_fragments)){
                if (count($official_fragments)){
                    $fragment_data = array_merge($official_fragments->toArray(),$address_street_fragments->toArray(),$rand_fragment->toArray());
                }else{
                    $fragment_data = array_merge($address_street_fragments->toArray(),$rand_fragment->toArray());
                }
            }else{
                //按区搜索
                $address_county_fragments = Fragment::with(['keyWord'=>function($q){
                    $q->select('keyword');
                }])
                    ->where('address_county','=',$address_county)
                    ->where('active','!=','2')
                    ->orderBy('count', 'desc')
                    ->take(3)
                    ->get();
                //官方 + 区
                if (count($official_fragments)){
                    $fragment_data = array_merge($official_fragments->toArray(),$address_county_fragments->toArray(),$rand_fragment->toArray());
                }else{
                    $fragment_data = array_merge($address_county_fragments->toArray(),$rand_fragment->toArray());
                }

                if (!count($address_county_fragments)){
                    //按城市搜索
                    $address_city_fragments = Fragment::with(['keyWord'=>function($q){
                        $q->select('keyword');
                    }])
                        ->where('address_city','=',$address_city)
                        ->where('active','!=','2')
                        ->orderBy('count', 'desc')
                        ->take(3)
                        ->get();

                    //官方 + 城市
                    if (count($official_fragments)){
                        $fragment_data = array_merge($official_fragments->toArray(),$address_city_fragments->toArray(),$rand_fragment->toArray());
                    }else{
                        $fragment_data = array_merge($address_city_fragments->toArray(),$rand_fragment->toArray());
                    }

                    if (!count($address_city_fragments)){
                        //按省份搜索
                    $address_province_fragments = Fragment::with(['keyWord'=>function($q){
                        $q->select('keyword');
                    }])
                        ->where('address_province','=',$address_province)
                        ->where('active','!=','2')
                        ->orderBy('count', 'desc')
                        ->take(3)
                        ->get();

                    //官方 + 省份
                    if (count($official_fragments)){
                        $fragment_data = array_merge($official_fragments->toArray(),$address_province_fragments->toArray(),$rand_fragment->toArray());
                    }else{
                        $fragment_data = array_merge($address_province_fragments->toArray(),$rand_fragment->toArray());
                    }

                    if (!count($address_province_fragments)){
                        //按国家搜索
                    $address_country_fragments = Fragment::with(['keyWord'=>function($q){
                        $q->select('keyword');
                    }])
                        ->where('address_country','=',$address_country)
                        ->where('active','!=','2')
                        ->orderBy('count', 'desc')
                        ->take(3)
                        ->get();

                    //官方 + 国家
                    if (count($official_fragments)){
                        $fragment_data = array_merge($official_fragments->toArray(),$address_country_fragments->toArray(),$rand_fragment->toArray());
                    }else{
                        $fragment_data = array_merge($address_country_fragments->toArray(),$rand_fragment->toArray());
                    }

                    if(!count($address_country_fragments)){
                         if (count($official_fragments)){
                             $fragment_data = array_merge($official_fragments->toArray(),$rand_fragment->toArray());
                         }else{
                             $fragment_data = $rand_fragment->toArray();
                         }
                      }
                    }
                  }
                }
            }

            $count = count($fragment_data);

            $data = $count ? $fragment_data : [];

            //获取默认的所有分类
            $classify = FragmentType::all();
            if ($count){
                return [
                    // 应取数据的条数
                    'count'      => $this->paginate,
                    'status' => 'success',
                    'status_code' => 200,
                    'classify_data'=>$this->ClassifyCollection($classify),
                    'fragment_data' =>  $this->fragCollectTransformer->ptransform($data),
                ];
            }else{
                return [
                    'status' => 'failed',
                    'status_code' =>404,
                    'error' => 'Not Found'
                ];
            }

        }catch (\Exception $e) {
             return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @param $items
     * @return array
     */
    public function ClassifyCollection($items)
    {
        return array_map([$this,'classifytransform'],$items->toArray());
    }

    /**
     * @param $classify
     * @return array
     */

    public function classifytransform($classify)
    {
        return [
            'id' =>$classify['id'],
            'name'=>$classify['name'],
            'icon'=>CloudStorage::downloadUrl($classify['icon']),
            'hash_icon' =>$classify['hash_icon']
        ];
    }

    /**
     * @param $items
     * @return array
     */
    public function FragmentCollection($items)
    {
        return array_map([$this,'Fragmenttransform'],$items->toArray());
    }

    /**
     * @param $classify
     * @return array
     */
    public function Fragmenttransform($fragment)
    {
        return [
            'id' =>$fragment['id'],
            'title'=>$fragment['name'],
            'duration'=>$fragment['duration'],
            'cover'=>$fragment['cover']
//            'label'=> $fragment    //标签
        ];
    }

    /**
     * 附近片段接口
     */
    public function nearby(Request $request)
    {
        try{
        /**
         * 获取用户目前所在的地址
         */
        //街道
        $address_street = $request->get('address_street');

        //区县
        $address_county = $request->get('address_county');

        //城市
        $address_city = $request->get('address_city');

        //省份
        $address_province = $request->get('address_province');

        //国家
        $address_country = $request->get('address_country');

        //按街道进行搜索
        $address_street_fragments = Fragment::with(['keyWord'=>function($q){
            $q->select('keyword');
        }])
            ->where('address_street','=',$address_street)
            ->where('active','!=','2')
            ->orderBy('count', 'desc')
            ->get();

        //按区搜索
        $address_county_fragments = Fragment::with(['keyWord'=>function($q){
            $q->select('keyword');
        }])
            ->where('address_county','=',$address_county)
            ->where('active','!=','2')
            ->orderBy('count', 'desc')
            ->get();

        //如果搜索街道有内容
        $fragment_data = [];
        if (count($address_street_fragments)){
                $fragment_data = array_merge($address_street_fragments->toArray(),$address_county_fragments->toArray());
        }else{
            //按城市搜索
            $address_city_fragments = Fragment::with(['keyWord'=>function($q){
                $q->select('keyword');
            }])
                ->where('address_city','=',$address_city)
                ->where('active','!=','2')
                ->orderBy('count', 'desc')
                ->get();

            $fragment_data = $address_county_fragments->toArray();

            if (!count($address_city_fragments)){
                //按省份搜索
                $address_province_fragments = Fragment::with(['keyWord'=>function($q){
                    $q->select('keyword');
                }])
                    ->where('address_province','=',$address_province)
                    ->where('active','!=','2')
                    ->orderBy('count', 'desc')
                    ->get();

                $fragment_data = $address_province_fragments->toArray();

                if(!count($address_province_fragments)){
                    //按国家搜索
                    $address_country_fragments = Fragment::with(['keyWord'=>function($q){
                        $q->select('keyword');
                    }])
                        ->where('address_country','=',$address_country)
                        ->where('active','!=','2')
                        ->orderBy('count', 'desc')
                        ->get();

                    $fragment_data = $address_country_fragments->toArray();

                    if(!count($address_country_fragments)){
                        $fragment_data = '';
                    }
                }
            }
        }

        if ($fragment_data){
            return [
                'status'=> 'success',
                'status_code' => 200,
                'count' => $this->paginate,
                'fragment_data' => $this->fragCollectTransformer->ptransform($fragment_data),
            ];
        }else{
            return [
                'status'=> 'Not Found',
                'status_code' => 404
            ];
        }

         }catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * 片段收藏
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public  function collect(Request $request)
    {
        try{
            // 判断用户是否为登录状态
            $users = Auth::guard('api')->user();

            //如果用户未登录
            if (empty( $users)){
                return  [
                    'status_code' =>400,
                    'message' => 'Not logged in'
                ];
            }

            $user = User::with(['belongsToManyFragment'=>function($q){
                $q->with(['keyWord'=>function($a){
                    $a->select('keyword');
                },'belongsToManyFragmentType'=>function($b){
                    $b->select('name','icon');
                }])->where('way',1);
            }])->find( $users->id )->belongsToManyFragment;

            if ($user){
                return [
                    'status_code'=>200,
                    'status'=>'success',
                    'user'=>$this->fragCollectTransformer->ptransform($user->toArray())
                ];
            }else{
                return [
                    'status_code'=>404,
                    'status'=>'Not Found'
                ];
            }

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * 下载
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public  function download(Request $request)
    {
        try{
            // 判断用户是否为登录状态
            $users = Auth::guard('api')->user();

            //如果用户未登录
          if (empty( $users)){
                return  [
                    'status_code' =>400,
                    'message' => 'Not logged in'
                ];
            }

            $user = User::with(['belongsToManyFragment'=>function($q){
                $q->with(['keyWord'=>function($a){
                    $a->select('keyword');
                },'belongsToManyFragmentType'=>function($b){
                    $b->select('name','icon');
                }])->where('way',2);
            }])->find($users->id)->belongsToManyFragment;

            if ($user){
                return [
                    'status_code'=>200,
                    'status'=>'success',
                    'user'=>$this->fragCollectTransformer->transform($user->toArray())
                ];
            }else{
                return [
                    'status_code'=>404,
                    'status'=>'Not Found'
                ];
            }

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * 分类详情
     * @param Request $request
     * @param $id
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function details(Request $request,$id)
    {
        try{
            // 获取要查询的关键词 及 所取页数
            if(!is_numeric($page = $request -> get('page',1)))
                return response()->json(['error'=>'bad_request'],403);

            //搜索官方推荐
            $first_fragments = FragmentType::find($id)->belongsToManyFragment()->with(['keyWord'=>function($query){
                $query->select('keyword');
            }])
                ->where('recommend','=','1')
                ->where('active','!=',2)
                ->take(3)
                ->get();

            //排行
            $second__fragments = FragmentType::find($id)->belongsToManyFragment()->with(['keyWord'=>function($query){
                $query->select('keyword');
            }])
                -> where('active','!=',2)
                -> orderBy('watch_count', 'DESC')
                -> forPage($page,$this->paginate)
                -> get();

            //拼接
            $data = array_merge($first_fragments->toArray(),$second__fragments->toArray());

            //片段数量
            $fragments_count = FragmentType::find($id)->belongsToManyFragment()->with(['keyWord'=>function($query){
                $query->select('keyword');
            }])
                -> where('active','!=',2)
                ->count();

            //观看次数
            $watch_counts = FragmentType::find($id)->belongsToManyFragment()
                -> where('active','!=',2)
                -> get(['watch_count','count','praise']);

            $aa = [];   //观看
            $bb = [];   //下载
            $cc = [];   //赞
            foreach ($watch_counts as $v){
                $aa [] = $v->watch_count;
                $bb [] = $v->count;
                $cc [] = $v->praise;
            }

            //观看次数
            $watch_count = 0;
            foreach ($aa as $v){
                $watch_count += $v;
            }

            //下载次数
            $down_count = 0;
            foreach ($bb as $v){
                $down_count += $v;
            }

            //赞次数
            $praise_count = 0;
            foreach ($cc as $v){
                $praise_count += $v;
            }

            //响应
            if(!count($data)){
                return [
                    'status_code'=>404,
                    'status'=>'Not Found'
                ];
            }

            $data = mult_unique($data);
            //响应
            return [
                'status_code'=>200,
                'status' => 'success',
                'fragments_count'=>$fragments_count,
                'watch_count' => $watch_count,
                'down_count' =>$down_count,
                'praise_count' =>$praise_count,
                'data'=> $this->fragCollectTransformer->ptransform($data)
            ];

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 最新片段
     * @param Request $request
     * @param $id
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function newlist(Request $request,$id)
    {
        try{
            // 获取要查询的关键词 及 所取页数
            if(!is_numeric($page = $request -> get('page',1)))
                return response()->json(['error'=>'bad_request'],403);

            //搜索官方推荐
            $first_fragments = FragmentType::find($id)->belongsToManyFragment()->with(['keyWord'=>function($query){
                $query->select('keyword');
            }])
                ->where('recommend','=','1')
                ->where('active','!=',2)
                ->take(3)
                ->get();

            //最新
            $second__fragments = FragmentType::find($id)->belongsToManyFragment()->with(['keyWord'=>function($query){
                $query->select('keyword');
            }])
                -> where('active','!=',2)
                -> orderBy('time_add', 'DESC')
                -> forPage($page,$this->paginate)
                -> get();

            //拼接
            $data = array_merge($first_fragments->toArray(),$second__fragments->toArray());

            //片段数量
            $fragments_count = FragmentType::find($id)->belongsToManyFragment()->with(['keyWord'=>function($query){
                $query->select('keyword');
            }])
                -> where('active','!=',2)
                ->count();

            //观看次数
            $watch_counts = FragmentType::find($id)->belongsToManyFragment()
                -> where('active','!=',2)
                -> get(['watch_count','count','praise']);

            $aa = [];   //观看
            $bb = [];   //下载
            $cc = [];   //赞
            foreach ($watch_counts as $v){
                $aa [] = $v->watch_count;
                $bb [] = $v->count;
                $cc [] = $v->praise;
            }

            //观看次数
            $watch_count = 0;
            foreach ($aa as $v){
                $watch_count += $v;
            }

            //下载次数
            $down_count = 0;
            foreach ($bb as $v){
                $down_count += $v;
            }

            //赞次数
            $praise_count = 0;
            foreach ($cc as $v){
                $praise_count += $v;
            }

            //响应
            if(!count($data)){
                return [
                    'status_code'=>404,
                    'status'=>'Not Found'
                ];
            }

            $data = mult_unique($data);

            //响应
            return [
                'status_code'=>200,
                'status' => 'success',
                'fragments_count'=>$fragments_count,
                'watch_count' => $watch_count,
                'down_count' =>$down_count,
                'praise_count' =>$praise_count,
                'data'=> $this->fragCollectTransformer->ptransform($data)
            ];

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * 片段详情
     * @param $id
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function fragdetail($id)
    {
        try{

            // 判断用户是否为登录状态
            $user = Auth::guard('api')->user();

      /*      if (empty($user)){
                return [
                    'status'=> 'failed',
                    'status_code' => 403,
                    'message' => 'No Logged in',
                ];
            }*/

        $fragment = Fragment::with(['belongsToManyUser'=>function($q){
            $q->select('nickname');
        },'hasManySubtitle'=>function($a){
            $a->orderBy('start_time','asc');
        }])->find($id);

           return [
               'status' =>'success',
               'status_code' => 200,
               'data' => $this->fragmentDetailTransformer->transform($fragment)
           ];

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function useOrFilm($frag_id,Request $request)
    {
        try{
        //判断用户是否为登录状态
        $user = Auth::guard('api')->user();
            $id = 1000234;
            $user = User::find($id);
        //判断片段是否收费
        $fragment_info = Fragment::find($frag_id);

        //片段不存在
        if (!$fragment_info){
            return [
                'status' => 'failed',
                'status_code' => 404,
                'message' => 'this fragment not found',
            ];
        }

        //所需积分
        $integral = $fragment_info->intergral + 0;

        //所需金币
        $cost = $fragment_info->cost + 0;

        //会员是否收费
        $vip_isfree = $fragment_info->vipfree + 0;

        //免费片段
        if (!$integral && !$cost && !$vip_isfree){

            $fragment= Fragment::with(['belongsToUser'])->find($frag_id);

            //修改下载量
            DB::table('fragment')->increment('count');
            DB::table('fragment')->increment('watch_count');

            return [
                'status' =>'success',
                'status_code' => 200,
                'data' => $this->fragmentDetailTransformer->transform($fragment)
            ];

        }elseif ($integral && !$cost && !$vip_isfree){
            //需要积分  但会员免费

//            判断用户是否是vip
            if ($user->is_vip){

                $fragment= Fragment::with(['belongsToUser'])->find($frag_id);

                $is_exist = DB::table('fragment_user_collect')
                    ->where('user_id','=',$user->id)
                    ->where('fragment_id','=',$frag_id)
                    ->first();

                   if(!$is_exist) {
                           //写入下载表
                           DB::table('fragment_user_collect')->insert([
                               'user_id' => $user->id,
                               'fragment_id' => $frag_id,
                               'create_at' => time(),
                               'way' => 2,
                           ]);
                   }

                //修改下载量
                DB::table('fragment')->increment('count');
                DB::table('fragment')->increment('watch_count');

                return [
                    'status' =>'success',
                    'status_code' => 200,
                    'data' => $this->fragmentDetailTransformer->transform($fragment)
                ];

            }else{

                //从消费表查看是否购买过
                $res = User\UserIntegralExpend::where('user_id',$user->id)
                            -> where('type_id',$frag_id)
                            -> where('status',1)
                            ->first();

                //接收用户是否确认扣除积分
                $commit = $request->get('commit');

                //已经购买过
                if ($res) {
                    $fragment = Fragment::with(['belongsToUser'])->find($frag_id);

                    //修改下载量
                    DB::table('fragment')->increment('count');
                    DB::table('fragment')->increment('watch_count');

                    return [
                        'status' =>'success',
                        'status_code' => 200,
                        'data' => $this->fragmentDetailTransformer->transform($fragment)
                    ];
                }

                //确认扣除积分
                if ($commit === '1'){
                    $user_info = User\UserIntegral::where('user_id','=',$user->id)->first();
                    $user_integral = $user_info->integral_count;
                    $number = date('YmdHis').rand(100000,999999);

                    //如果用户积分足够
                    if($user_integral >= $integral){
//                       开启事务
                        DB::beginTransaction();

                        //扣除积分
                        $integral_update = User\UserIntegral::where('user_id','=',$user->id)->update(['integral_count'=>$user_integral - $integral]);

                        if($integral_update){

                            $fragment= Fragment::with(['belongsToUser'])->find($frag_id);

                            //写入消费表
                            $integral_extend = new User\UserIntegralExpend();

                            $integral_extend -> user_id = $user_info->user_id;

                            $integral_extend -> pay_number = $number;

                            $integral_extend -> pay_count  = $integral;

                            $integral_extend -> type_id = $fragment->id;

                            $integral_extend -> pay_reason = '片段:'.$fragment->name;

                            $integral_extend -> status     = 1;

                            $integral_extend -> create_at  = time();

                            $result = $integral_extend -> save();

                            //返回数据
                            if ($result){

                                DB::commit();

                                //写入下载表
                                DB::table('fragment_user_collect')->insert([
                                    'user_id' => $user->id,
                                    'fragment_id' => $frag_id,
                                    'create_at' =>time(),
                                    'way' => 2,
                                ]);

                                DB::table('fragment')->increment('count');
                                DB::table('fragment')->increment('watch_count');

                                return [
                                    'data' => $this->fragmentDetailTransformer->transform($fragment)
                                ];
                            }else{
                                DB::rollBack();
                                return [
                                    'status' => 'failed',
                                    'status_code' => 500,
                                    'message' => 'Try again later',
                                ];
                            }

                        }else{
                            DB::rollBack();
                            return [
                                'status' => 'failed',
                                'status_code' => 500,
                                'message' => 'Try again later',
                            ];
                        }

                    }else{
                        //用户所剩的积分数
                        $user_info = User\UserIntegral::where('user_id','=',$user->id)->first();
                        $user_integral = $user_info->integral_count;

                        return [
                            'status' => 'failed',
                            'status_code' => 403,
                            'user_integral' => $user_integral,
                            'message' => 'Sorry Underbalance',
                        ];
                    }

                }else if($commit === '2'){                      //取消购买
                    return [
                        'status' => 'success',
                        'status_code' => 204,
                        'message' => 'Successfully Canceled',
                    ];
                }else{
                    return [
                        'status' => 'failed',
                        'status_code' => 103,
                        'message' => 'Need to purchase',
                    ];
                }
            }


        }elseif ($integral && !$cost && $vip_isfree){     //会员收费

            //从消费表查看是否购买过
            $res = User\UserIntegralExpend::where('user_id',$user->id)
                -> where('type_id',$frag_id)
                -> where('status',1)
                ->first();

            //已经购买过
            if ($res){
                $fragment= Fragment::with(['belongsToUser'])->find($frag_id);

                //修改下载量
                DB::table('fragment')->increment('count');
                DB::table('fragment')->increment('watch_count');

                return [
                    'status' =>'success',
                    'status_code' => 200,
                    'data' => $this->fragmentDetailTransformer->transform($fragment)
                ];
            }

            //接收用户是否确认扣除积分
            $commit = $request->get('commit');

            //确认扣除积分
            if ($commit === '1'){
                $user_info = User\UserIntegral::where('user_id','=',$user->id)->first();
                $user_integral = $user_info->integral_count;
                $number = date('YmdHis').rand(100000,999999);

                //如果用户积分足够
                if($user_integral >= $integral){
//                       开启事务
                    DB::beginTransaction();

                    //扣除积分
                    $integral_update = User\UserIntegral::where('user_id','=',$user->id)->update(['integral_count'=>$user_integral - $integral]);

                    if($integral_update){

                        $fragment= Fragment::with(['belongsToUser'])->find($frag_id);

                        //写入消费表
                        $integral_extend = new User\UserIntegralExpend();

                        $integral_extend -> user_id = $user_info->user_id;

                        $integral_extend -> pay_number = $number;

                        $integral_extend -> pay_count  = $integral;

                        $integral_extend -> type_id = $fragment->id;

                        $integral_extend -> pay_reason = '片段:'.$fragment->name;

                        $integral_extend -> status     = 1;

                        $integral_extend -> create_at  = time();

                        $result = $integral_extend -> save();

                        //返回数据
                        if ($result){
                            //写入下载表
                            DB::table('fragment_user_collect')->insert([
                                'user_id' => $user->id,
                                'fragment_id' => $frag_id,
                                'create_at' =>time(),
                                'way' => 2,
                            ]);

                            DB::table('fragment')->increment('count');
                            DB::table('fragment')->increment('watch_count');

                            DB::commit();

                            return [
                                'status' =>'success',
                                'status_code' => 200,
                                'data' =>$this->fragmentDetailTransformer->transform($fragment)
                            ];
                        }else{
                            DB::rollBack();
                            return [
                                'status' => 'failed',
                                'status_code' => 500,
                                'message' => 'Try again later',
                            ];
                        }

                    }else{
                        DB::rollBack();
                        return [
                            'status' => 'failed',
                            'status_code' => 500,
                            'message' => 'Try again later',
                        ];
                    }

                }else{
                    //用户所剩的积分数
                    $user_info = User\UserIntegral::where('user_id','=',$user->id)->first();
                    $user_integral = $user_info->integral_count;

                    return [
                        'status' => 'failed',
                        'status_code' => 403,
                        'user_integral' => $user_integral,
                        'message' => 'Sorry Underbalance',
                    ];
                }

            }else if($commit === '2'){                      //取消购买
                return [
                    'status' => 'success',
                    'status_code' => 204,
                    'message' => 'Successfully Canceled',
                ];
            }else{
                return [
                    'status' => 'failed',
                    'status_code' => 103,
                    'message' => 'Need to purchase',
                ];
            }
        }
        //TODO    会员不免费

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

}
