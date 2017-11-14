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
            $official_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToManyUser'=>function($q){
                $q->select('nickname','avatar','verify','verify_info','cover','signature');
            }])
                ->where('recommend','=','1')
                ->where('active','!=','2')
                ->where('test_results',1)
                ->take(3)
                ->get();

            //随机取出数据
            $rand_fragment = Fragment::with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToManyUser'=>function($q){
                $q->select('nickname','avatar','verify','verify_info','cover','signature');
            }])
                ->where('active','!=','2')
                ->where('test_results',1)
                -> forPage($page,$this->paginate)
                ->get();

            //按街道进行搜索
            $address_street_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToManyUser'=>function($q){
                $q->select('nickname','avatar','verify','verify_info','cover','signature');
            }])
                ->where('address_street','=',$address_street)
                ->where('active','!=','2')
                ->where('test_results',1)
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
                $address_county_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                    $q->select('name');
                },'belongsToManyUser'=>function($q){
                    $q->select('nickname','avatar','verify','verify_info','cover','signature');
                }])
                    ->where('address_county','=',$address_county)
                    ->where('active','!=','2')
                    ->where('test_results',1)
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
                    $address_city_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                        $q->select('name');
                    },'belongsToManyUser'=>function($q){
                        $q->select('nickname','avatar','verify','verify_info','cover','signature');
                    }])
                        ->where('address_city','=',$address_city)
                        ->where('active','!=','2')
                        ->where('test_results',1)
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
                    $address_province_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                        $q->select('name');
                    },'belongsToManyUser'=>function($q){
                        $q->select('nickname','avatar','verify','verify_info','cover','signature');
                    }])
                        ->where('address_province','=',$address_province)
                        ->where('active','!=','2')
                        ->where('test_results',1)
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
                    $address_country_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
                        $q->select('name');
                    },'belongsToManyUser'=>function($q){
                        $q->select('nickname','avatar','verify','verify_info','cover','signature');
                    }])
                        ->where('address_country','=',$address_country)
                        ->where('active','!=','2')
                        ->where('test_results',1)
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

            if ($count){
               return response() -> json([
                    // 应取数据的条数
                    'count'      => $this->paginate,
                    'classify_data'=>$this->ClassifyCollection($classifys),
                    'fragment_data' =>  $this->fragCollectTransformer->transform($data),
                ], 200);
            }else{
		return response() -> json(['error'=>'not_found'], 404);
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
        return array_map([$this,'classifytransform'],$items);
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
        $address_street_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
            $q->select('name');
        },'belongsToManyUser'=>function($q){
            $q->select('nickname','avatar','verify','verify_info','cover','signature');
        }])
            ->where('address_street','=',$address_street)
            ->where('active','!=','2')
            ->where('test_results',1)
            ->orderBy('count', 'desc')
            ->get();

        //按区搜索
        $address_county_fragments = Fragment::with(['belongsToManyFragmentType'=>function($q){
            $q->select('name');
        },'belongsToManyUser'=>function($q){
            $q->select('nickname','avatar','verify','verify_info','cover','signature');
        }])
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
            },'belongsToManyUser'=>function($q){
                $q->select('nickname','avatar','verify','verify_info','cover','signature');
            }])
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
                },'belongsToManyUser'=>function($q){
                    $q->select('nickname','avatar','verify','verify_info','cover','signature');
                }])
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
                    },'belongsToManyUser'=>function($q){
                        $q->select('nickname','avatar','verify','verify_info','cover','signature');
                    }])
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
            $users = Auth::guard('api')->user();

            //如果用户未登录
            if (empty( $users)){
               return response() -> json(['error'=>'not_login'], 403);
            }

            $user = User::with(['belongsToManyFragment'=>function($q){
                $q->with(['belongsToManyFragmentType'=>function($q){
                    $q->select('name');
                },'belongsToManyUser'=>function($q){
                    $q->select('nickname','avatar','verify','verify_info','cover','signature');
                }])->where('way',2)->where('test_results',1);
            }])->find($users->id)->belongsToManyFragment;

            if ($user->toArray()){
                return response() -> json([
                    'data'=>$this->fragCollectTransformer->transform($user->toArray())
                  ], 200);
            }else{
                return response() -> json(['error'=>'not_found'], 404);
            }

        }catch(\Exception $e) {
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
               // return response() -> json(['error'=>'not_login'], 403);
            }

           $user = User::with(['belongsToManyFragment'=>function($q){
                $q->with(['hasManyStoryboard'=>function($a){
                    $a->orderBy('sort','asc');
                },'belongsToManyFragmentType'=>function($q){
                    $q->select('name');
                },'belongsToManyUser'=>function($q){
                    $q->select('nickname','avatar','verify','verify_info','cover','signature');
                },'hasManySubtitle'=>function($q){
                    $q->orderBy('start_time','asc');
                }])->where('way',2)->where('test_results',1);
            }])->find(1000240)->belongsToManyFragment;

            if ($user->toArray()){
                return response() -> json([
                            'data'=>$this->fragCollectTransformer->downtransform($user->toArray())
                        ], 200);
            }else{
		        return response() -> json(['error'=>'not_found'], 404);
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
            },'belongsToManyUser'=>function($q){
                $q->select('nickname','avatar','verify','verify_info','cover','signature');
            }])
                ->where('recommend','=','1')
                ->where('test_results',1)
                ->where('active','!=',2)
                ->take(3)
                ->get();

           if($type == 0){
               //排行
               $second__fragments = FragmentType::find($id)->belongsToManyFragment()->with(['belongsToManyFragmentType'=>function($q){
                   $q->select('name');
               },'belongsToManyUser'=>function($q){
                   $q->select('nickname','avatar','verify','verify_info','cover','signature');
               }])
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
               },'belongsToManyUser'=>function($q){
                   $q->select('nickname','avatar','verify','verify_info','cover','signature');
               }])
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
            $first_fragments = FragmentType::find($id)->belongsToManyFragment()->with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToManyUser'=>function($q){
                $q->select('nickname','avatar','verify','verify_info','cover','signature');
            }])
                ->where('recommend','=','1')
                -> where('test_results',1)
                ->where('active','!=',2)
                ->take(3)
                ->get();

            //最新
            $second__fragments = FragmentType::find($id)->belongsToManyFragment()->with(['belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'belongsToManyUser'=>function($q){
                $q->select('nickname','avatar','verify','verify_info','cover','signature');
            }])
                -> where('active','!=',2)
                -> where('test_results',1)
                -> orderBy('time_add', 'DESC')
                -> forPage($page,$this->paginate)
                -> get();

            //拼接
            $data = array_merge($first_fragments->toArray(),$second__fragments->toArray());

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
            if(!count($data)){
                return response() -> json(['error'=>'not_found'], 404);
            }

            $data = mult_unique($data);

            //响应
            return response() -> json([
                'fragments_count'=>$fragments_count,
                'watch_count' => $watch_count,
                'down_count' =>$down_count,
                'praise_count' =>$praise_count,
                'data'=> $this->fragCollectTransformer->transform($data)
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * 片段预览
     * @param $id
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function fragdetail($id)
    {
        try{

            // 判断用户是否为登录状态
            $user = Auth::guard('api')->user();

            if (empty($user)){
          //      return response() -> json(['error'=>'no login'], 403);
            }

        $fragment = Fragment::with(['belongsToManyUser','belongsToManyFragmentType'=>function($q){
                $q->select('name');
            }])->find($id);

            DB::table('fragment')->where('id','=',$id)->increment('watch_count');

            return $this->fragmentDetailTransformer->transform($fragment);

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 片段详情
     * @param $fragmentId
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function  fragmentdetails($fragmentId)
    {
        try{
            //获取片段数据
            $fragment = Fragment::with(['belongsToManyUser','belongsToManyFragmentType'=>function($q){
                $q->select('name');
            },'hasManyStoryboard'=>function($a){
                $a->orderBy('sort','asc');
            },'hasManySubtitle'=>function($q){
                $q->orderBy('start_time','asc');
            }])->where('test_results','=',1)
                ->find($fragmentId);

            if (empty($fragment)){
                return response()->json(['error'=>'not found'],404);
            }

            //下载 + 1
            DB::table('fragment')->where('id','=',$fragmentId)->increment('count');

            // 观看 + 1
            DB::table('fragment')->where('id','=',$fragmentId)->increment('watch_count');

            //响应
            return $this->fragmentDetailTransformer->usetransform($fragment);

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
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
        $user = Auth::guard('api')->user();
            $id = 1000240;
            $user = User::find($id);
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

            //判断下载记录表中数据
            $is_exist = DB::table('fragment_user_collect')
                ->where('user_id','=',$user->id)
                ->where('fragment_id','=',$frag_id)
                ->first();

            //如果已经下载过
            if ($is_exist){
                //获取片段数据
              return  $this->fragmentdetails($frag_id);
            }

            //写入下载表
            DB::table('fragment_user_collect')->insert([
                'user_id' => $user->id,
                'fragment_id' => $frag_id,
                'create_at' => time(),
                'way' => 2,
            ]);

            // 获取片段数据
              $fragment = $this->fragmentdetails($frag_id);

            return $this->fragmentDetailTransformer->usetransform($fragment);

        }elseif ($integral && !$cost && !$vip_isfree){   //需要积分  会员不收费
//            判断用户是否是vip

            if ($user->is_vip){

                //是否下载过
                $is_exist = DB::table('fragment_user_collect')
                    ->where('user_id','=',$user->id)
                    ->where('fragment_id','=',$frag_id)
                    ->first();

                   if(!$is_exist) {
                       //获取片段数据
                       return  $this->fragmentdetails($frag_id);
                   }

                // 获取片段数据
                $fragment = $this->fragmentdetails($frag_id);

                return response() -> json([
                    'data' => $this->fragmentDetailTransformer->transform($fragment)
                ], 200);

            }else{

                //接收用户是否确认扣除积分
                $commit = $request->get('commit');

                //是否下载过
                $is_exist = DB::table('fragment_user_collect')
                    ->where('user_id','=',$user->id)
                    ->where('fragment_id','=',$frag_id)
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

                                //写入下载表
                                DB::table('fragment_user_collect')->insert([
                                    'user_id' => $user->id,
                                    'fragment_id' => $frag_id,
                                    'create_at' =>time(),
                                    'way' => 2,
                                ]);

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

            //从消费表查看是否购买过
            $res = User\UserIntegralExpend::where('user_id',$user->id)
                -> where('type_id',$frag_id)
                -> where('status',1)
                ->first();

            //已经购买过
            if ($res){
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
                            //写入下载表
                            DB::table('fragment_user_collect')->insert([
                                'user_id' => $user->id,
                                'fragment_id' => $frag_id,
                                'create_at' =>time(),
                                'way' => 2,
                            ]);

                            DB::commit();
                            return $this->fragmentdetails($frag_id);
                        }else{
                            DB::rollBack();
                            return response() -> json(['message'=>'Try again later'], 500);
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

            }else if($commit == '2'){                //取消购买
		  return response() -> json(['message'=>'Successfully Canceled'], 204);               
            }else{
                   return response() -> json(['message'=>'Need to purchase'], 103);
            }
        }
        //TODO    会员不免费

        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

}
