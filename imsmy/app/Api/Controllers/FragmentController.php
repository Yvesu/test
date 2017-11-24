<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ChannelTweetsTransformer;
use App\Api\Transformer\FragCollectTransformer;
use App\Api\Transformer\FragmentDetailTransformer;
use App\Api\Transformer\UserIntegralTransformer;
use App\Api\Transformer\UsersTransformer;
use App\Library\aliyun\SmsDemo;
use App\Library\pinyin\CUtf8_PY;
use App\Models\Config;
use App\Models\Fragment;
use App\Models\Friend;
use App\Models\KeywordFragment;
use App\Models\Keywords;
use App\Models\SensitiveWord;
use App\Models\Tweet;
use App\Models\TweetHot;
use App\Models\TweetQiniuCheck;
use App\Models\User;
use App\Models\FragmentType;
use App\Models\UserKeywords;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Facades\CloudStorage;
use Auth as J_Auth;


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

    private $channelTweetsTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        FragCollectTransformer $fragCollectTransformer,
        UserIntegralTransformer $userIntegralTransformer,
        FragmentDetailTransformer $fragmentDetailTransformer,
        ChannelTweetsTransformer $channelTweetsTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->fragCollectTransformer = $fragCollectTransformer;
        $this->userIntegralTransform = $userIntegralTransformer;
        $this->fragmentDetailTransformer = $fragmentDetailTransformer;
        $this ->channelTweetsTransformer = $channelTweetsTransformer;
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

            //判断用户是否登录
            $user = J_Auth::guard('api')->user();

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

            //官方置顶
            $top_fragment_id = Fragment::where('ishot','=',1)
                ->where('ishottime','>',time())
                ->where('active','=',1)
                ->where('test_results',1)
                ->pluck('id');

            if($top_fragment_id->count()>2){
                $top_fragment_id = $top_fragment_id->random(2);
            }

            //官方推荐
            $recommend_fragment_id = Fragment::where('recommend','=',1)
                ->whereNotIn('id',$top_fragment_id)
                ->where('active','=',1)
                ->where('test_results',1)
                ->pluck('id');

            if ($recommend_fragment_id->count()>4){
                $recommend_fragment_id = $recommend_fragment_id->random(4);
            }

            $first_fragment_id = array_merge($top_fragment_id->toArray(),$recommend_fragment_id->toArray());

            //官方推荐和置顶
            $official_fragments = Fragment::with([
                'belongsToUser'=>function($q){
                    $q->select(['id','nickname','avatar','cover','verify','verify_info','signature']);
                },'belongsToManyFragmentType'
            ])
                ->whereIn('id',$first_fragment_id)
                ->get();

            //随机取出数据
            $rand_fragment = Fragment::with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToUser'])
                ->where('active','!=','2')
                ->where('test_results',1)
                ->whereNotIn('id',$first_fragment_id)
                ->orderBy('watch_count','DESC')
                -> forPage($page,$this->paginate)
                ->get();


            $user_fragment = [];
            //搜索用户喜好
            if($user){
                $user_keywords_ids = UserKeywords::where('user_id','=',$user->id)->pluck('id');

                //按喜好查询
                if($user_keywords_ids->all()){

                    $user_fragment = Fragment::WhereHas('keyWord',function ($q) use ($user_keywords_ids){
                        $q->whereIn('keyword_id',$user_keywords_ids);
                    })
                        ->with(['belongsToManyFragmentType'=>function($q){
                            $q->select('name');
                        },'belongsToUser'])
                        ->where('active','!=','2')
                        ->where('test_results',1)
                        ->whereNotIn('id',$first_fragment_id)
                        ->orderBy('watch_count','DESC')
                        -> forPage($page,$this->paginate)
                        ->get();
                }
            }

            //按街道进行搜索
            $address_street_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToUser'])
                ->where('address_street','=',$address_street)
                ->where('active','!=','2')
                ->where('test_results',1)
                ->orderBy('watch_count', 'desc')
                ->take(3)
                ->get();

            //如果搜索街道有内容
            $fragment_data = [];
            if (count($address_street_fragments)){
                if (count($official_fragments)){
                    if($user){
                        $fragment_data = array_merge($official_fragments->toArray(),$address_street_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                    }else{
                        $fragment_data = array_merge($official_fragments->toArray(),$address_street_fragments->toArray(),$rand_fragment->toArray());
                    }
                }else{
                    if($user){
                        $fragment_data = array_merge($address_street_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                    }else{
                        $fragment_data = array_merge($address_street_fragments->toArray(),$rand_fragment->toArray());
                    }
            }
            }else{
                //按区搜索
                $address_county_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                    $q->select('name');
                },'belongsToUser'])
                    ->where('address_county','=',$address_county)
                    ->where('active','!=','2')
                    ->where('test_results',1)
                    ->orderBy('watch_count', 'desc')
                    ->take(3)
                    ->get();
                //官方 + 区
                if (count($official_fragments)){

                    if($user){
                        $fragment_data = array_merge($official_fragments->toArray(),$address_county_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                    }else{
                        $fragment_data = array_merge($official_fragments->toArray(),$address_county_fragments->toArray(),$rand_fragment->toArray());
                    }

                }else{
                    if($user){
                        $fragment_data = array_merge($address_county_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                    }else{
                        $fragment_data = array_merge($address_county_fragments->toArray(),$rand_fragment->toArray());
                    }
                }

                if (!count($address_county_fragments)){
                    //按城市搜索
                    $address_city_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                        $q->select('name');
                    },'belongsToUser'])
                        ->where('address_city','=',$address_city)
                        ->where('active','!=','2')
                        ->where('test_results',1)
                        ->orderBy('watch_count', 'desc')
                        ->take(3)
                        ->get();

                    //官方 + 城市
                    if (count($official_fragments)){
                        if($user){
                            $fragment_data = array_merge($official_fragments->toArray(),$address_city_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                        }else{
                            $fragment_data = array_merge($official_fragments->toArray(),$address_city_fragments->toArray(),$rand_fragment->toArray());
                        }

                    }else{
                        if($user){
                            $fragment_data = array_merge($address_city_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                        }else{
                            $fragment_data = array_merge($address_city_fragments->toArray(),$rand_fragment->toArray());
                        }

                    }

                    if (!count($address_city_fragments)){
                        //按省份搜索
                    $address_province_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                        $q->select('name');
                    },'belongsToUser'])
                        ->where('address_province','=',$address_province)
                        ->where('active','!=','2')
                        ->where('test_results',1)
                        ->orderBy('watch_count', 'desc')
                        ->take(3)
                        ->get();

                    //官方 + 省份
                    if (count($official_fragments)){
                        if($user){
                            $fragment_data = array_merge($official_fragments->toArray(),$address_province_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                        }else{
                            $fragment_data = array_merge($official_fragments->toArray(),$address_province_fragments->toArray(),$rand_fragment->toArray());
                        }

                    }else{
                        if($user){
                            $fragment_data = array_merge($address_province_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                        }else{
                            $fragment_data = array_merge($address_province_fragments->toArray(),$rand_fragment->toArray());
                        }
                    }

                    if (!count($address_province_fragments)){
                        //按国家搜索
                    $address_country_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                        $q->select('name');
                    },'belongsToUser'])
                        ->where('address_country','=',$address_country)
                        ->where('active','!=','2')
                        ->where('test_results',1)
                        ->orderBy('watch_count', 'desc')
                        ->take(3)
                        ->get();

                    //官方 + 国家
                    if (count($official_fragments)){
                        if($user){
                            $fragment_data = array_merge($official_fragments->toArray(),$address_country_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                        }else{
                            $fragment_data = array_merge($official_fragments->toArray(),$address_country_fragments->toArray(),$rand_fragment->toArray());
                        }
                    }else{

                        if($user){
                            $fragment_data = array_merge($address_country_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                        }else{
                            $fragment_data = array_merge($address_country_fragments->toArray(),$rand_fragment->toArray());
                        }

                    }

                    if(!count($address_country_fragments)){
                         if (count($official_fragments)){
                             if($user){
                                 $fragment_data = array_merge($official_fragments->toArray(),$user_fragment->toArray(),$rand_fragment->toArray());
                             }else{
                                 $fragment_data = array_merge($official_fragments->toArray(),$rand_fragment->toArray());
                             }

                         }else{
                             if($user){
                                 $fragment_data = array_merge($user_fragment->toArray(),$rand_fragment->toArray());
                             }else{
                                 $fragment_data = $rand_fragment->toArray();
                             }
                         }
                      }
                    }
                  }
                }
            }

            $count = count($fragment_data);

            $data = $count ? $fragment_data : [];

            $data = mult_unique($data);
            //获取默认的所有分类
            $classify = FragmentType::all();

            $classifys = [];
            foreach ($classify->toArray() as $k=>$v){
               $a[] =  DB::table('fragmenttype_fragment')->where('fragmentType_id','=',$v['id'])->first();
               if(!empty($a[$k])){
                   $classifys[] = $v;
               }
            }

//            dd(($classifys));
            if($page == 1){
                if ($count){
                    return response() -> json([
                        // 应取数据的条数
                        'count'      => $this->paginate,
                        'classify_data'=>$this->classifytransform($classifys),
                        'fragment_data' =>  $this->fragCollectTransformer->transform($data),
                    ], 200);
                }else{
                    return response() -> json(['error'=>'not_found'], 404);
                }
            }

            //当不是第一页的时候
            return response() -> json([
                'fragment_data' =>  $this->fragCollectTransformer->transform(array_merge($user_fragment->toArray(),$rand_fragment->toArray())),
            ], 200);

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
        return array_map([$this,'classifytransform'],$items);
    }

    /**
     * @param $classify
     * @return array
     */
    public function classifytransform($classify)
    {
        $a = [];
        foreach ($classify as $v){
            $a[] = [
            'id' =>$v['id'],
            'name'=>$v['name'],
            'icon'=>CloudStorage::downloadUrl( $v['icon']),
         ];
        }
        return $a;
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
            'duration'=>changeTimeType($fragment['duration']),
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
        $address_street_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
            $q->select('name');
        },'belongsToUser'])
            ->where('address_street','=',$address_street)
            ->where('active','!=','2')
            ->where('test_results',1)
            ->orderBy('count', 'desc')
            ->get();

        //按区搜索
        $address_county_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
            $q->select('name');
        },'belongsToUser'])
            ->where('address_county','=',$address_county)
            ->where('active','!=','2')
            ->where('test_results',1)
            ->orderBy('count', 'desc')
            ->get();

        //如果搜索街道有内容
        $fragment_data = [];
        if (count($address_street_fragments)){
                $fragment_data = array_merge($address_street_fragments->toArray(),$address_county_fragments->toArray());
        }else{
            //按城市搜索
            $address_city_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToUser'])
                ->where('address_city','=',$address_city)
                ->where('active','!=','2')
                ->where('test_results',1)
                ->orderBy('count', 'desc')
                ->get();

            $fragment_data = $address_county_fragments->toArray();

            if (!count($address_city_fragments)){
                //按省份搜索
                $address_province_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                    $q->select('name');
                },'belongsToUser'])
                    ->where('address_province','=',$address_province)
                    ->where('active','!=','2')
                    ->where('test_results',1)
                    ->orderBy('count', 'desc')
                    ->get();

                $fragment_data = $address_province_fragments->toArray();

                if(!count($address_province_fragments)){
                    //按国家搜索
                    $address_country_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                        $q->select('name');
                    },'belongsToUser'])
                        ->where('address_country','=',$address_country)
                        ->where('active','!=','2')
                        ->where('test_results',1)
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
            return response() -> json([
                'count' => $this->paginate,
                'fragment_data' => $this->fragCollectTransformer->transform($fragment_data),
            ], 200);
        }else{
		return response() -> json(['error'=>'not_found'], 404);
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
            $users = J_Auth::guard('api')->user();

            if (!$users){
                return resoponse()->json(['error'=>'Please log in']);
            }

            $ids = DB::table('fragment_user_collect')
                ->where('user_id','=',$users->id)
                ->where('way','=','1')
                ->get();

            $id = [];
            foreach ($ids as $k=>$v){
                $id [] = $v->fragment_id;
            }

            $collect = [];
            foreach ($id as $v){
                $collect[] = Fragment::with(['belongsToManyFragmentType','belongsToUser'])
                    ->where('test_results','=',1)
                    ->find($v)
                    ->toArray();
            }

            if ($collect){
                return response() -> json([
                    'data'=>$this->fragCollectTransformer->collecttransform($collect)
                  ], 200);
            }else{
                return [];
            }
        }catch(\Exception $e) {
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
            //片段数量
            $fragments_count = FragmentType::find($id)->belongsToManyFragment()->with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToManyUser'=>function($q){
                $q->select('nickname','avatar','verify','verify_info','cover','signature');
            }])
                -> where('active','!=',2)
                -> where('test_results',1)
                ->count();

            //观看次数
            $watch_counts = FragmentType::find($id)->belongsToManyFragment()
                -> where('active','!=',2)
                -> where('test_results',1)
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
            return response() -> json([
                'fragments_count'=>$fragments_count,
                'watch_count' => $watch_count,
                'down_count' =>$down_count,
                'praise_count' =>$praise_count
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 最新和热门
     * @param $id
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function fraglists($id,Request $request)
    {
        try{
            //接收类型
            $type = $request ->get('type');

            // 获取要查询的 页数
            if(!is_numeric($page = $request -> get('page',1)))
                return response()->json(['error'=>'bad_request'],403);

            //搜索官方推荐
            $first_fragments = FragmentType::find($id)->belongsToManyFragment()->with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToUser'])
                ->where('recommend','=','1')
                ->where('test_results',1)
                ->where('active','!=',2)
                ->take(3)
                ->get();

           if($type == 0){
               //排行
               $second__fragments = FragmentType::find($id)->belongsToManyFragment()->with(['belongsToManyFragmentType'=>function($q){
                   $q->select('name');
               },'belongsToUser'])
                   -> where('active','!=',2)
                   -> where('test_results',1)
                   -> orderBy('watch_count', 'DESC')
                   -> forPage($page,$this->paginate)
                   -> get();

               //拼接
               $data = array_merge($first_fragments->toArray(),$second__fragments->toArray());

           }else{

               //最新
               $second__fragments = FragmentType::find($id)->belongsToManyFragment()->with(['belongsToManyFragmentType'=>function($q){
                   $q->select('name');
               },'belongsToUser'])
                   -> where('active','!=',2)
                   -> where('test_results',1)
                   -> orderBy('time_add', 'DESC')
                   -> forPage($page,$this->paginate)
                   -> get();

               //拼接
               $data = array_merge($first_fragments->toArray(),$second__fragments->toArray());
           }
            //响应
            $data = mult_unique($data);

            return $this->fragCollectTransformer->transform($data);

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * 片段详情
     * @param $fragmentId
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function fragmentdetails($fragmentId)
    {
        //获取片段数据
        $fragment = Fragment::find($fragmentId);

        //下载 + 1
        DB::table('fragment')->where('id','=',$fragmentId)->increment('count');

        // 观看 + 1
        DB::table('fragment')->where('id','=',$fragmentId)->increment('watch_count');

        //响应
        return response()->json([
            'url'   => CloudStorage::privateUrl_zip($fragment->zip_address),
            'size'  => $fragment->size,
        ],200);
    }

    /**
     * 使用开拍
     * @param $frag_id
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function useOrFilm($frag_id,Request $request)
    {
        try{
        //判断用户是否为登录状态
        $user = J_Auth::guard('api')->user();

        //判断片段是否收费
        $fragment_info = Fragment::find($frag_id);

        //片段不存在
        if (!$fragment_info){
            return response()->json(['error'=>'not found'],404);
        }

        //所需积分
        $integral = $fragment_info->intergral + 0;

        //所需金币
        $cost = $fragment_info->cost + 0;

        //会员是否收费
        $vip_isfree = $fragment_info->vipfree + 0;

        //免费片段
        if (!$integral && !$cost && !$vip_isfree){
            // 获取片段数据
            return  $this->fragmentdetails($frag_id);
        }elseif ($integral && !$cost && !$vip_isfree){   //需要积分  会员不收费
            //判断用户是否登录
            if (!$user){
                return resoponse()->json(['error'=>'Please log in']);
            }

//            判断用户是否是vip
            if ($user->is_vip){
                return  $this->fragmentdetails($frag_id);
            }else{

                //接收用户是否确认扣除积分
                $commit = $request->get('commit');

                $fragment = Fragment::find($frag_id);

                //是否购买过
                $is_exist = DB::table('user_integral_expend_log')
                    ->where('user_id','=',$user->id)
                    ->where('pay_count','=',$integral)
                    ->where('type_id','=',$frag_id)
                    ->where('pay_reason','=','片段:'.$fragment->name)
                    ->first();

                //已经购买过
                if ($is_exist) {
                    return $this->fragmentdetails($frag_id);
                }

                //需要提交动作
                if (empty($commit)){
                    return response()->json([
                        'error'     => 'need commit',
                        'integral'  => $integral,
                    ],403);
                }

                //确认扣除积分
                if ($commit == '1'){
                    $user_info = User\UserIntegral::where('user_id','=',$user->id)->first();
                    //用户积分为0
                    if (!$user_info) {
                        return response()->json(['message' => 'Integral is 0'], 403);
                    }
                    //用户积分
                    $user_integral = $user_info->integral_count;
                    //生成订单号
                    $number = date('YmdHis').rand(100000,999999);
                    //如果用户积分足够
                    if($user_integral >= $integral){
//                       开启事务
                        DB::beginTransaction();
                        //扣除积分
                        $integral_update = User\UserIntegral::where('user_id','=',$user->id)->update(['integral_count'=>$user_integral - $integral]);
                        if($integral_update){
                            //获取详细信息
                            $fragment = Fragment::find($frag_id);
                            //写入消费表
                            $result = DB::table('user_integral_expend_log')->insert([
                                     'user_id'    => $user->id,
                                     'pay_number' => $number,
                                     'pay_count'  => $integral,
                                     'type_id'    => $fragment->id,
                                     'pay_reason' => '片段:'.$fragment->name,
                                     'status'     => 1,
                                     'create_at'  => time(),
                                ]);
                            //返回数据
                            if ($result){
                                DB::commit();
                                return $this->fragmentdetails($frag_id);
                            }else{
                                DB::rollBack();
                               return response() -> json(['error'=>'Try again later'], 500);
                            }

                        }else{
                            DB::rollBack();
                           return response() -> json(['error'=>'Try again later'], 500);
                        }

                    }else{
                        //用户所剩的积分数
                        $user_info = User\UserIntegral::where('user_id','=',$user->id)->first();
                        $user_integral = $user_info->integral_count;

                        //用户积分为0
                        if (!$user_integral) {
                            return response()->json([
                                'message'=>'Sorry Underbalance',
                                'user_integral' => $user_integral,
                            ], 403);
                        }
                    }

                }else if($commit == '2'){                     //取消购买
                   return ['message'=>'Successfully Canceled'];
                }else{
                   return response() -> json(['message'=>'Need commit'], 103);
                }
            }
        }elseif ($integral && !$cost && $vip_isfree){     //会员是否收费   1

            //判断用户是否登录
            if (!$user){
                return resoponse()->json(['error'=>'Please log in']);
            }

            $fragment = Fragment::find($frag_id);

            //是否购买过
            $is_exist = DB::table('user_integral_expend_log')
                ->where('user_id','=',$user->id)
                ->where('pay_count','=',$integral)
                ->where('type_id','=',$frag_id)
                ->where('pay_reason','=','片段:'.$fragment->name)
                ->first();

            //已经购买过
            if ($is_exist) {
                return $this->fragmentdetails($frag_id);
            }

            //接收用户是否确认扣除积分
            $commit = $request->get('commit');

            //需要提交动作
            if (empty($commit)){
                return response()->json([
                    'error'     => 'need commit',
                    'integral'  => $integral,
                ],403);
            }

            //确认扣除积分
            if ($commit == '1'){
                //从积分表获取用户积分
                $user_info = User\UserIntegral::where('user_id','=',$user->id)->first();

                //用户积分为0
                if (!$user_info) {
                    return response()->json(['message' => 'Integral is 0'], 403);
                }

                $user_integral = $user_info->integral_count;
                $number = date('YmdHis').rand(100000,999999);

                //如果用户积分足够
                if($user_integral >= $integral){
//                       开启事务
                    DB::beginTransaction();

                    //扣除积分
                    $integral_update = User\UserIntegral::where('user_id','=',$user->id)->update(['integral_count'=>$user_integral - $integral]);

                    if($integral_update){

                            //获取详细信息
                            $fragment = Fragment::find($frag_id);

                            //写入消费表
                            $result = DB::table('user_integral_expend_log')->insert([
                                'user_id'    => $user->id,
                                'pay_number' => $number,
                                'pay_count'  => $integral,
                                'type_id'    => $fragment->id,
                                'pay_reason' => '片段:'.$fragment->name,
                                'status'     => 1,
                                'create_at'  => time(),
                            ]);

                            //返回数据
                            if ($result){
                                DB::commit();

                                return $this->fragmentdetails($frag_id);
                            }else{
                                DB::rollBack();
                                return response() -> json(['error'=>'Try again later'], 500);
                            }
                        }else{
                            DB::rollBack();
                            return response() -> json(['message'=>'Try again later'], 500);
                        }

                }else{
                    //用户所剩的积分数
                    $user_info = User\UserIntegral::where('user_id','=',$user->id)->first();
                    $user_integral = $user_info->integral_count;

                    return response() -> json([
                        'user_integral' => $user_integral,
                        'message' => 'Sorry Underbalance',
                    ], 403);
                }

            }else if($commit == '2'){                     //取消购买
		            return ['message'=>'Successfully Canceled'];
            }else{
                   return response() -> json(['message'=>'Need to purchase'], 103);
            }
        }
        //TODO    会员不免费需要金币

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 观摩
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function watch($id,Request $request)
    {
        try {
            $user = J_Auth::guard('api')->user();

            //接受页数
            $page = (int)$request->get('page', 1);

            if($page==1) {

                //获取官推片段动态
                $top_tweets = TweetHot::top()->pluck('tweet_id');

                // 如果置顶动态id多于2条，随机取2条置顶的动态
                if ($top_tweets->count() > 2) {
                    $top_tweets = $top_tweets->random(2);
                }

                //获取推荐动态
                $recommend_tweets = TweetHot::recommend()->whereNotIn('tweet_id', $top_tweets->all())->pluck('tweet_id');

                // 如果推荐动态id多于4条，随机取4条动态
                if ($recommend_tweets->count() > 4) {
                    $recommend_tweets = $recommend_tweets->random(4);
                }

                // 合并置顶和推荐的数组
                $special_ids = array_merge($top_tweets->all(), $recommend_tweets->all());

                // 初始化
                $special_data = [];
                //判断
                if (isset($special_ids[0])) {
                    // 获取相关热门动态的数据
                    foreach ($special_ids as $v) {
                        $special_tweets = Tweet::whereType(0)
                            ->where('visible', '=', 0)
                            ->where('id', $v)
                            ->where('fragment_id', $id)
//                            ->where('')
                            ->get(['id', 'type', 'user_id', 'fragment_id', 'duration', 'size', 'location', 'photo', 'screen_shot', 'video', 'created_at']);

                        // 官方推荐动态
                        $special_data[] = $this->channelTweetsTransformer->transformCollection($special_tweets->all())[0];
                    }
                }

                //公开状态的
                $third_tweets = Tweet::where('active', '=', 1)
                    ->where('fragment_id', '=', $id)
                    ->where('visible', '=', 0)
                    ->with([
                        'hasOneContent' => function ($query) {
                            $query->select(['tweet_id', 'content']);
                        },
                        'belongsToUser' => function ($q) {
                            $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                        }])
                    ->forPage($page, $this->paginate)
                    ->whereNotIn('id', $special_ids)
                    ->orderBy('browse_times', 'desc')
                    ->get(['id', 'type', 'user_id', 'fragment_id', 'duration', 'size', 'location', 'photo', 'screen_shot', 'video', 'created_at']);

                $third_tweets = $this->channelTweetsTransformer->transformCollection($third_tweets->all());

                //如果用户未登录
                if (!$user){

                    $datas = array_merge($special_data,$third_tweets);
                    $data = mult_unique($datas);

                    $count = Tweet::where('active', '=', 1)
                        ->where('fragment_id', '=', $id)
                        ->where('visible', '=', 0)
                        ->count();

                    if ($request->get('type') == 2) {
                        return response()->json([
                            'count' => $count,
                            'page_count' => ceil(count($data) / $this->paginate),
                            'data' => $data,
                        ]);
                    }

                    return response()->json([
                        'page_count' => ceil(count($data) / $this->paginate),
                        'data' => $data,
                    ]);

                }

                //如果为登录状态
                if ($user) {
                    //设置为好友可见
                    $tweets = Tweet::where('fragment_id', '=', $id)
                        ->where('active', '=', 1)
                        ->where('visible', '=', 1)
                        ->pluck('id')->all();

                    $user_ids = [];
                    foreach ($tweets as $k => $v) {
                        $user_ids[] = Tweet::find($v)->user_id;
                    }

                    $second_tweets = [];
                    foreach ($user_ids as $k => $v) {
                        $first = Friend::where('from', '=', $user->id)->where('to', '=', $v)->first();

                        if ($first) {
                            $second = Friend::where('from', '=', $v)->where('to', '=', $user->id)->first();

                            if ($second) {
                                //好友数据
                                $second_tweets[] = Tweet::where('id', '=', $tweets[$k])
                                    ->with([
                                        'hasOneContent' => function ($query) {
                                            $query->select(['tweet_id', 'content']);
                                        },
                                        'belongsToUser' => function ($q) {
                                            $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                                        }])
                                    ->forPage($page, $this->paginate)
                                    ->get();
                            }
                            //好友动态
                            $second_tweet[] = $this->channelTweetsTransformer->transformCollection($second_tweets[0]->all())[0];
                        }
                    }

                    $datas = array_merge($special_data,$second_tweet, $third_tweets);
                    $data = mult_unique($datas);

                    $count = Tweet::where('active', '=', 1)
                        ->where('fragment_id', '=', $id)
                        ->where('visible', '=', 0)
                        ->count();

                    if ($request->get('type') == 2) {
                        return response()->json([
                            'count' => $count,
                            'page_count' => ceil(count($data) / $this->paginate),
                            'data' => $data,
                        ]);
                    }

                    return response()->json([
                        'page_count' => ceil(count($data) / $this->paginate),
                        'data' => $data,
                    ]);

                }

            }else {
                if ($request->get('type') == 1) {
                    //如果不是第一页
                    $five_tweets = Tweet::where('active', '=', 1)
                        ->where('fragment_id', '=', $id)
                        ->where('visible', '=', 0)
                        ->with([
                            'hasOneContent' => function ($query) {
                                $query->select(['tweet_id', 'content']);
                            },
                            'belongsToUser' => function ($q) {
                                $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                            }])
                        ->forPage($page, $this->paginate)
                        ->orderBy('browse_times', 'desc')
                        ->get(['id', 'type', 'user_id', 'fragment_id', 'duration', 'size', 'location', 'photo', 'screen_shot', 'video', 'created_at']);
                    $five_tweet = $this->channelTweetsTransformer->transformCollection($five_tweets->all());

                    //如果为登录状态
                    if ($user) {
                        //设置为好友可见
                        $tweets = Tweet::where('fragment_id', '=', $id)
                            ->where('active', '=', 1)
                            ->where('visible', '=', 1)
                            ->pluck('id')->all();

                        $user_ids = [];
                        foreach ($tweets as $k => $v) {
                            $user_ids[] = Tweet::find($v)->user_id;
                        }

                        $second_tweets = [];
                        foreach ($user_ids as $k => $v) {
                            $first = Friend::where('from', '=', $user->id)->where('to', '=', $v)->first();

                            if ($first) {
                                $second = Friend::where('from', '=', $v)->where('to', '=', $user->id)->first();

                                if ($second) {
                                    //好友数据
                                    $second_tweets[] = Tweet::where('id', '=', $tweets[$k])
                                        ->with([
                                            'hasOneContent' => function ($query) {
                                                $query->select(['tweet_id', 'content']);
                                            },
                                            'belongsToUser' => function ($q) {
                                                $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                                            }])
                                        ->forPage($page, $this->paginate)
                                        ->get();
                                }
                                //好友动态
                                if(empty($second_tweets))
                                {
                                    $second_tweet = [];
                                }
                                $second_tweet[] = $this->channelTweetsTransformer->transformCollection($second_tweets[0]->all())[0];
                            }
                        }
                    }

                    if(!$user){
                        return response()->json([
                            'page_count' => ceil(count($five_tweet) / $this->paginate),
                            'data' => $five_tweet,
                        ]);
                    }else{
                        $datas = array_merge($second_tweet,$five_tweet);

                        $data = mult_unique($datas);

                        return response()->json([
                            'page_count' => ceil(count($data) / $this->paginate),
                            'data' => $data,
                        ]);
                    }
                } else {
                    //如果不是第一页
                    $six_tweets = Tweet::where('active', '=', 1)
                        ->where('fragment_id', '=', $id)
                        ->where('visible', '=', 0)
                        ->with([
                            'hasOneContent' => function ($query) {
                                $query->select(['tweet_id', 'content']);
                            },
                            'belongsToUser' => function ($q) {
                                $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                            }])
                        ->forPage($page, $this->paginate)
                        ->orderBy('tweet_grade_total', 'desc')
                        ->get(['id', 'type', 'user_id', 'fragment_id', 'duration', 'size', 'location', 'photo', 'screen_shot', 'video', 'created_at']);
                    $six_tweet = $this->channelTweetsTransformer->transformCollection($six_tweets->all());

                    //如果为登录状态
                    if ($user) {
                        //设置为好友可见
                        $tweets = Tweet::where('fragment_id', '=', $id)
                            ->where('active', '=', 1)
                            ->where('visible', '=', 1)
                            ->pluck('id')->all();

                        $user_ids = [];
                        foreach ($tweets as $k => $v) {
                            $user_ids[] = Tweet::find($v)->user_id;
                        }

                        $second_tweets = [];
                        foreach ($user_ids as $k => $v) {
                            $first = Friend::where('from', '=', $user->id)->where('to', '=', $v)->first();

                            if ($first) {
                                $second = Friend::where('from', '=', $v)->where('to', '=', $user->id)->first();

                                if ($second) {
                                    //好友数据
                                    $second_tweets[] = Tweet::where('id', '=', $tweets[$k])
                                        ->with([
                                            'hasOneContent' => function ($query) {
                                                $query->select(['tweet_id', 'content']);
                                            },
                                            'belongsToUser' => function ($q) {
                                                $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                                            }])
                                        ->forPage($page, $this->paginate)
                                        ->get();
                                }
                                //好友动态
                                if(empty($second_tweets))
                                {
                                    $second_tweet = [];
                                }
                                $second_tweet[] = $this->channelTweetsTransformer->transformCollection($second_tweets[0]->all())[0];
                            }
                        }
                    }

                    if(!$user){
                        return response()->json([
                            'page_count' => ceil(count($six_tweet) / $this->paginate),
                            'data' => $six_tweet,
                        ]);
                    }else{
                        $datas = array_merge($second_tweet,$six_tweet);

                        $data = mult_unique($datas);

                        return response()->json([
                            'page_count' => ceil(count($data) / $this->paginate),
                            'data' => $data,
                        ]);
                    }

                    $count = Tweet::where('active', '=', 1)
                        ->where('fragment_id', '=', $id)
                        ->where('visible', '=', 0)
                        ->count();

                    return response()->json([
                        'count' => $count,
                        'page_count' => ceil(count($data) / $this->paginate),
                        'data' => $data,
                    ]);
                }

            }
        }catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 搜索
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try{
            //接收页数
            $page = $request->get('page',1);

            //接收关键词
            $keyword = preg_replace('# #','',$request->get('keyword'));

            //判断用户是否登录
            $user = J_Auth::guard('api')->user();

            if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $keyword)>0) {

                //总搜索次数+1
                DB::table('keywords')->where('keyword','=',$keyword)->increment('count_sum_pv');

                //日搜索+1
                DB::table('keywords')->where('keyword','=',$keyword)->increment('count_day_pv');

                //周搜索 +1
                DB::table('keywords')->where('keyword','=',$keyword)->increment('count_week_pv');


               if( $keyword != Cache::get( getIP() ) ){
                   //总搜索次数+1
                   DB::table('keywords')->where('keyword','=',$keyword)->increment('count_sum');

                   //日搜索+1
                   DB::table('keywords')->where('keyword','=',$keyword)->increment('count_day');

                   //周搜索 +1
                   DB::table('keywords')->where('keyword','=',$keyword)->increment('count_week');

               }

                //将IP写入缓存
                $ip = getIP();

                Cache::add($ip,$keyword,'10');

                if ($user) {
                    //写入用户喜好
                    $user_keywords_ids = Keywords::WhereHas('belongtoManyUser', function ($q) use ($user) {
                        $q->where('user_id', '=', $user->id);
                    })->where('keyword', '=', $keyword)->first(['id']);

                    //如果用户喜好不存在  则存入
                    if (!$user_keywords_ids) {
                        $keyword_id = Keywords::where('keyword', '=', $keyword)->pluck('id');

                        if ($keyword_id->all()) {
                            $user_keywords_count = UserKeywords::where('user_id', '=', $user->id)
                                ->orderBy('create_time', 'asc')
                                ->get();

                            //如果用户喜好大于50
                            if ($user_keywords_count->count() > 50) {
                                $user_keywords_last = UserKeywords::where('user_id', '=', $user->id)
                                    ->orderBy('create_time', 'asc')
                                    ->take(1)
                                    ->delete();
                            }

                            $new_keyword = new UserKeywords();

                            $new_keyword->user_id = $user->id;

                            $new_keyword->keyword_id = $keyword_id->all()[0];

                            $new_keyword->create_time = time();

                            $new_keyword->save();
                        }
                    }
                }

                //官方置顶
                $top_fragment_id = Fragment::WhereHas('keyWord', function ($q) use ($keyword) {
                    $q->where('keyword', '=', $keyword);
                })
                    ->where('ishot', '=', 1)
                    ->where('ishottime', '>', time())
                    ->where('active', '=', 1)
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->pluck('id');

                if ($top_fragment_id->count() > 2) {
                    $top_fragment_id = $top_fragment_id->random(2);
                }

                //官方推荐
                $recommend_fragment_id = Fragment::WhereHas('keyWord', function ($q) use ($keyword) {
                    $q->where('keyword', '=', $keyword);
                })
                    ->where('recommend', '=', 1)
                    ->whereNotIn('id', $top_fragment_id)
                    ->where('active', '=', 1)
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->pluck('id');

                if ($recommend_fragment_id->count() > 4) {
                    $recommend_fragment_id = $recommend_fragment_id->random(4);
                }

                $first_fragment_id = array_merge($top_fragment_id->toArray(), $recommend_fragment_id->toArray());

                //官方推荐和置顶
                $first_fragment_info = Fragment::with([
                    'belongsToUser' => function ($q) {
                        $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'verify_info', 'signature']);
                    }, 'belongsToManyFragmentType'
                ])
                    ->whereIn('id', $first_fragment_id)
                    ->get();

                //搜索相关
                $second_fragment_info = Fragment::WhereHas('keyWord', function ($q) use ($keyword) {
                    $q->where('keyword', '=', $keyword);
                })
                    ->with([
                        'belongsToUser' => function ($q) {
                            $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'verify_info', 'signature']);
                        }, 'belongsToManyFragmentType'
                    ])
                    ->forPage($page, $this->paginate)
                    ->where('active', '=', 1)
                    ->whereNotIn('id', $first_fragment_id)
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orderBy('watch_count', 'DESC')
                    ->get();

                if ($page == 1) {
                    $data = array_merge($first_fragment_info->toArray(), $second_fragment_info->toArray());

                    if (empty($data)) {
                        return response()->json([
                            'error' => 'Not found'
                        ], 404);
                    }

                    return response()->json([
                        'data' => $this->fragCollectTransformer->searchtransform($data),
                    ], 200);

                }

                return response()->json([
                    'data' => $this->fragCollectTransformer->searchtransform($second_fragment_info->toArray()),
                ]);

            }else{
                //接收用户搜索的内容
                $keyword_pin =  preg_replace('# #','',CUtf8_PY::encode($request->get('keyword'),'all'));

                //总搜索次数+1
                DB::table('keywords')->where('keyword_pinyin','=',$keyword_pin)->increment('count_sum_pv');

                //日搜索+1
                DB::table('keywords')->where('keyword_pinyin','=',$keyword_pin)->increment('count_day_pv');

                //周搜索 +1
                DB::table('keywords')->where('keyword_pinyin','=',$keyword_pin)->increment('count_week_pv');

                if( $keyword != Cache::get( getIP() ) ){
                    //总搜索次数+1
                    DB::table('keywords')->where('keyword_pinyin','=',$keyword_pin)->increment('count_sum');

                    //日搜索+1
                    DB::table('keywords')->where('keyword_pinyin','=',$keyword_pin)->increment('count_day');

                    //周搜索 +1
                    DB::table('keywords')->where('keyword_pinyin','=',$keyword_pin)->increment('count_week');

                }

                //将IP写入缓存
                $ip = getIP();

                Cache::add($ip,'$keyword_pin','10');

                if ($user) {
                    //写入用户喜好
                    $user_keywords_ids = Keywords::WhereHas('belongtoManyUser', function ($q) use ($user) {
                        $q->where('user_id', '=', $user->id);
                    })
                        ->where('keyword_pinyin', '=', $keyword_pin)
                        ->first(['id']);

                    //如果用户喜好不存在  则存入
                    if (!$user_keywords_ids) {
                        $keyword_id = Keywords::where('keyword_pinyin', '=', $keyword_pin)->pluck('id');

                        if ($keyword_id->all()) {
                            $user_keywords_count = UserKeywords::where('user_id', '=', $user->id)
                                ->orderBy('create_time', 'asc')
                                ->get();

                            //如果用户喜好大于50
                            if ($user_keywords_count->count() > 50) {
                                $user_keywords_last = UserKeywords::where('user_id', '=', $user->id)
                                    ->orderBy('create_time', 'asc')
                                    ->take(1)
                                    ->delete();
                            }

                            $new_keyword = new UserKeywords();

                            $new_keyword->user_id = $user->id;

                            $new_keyword->keyword_id = $keyword_id->all()[0];

                            $new_keyword->create_time = time();

                            $new_keyword->save();
                        }
                    }
                }

                //官方置顶
                $top_fragment_id = Fragment::WhereHas('keyWord', function ($q) use ($keyword_pin) {
                    $q->where('keyword_pinyin', '=', $keyword_pin);
                })
                    ->where('ishot', '=', 1)
                    ->where('ishottime', '>', time())
                    ->where('active', '=', 1)
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->pluck('id');

                if ($top_fragment_id->count() > 2) {
                    $top_fragment_id = $top_fragment_id->random(2);
                }

                //官方推荐
                $recommend_fragment_id = Fragment::WhereHas('keyWord', function ($q) use ($keyword_pin) {
                    $q->where('keyword_pinyin', '=', $keyword_pin);
                })
                    ->where('recommend', '=', 1)
                    ->whereNotIn('id', $top_fragment_id)
                    ->where('active', '=', 1)
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->pluck('id');

                if ($recommend_fragment_id->count() > 4) {
                    $recommend_fragment_id = $recommend_fragment_id->random(4);
                }

                $first_fragment_id = array_merge($top_fragment_id->toArray(), $recommend_fragment_id->toArray());

                //官方推荐和置顶
                $first_fragment_info = Fragment::with([
                    'belongsToUser' => function ($q) {
                        $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'verify_info', 'signature']);
                    }, 'belongsToManyFragmentType'
                ])
                    ->whereIn('id', $first_fragment_id)
                    ->get();

                //搜索相关
                $second_fragment_info = Fragment::WhereHas('keyWord', function ($q) use ($keyword_pin) {
                    $q->where('keyword_pinyin', '=', $keyword_pin);
                })
                    ->with([
                        'belongsToUser' => function ($q) {
                            $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'verify_info', 'signature']);
                        }, 'belongsToManyFragmentType'
                    ])
                    ->forPage($page, $this->paginate)
                    ->where('active', '=', 1)
                    ->whereNotIn('id', $first_fragment_id)
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orderBy('watch_count', 'DESC')
                    ->get();

                if ($page == 1) {
                    $data = array_merge($first_fragment_info->toArray(), $second_fragment_info->toArray());

                    if (empty($data)) {
                        return response()->json([
                            'error' => 'Not found'
                        ], 404);
                    }

                    return response()->json([
                        'data' => $this->fragCollectTransformer->searchtransform($data),
                    ], 200);

                }

                return response()->json([
                    'data' => $this->fragCollectTransformer->searchtransform($second_fragment_info->toArray()),
                ]);
            }
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }
    }

    //相关片段
    public function correlation($id,Request $request)
    {
        try {
            if (!is_numeric($page = $request->get('page', 1))) {
                return response()->json(['error' => 'bad_request'], 403);
            }

            $keywords_ids = KeywordFragment::where('fragment_id', '=', $id)->pluck('keyword_id')->all();

            $fragment = Fragment::WhereHas('keyWord', function ($q) use ($keywords_ids) {
                $q->whereIn('keyword_id', $keywords_ids);
            })
                ->with(['belongsToManyFragmentType' => function ($q) {
                    $q->select('name');
                }, 'belongsToUser'])
                ->where('id', '!=', $id)
                ->where('active', '!=', '2')
                ->where('test_results', 1)
                ->orderBy('watch_count', 'DESC')
                ->forPage($page, $this->paginate)
                ->get();

            $user = J_Auth::guard('api')->user();

            $user_fragment = [];
            //搜索用户喜好
            if ($user) {
                $user_keywords_ids = UserKeywords::where('user_id', '=', $user->id)->pluck('id');

                //按喜好查询
                if ($user_keywords_ids->all()) {

                    $user_fragment = Fragment::WhereHas('keyWord', function ($q) use ($user_keywords_ids) {
                        $q->whereIn('keyword_id', $user_keywords_ids);
                    })
                        ->with(['belongsToManyFragmentType' => function ($q) {
                            $q->select('name');
                        }, 'belongsToUser'])
                        ->where('id', '!=', $id)
                        ->where('active', '!=', '2')
                        ->where('test_results', 1)
                        ->whereNotIn('id', $keywords_ids)
                        ->orderBy('watch_count', 'DESC')
                        ->forPage($page, $this->paginate)
                        ->get();
                }
            }

            if ($user) {
                if ($fragment->toArray()) {
                    return response()->json([
                        'fragment_data' => $this->fragCollectTransformer->transform($fragment->toArray()),
                    ], 200);
                } else {
                    return response()->json([
                        'fragment_data' => $this->fragCollectTransformer->transform($user_fragment->toArray()),
                    ], 200);
                }

            } else {
                if($fragment->toArray()){
                    return response()->json([
                        'fragment_data' => $this->fragCollectTransformer->transform($fragment->toArray()),
                    ], 200);
                }else{
                    return [];
                }

            }
        }catch (\Exception $e){
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }

    }
}
