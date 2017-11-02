<?php

namespace App\Api\Controllers;

use App\Models\Fragment;
use App\Models\User;
use App\Models\FragmentType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

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

    /**
     * 首页
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // 获取要查询的关键词 及 所取页数
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
//            dd($rand_fragment[0]->keyWord);

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
                    'fragment_data' => $data,
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
            'icon'=>$classify['icon'],
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
        $address_street = '呼家楼街道';
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
                'fragment_data' => $fragment_data
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
            $user = Auth::guard('api')->user();

            //如果用户未登录
  /*          if (!$user){
                return  [
                    'status_code' =>400,
                    'message' => 'Not logged in'
                ];
            }   */

            $a = User::with(['belongsToManyFragment'=>function ($q){
                $q->select('cover','name')
            }])->find(1000234);

            dd($a);


        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }






}
