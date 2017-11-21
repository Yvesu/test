<?php

namespace App\Http\Controllers\NewAdmin\Fodder;

use App\Models\Admin\Administrator;
use App\Models\Fragment;
use App\Models\FragmentType;
use App\Models\FragmentTypeFragment;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class FragmentController extends Controller
{

    private $paginate = 20;
    //

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 片段主页
     */
    public function index(Request $request)
    {
        try{
            //  搜索条件
            //  关键字
            $name = $request->get('name');
            //  类别
            $type = $request->get('type_id','');
            //  操作员
            $operator = $request->get('operator_id','');
            //  下载量 0 为0  1 为 100  2 为 500 3 为1000   4 为5000  5 为1W  6 为5 W
            $count = $request->get('count',0);
            //  播放时长
            $duration = $request->get('duration','00:00');
            //  费用
            $integral = $request->get('integral','');
            //  发布时间
            $time = $request->get('time');
            //  页码
            $page = $request->get('page',1);
            switch ($time){
                case 0:
                    $time = 0;
                    break;
                case 1:
                    $time = strtotime(date('Y-m-d',time()));
                    break;
                case 2:
                    $time = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                    break;
                case 3:
                    $time = mktime(0,0,0,date('m'),1,date('Y'));
                    break;
                default:
                    $time = 0;
            }
            // 播放时长变形
            switch ($duration){
                case 0:
                    $duration = '00:00';
                    break;
                case 1:
                    $duration = '10:00';
                    break;
                case 2:
                    $duration = '30:00';
                    break;
                case 3:
                    $duration = '60:00';
                    break;
                default:
                    $duration = '00:00';
            }


            //取数据
            if(empty($type)){
                $maindata = Fragment::Name($name)->Operator($operator)->Duration($duration)->Count($count)->Integral($integral)->Time($time)->forPage($page,$this->paginate)->get();
            }else{
                $maindata = FragmentType::find($type)->belongsToManyFragment()->Name($name)->Operator($operator)->Duration($duration)->Count($count)->Integral($integral)->Time($time)->forPage($page,$this->paginate)->get();
            }

            $data = [];
            $type = [];
            foreach($maindata as $k => $value)
            {

                //  发布人
                $issuer = User::find($value->user_id)->nickname;
                //  类别
                foreach($value->belongsToManyFragmentType as $kk => $v)
                {
                    $type['type'.$kk] = $v->name;
                }
                //  时长
                $sumduration = floor(($value->duration)/60).':'.($value->duration)%60;
                //  操作员
                $operator = Administrator::find($value->operator_id)->name;
                //  可进行操作
                if($value->active != 1)
                {
                    $behavior = ['isheild'=>'屏蔽'];
                }else{
                    if($value->recommend === 0)
                    {
                        if($value->ishot ==0){
                            $behavior = [
                                'ishot'=>'推荐',
                                'recommend'=>'置顶',
                                'isheild'=>'屏蔽',
                                'dotype' => '分类'
                            ];
                        }else{
                            $behavior = [
                                'ishot'=>'取消推荐',
                                'recommend'=>'置顶',
                                'isheild'=>'屏蔽',
                                'dotype' => '分类'
                            ];
                        }

                    }elseif($value->recommend === 1){
                        if($value->ishot ==0){
                            $behavior = [
                                'ishot'=>'推荐',
                                'recommend'=>'取消置顶',
                                'isheild'=>'屏蔽',
                                'dotype' => '分类'
                            ];
                        }else{
                            $behavior = [
                                'ishot'=>'取消推荐',
                                'recommend'=>'取消置顶',
                                'isheid'=>'屏蔽',
                                'dotype' => '分类'
                            ];
                        }
                    }
                }


                $tempdata = [
                    'status' => $value->active,
                    'type' => $type,
                    'issuer' => $issuer,
                    'cover' => $value->cover,
                    'description' => $value->name,
                    'duration' => $sumduration,
                    'time' => date('Y-m-d H:i:s',$value->time_add),
                    'operator' => $operator,
                    'count' => $value->count,
                    'intergral' => $value->intergral,
                    'behavior'=>$behavior,

                ];
                array_push($data,$tempdata);
            }

            $sumnum = Fragment::where('active','=',1)->get()->count();
            $todaynew = Fragment::where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();
            return response()->json(['data'=>$data,'sumnum'=>$sumnum,'todaynew'=>$todaynew],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取分类
     */
    public function gettype()
    {
        try{
            $data = FragmentType::where('active','=','1')->get();
            $type = [];
            foreach($data as $k => $v)
            {
                array_push($type,['type'=>$v->name]);
            }

            return response() -> json(['data'=>$type], 200);
        }catch (ModelNotFoundException $e){
            return response() -> json(['error'=>'not_found'],404);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取操作员
     */
    public function getOperator()
    {
        try{
            $data = Administrator::get();
            $operator = [];
            foreach($data as $k => $v)
            {
                array_push($operator,['operator_name'=>$v->name,'operator_id'=>$v->id]);
            }
            return response()->json(['data'=>$operator],200);
        }catch (ModelNotFoundException $e){}
            return response()->json(['error'=>'not_found'],404);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 获得时间条件
     */
    public function getTime()
    {
        try{
            $time = [
                [
                    'label' => 0,
                    'des' => '不限时间',
                ],
                [
                    'label' => 1,
                    'des' => '一天内',
                ],
                [
                    'label' => 2,
                    'des' => '一周内',
                ],
                [
                    'label' => 3,
                    'des' => '一月内',
                ]
            ];
            return response()->json(['data'=>$time],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取时长条件
     */
    public function getDuration()
    {
        try{
            $duration = [
                [
                    'label' => 0,
                    'des' => '00:00',
                ],
                [
                    'label' =>  1,
                    'des' => '10:00',
                ],
                [
                    'label' => 2,
                    'des' => '30:00',
                ],
                [
                    'label' => 3,
                    'des' => '60:00',
                ]
            ];
            return response()->json(['data'=>$duration],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取下载量条件
     */
    public function getCount()
    {
        try{
            $count = [
                ['count' => 0],
                ['count' => 100],
                ['count' => 500],
                ['count' => 1000],
                ['count' => 5000],
                ['count' => 10000],
                ['count' => 500000],
            ];
            return response()->json(['data'=>$count],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found',404]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 变更片段是否为推荐
     */
    public function changeRecommend(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            foreach($id as $k => $v)
            {
                $data = Fragment::find($v);
                if(empty($data))
                {
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
                if($active == 1){

                    DB::beginTransaction();

                    if($data -> ishot == 0)
                    {
                        $data -> ishot = 1;
                    }else{
                        $data -> ishot = 0;
                    }
                    $data -> time_update = time();
                    $data -> operator_id = $admin->id;
                    $data -> save();
                    DB::commit();

                }else{
                    return response()->json(['message'=>'数据不合法'],200);
                }
            }
            return response()->json(['message'=>'修改成功'],200);


        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 变更片段是否置顶
     */
    public function changeIsHot(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            $time = $request->get('$time');
            switch ($time){
                case 0:
                    $time = time()+(60*60*24);
                    break;
                case 1:
                    $time = time()+(60*60*24*7);
                    break;
                case 2:
                    $time = time()+(60*60*24*7);
                    break;
                default:
                    $time = null;
                    break;
            }
            foreach($id as $k => $v)
            {
                $data = Fragment::find($v);
                if(empty($data))
                {
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
                if($active == 1)
                {
                    DB::beginTransaction();
                    if($data -> recommend == 0)
                    {
                        $data -> recommend = 1;
                    }else{
                        $data -> recommend = 0;
                    }
                    $data -> time_update = time();
                    $data -> operator_id = $admin->id;
                    $data -> ishottime = $time;
                    $data -> save();
                    DB::commit();

                }else{
                    return response()->json(['message'=>'数据不合法'],200);
                }
            }
            return response()->json(['message'=>'修改成功'],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * 设置置顶过期时间
     */
    public function isHotTime()
    {
        try{
            $time = [
                [
                    'label'=>0,
                    'des'=>'一天',
                ],
                [
                    'label'=>1,
                    'des'=>'7天',
                ],
                [
                    'label'=>2,
                    'des'=>'30天',
                ]

            ];
            return response(['data'=>$time],200);
        }catch (ModelNotFoundException $e){
            return response(['error'=>'not_found'],404);
        }
    }


    public function changeType(Request $request)
    {
        try{
            $type = $request->get('type');
            $id = $request->get('id');
            foreach($id as $k => $v)
            {
                $data = Fragment::find($v);
                if(empty($data)){
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
                if($active == 1){
                    foreach($type as $kk => $vv)
                    {
                        DB::beginTransaction();
                        FragmentTypeFragment::where('fragment_id',$vv)->delete();
                        $channel_id = FragmentType::where('name',$vv)->first()->id;
                        $fragmentType = new FragmentTypeFragment;
                        $fragmentType ->fragment_id = $v;
                        $fragmentType ->fragmentType_id = $channel_id;
                        $fragmentType ->time_add = time();
                        $fragmentType ->time_update  = time();
                        $fragmentType ->save();
                        DB::commit();
                    }
                }else{
                    return response()->json(['message'=>'数据不合法'],200);
                }
            }
            return response()->json(['message'=>'修改成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 屏蔽片段
     */
    public function doShield(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            foreach ($id as $k => $v)
            {
                $data = Fragment::find($v);
                if(empty($data))
                {
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
                if($active == 1){
                    DB::beginTransaction();
                    $data -> active = 3;
                    $data -> update = time();
                    $data -> operator_id = $admin->id;
                    $data -> save();
                    DB::commit();

                }else{
                    return response()->json(['message'=>'数据不合法'],200);
                }
            }
            return response()->json(['message'=>'屏蔽成功']);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found']);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 推荐位片段
     */
    public function recommend(Request $request)
    {
        try{
            //  搜索条件
            //  关键字
            $name = $request->get('name');
            //  类别
            $type = $request->get('type_id','');
            //  操作员
            $operator = $request->get('operator_id','');
            //  下载量 0 为0  1 为 100  2 为 500 3 为1000   4 为5000  5 为1W  6 为5 W
            $count = $request->get('count',0);
            //  播放时长
            $duration = $request->get('duration','00:00');
            //  费用
            $integral = $request->get('integral','');
            //  发布时间
            $time = $request->get('time');
            //  页码
            $page = $request->get('page',1);
            switch ($time){
                case 0:
                    $time = 0;
                    break;
                case 1:
                    $time = strtotime(date('Y-m-d',time()));
                    break;
                case 2:
                    $time = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                    break;
                case 3:
                    $time = mktime(0,0,0,date('m'),1,date('Y'));
                    break;
                default:
                    $time = 0;
            }
            // 播放时长变形
            switch ($duration){
                case 0:
                    $duration = '00:00';
                    break;
                case 1:
                    $duration = '10:00';
                    break;
                case 2:
                    $duration = '30:00';
                    break;
                case 3:
                    $duration = '60:00';
                    break;
                default:
                    $duration = '00:00';
            }


            //取数据
            if(empty($type)){
                $maindata = Fragment::Name($name)->Operator($operator)->Duration($duration)->Count($count)->Integral($integral)->Time($time)->where('active','=','1')->where('ishot','=',1)->forPage($page,$this->paginate)->get();
            }else{
                $maindata = FragmentType::find($type)->belongsToManyFragment()->Name($name)->Operator($operator)->Duration($duration)->Count($count)->Integral($integral)->Time($time)->where('active','=','1')->where('ishot','=',1)->forPage($page,$this->paginate)->get();
            }

            $data = [];
            $type = [];
            foreach($maindata as $k => $value)
            {
                //  发布人
                $issuer = User::find($value->user_id)->nickname;
                //  类别
                foreach($value->belongsToManyFragmentType as $kk => $v)
                {
                    $type['type'.$kk] = $v->name;
                }
                //  时长
                $sumduration = floor(($value->duration)/60).':'.($value->duration)%60;
                //  操作员
                $operator = Administrator::find($value->operator_id)->name;
                //  可进行操作
                if($value->recommend === 0)
                {

                        $behavior = [
                            'ishot'=>'取消推荐',
                            'recommend'=>'置顶',
                            'isheild'=>'屏蔽'
                        ];


                }elseif($value->recommend === 1){

                        $behavior = [
                            'ishot'=>'取消推荐',
                            'recommend'=>'取消置顶',
                            'isheid'=>'屏蔽'
                        ];

                }

                $tempdata = [
                    'type' => $type,
                    'issuer' => $issuer,
                    'cover' => $value->cover,
                    'description' => $value->name,
                    'duration' => $sumduration,
                    'time' => date('Y-m-d H:i:s',$value->time_add),
                    'operator' => $operator,
                    'count' => $value->count,
                    'intergral' => $value->intergral,
                    'behavior'=>$behavior,

                ];
                array_push($data,$tempdata);
            }

            $sumnum = Fragment::where('active','=',1)->get()->count();
            $todaynew = Fragment::where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();
            return response()->json(['data'=>$data,'sumnum'=>$sumnum,'todaynew'=>$todaynew],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse]
     * 片段分类
     */
    public function type(Request $request)
    {
        try{
            $active = $request->get('active',1);
            $page = $request->get('page',1);
            if($active == 2){
                return response()->json(['message'=>'无数据'],200);
            }
            $maindata1 = FragmentType::where('active','=',$active)->orderBy('sort')->get();
            $maxsort = $maindata1->max('sort');
            $sum = $maindata1->count();
            $maindata = FragmentType::where('active','=',$active)->orderBy('sort')->forPage($page,$this->paginate)->get();
            $data = [];
            foreach ($maindata as $item => $value)
            {
                $num = $value->belongsToManyFragment->count();
                $downloadnum = $value->belongsToManyFragment->sum('count');
                $operator = Administrator::where('id','=',$value->operator_id)->first()->name;
                $creater = Administrator::where('id','=',$value->create_id)->first()->name;
                $tempdata = [
                    'id' => $value->id,
                    'name' => $value->name,
                    'time_add' => date('Y-m-d H:i:s',$value->time_add),
                    'time_update' => date('Y-m-d H:i:s',$value->time_update),
                    'num' => $num,
                    'downloadnum' => $downloadnum,
                    'operator' => $operator,
                    'creater' => $creater,

                ];
                if($value->active == 1)
                {
                    if($value->sort == 1)
                    {
                        $tempdata['behavior'] = [
                            'down' => '向下',
                            'active' => '停用',
                        ];
                    }else if($value->sort == $maxsort){
                        $tempdata['behavior'] = [
                            'up' => '向上',
                            'active' => '停用',
                        ];
                    }else{
                        $tempdata['behavior'] = [
                            'up' => '向上',
                            'down' => '向下',
                            'active' => '停用'
                        ];
                    }
                }else if($value->active == 3 ){
                    if($value->sort == 1)
                    {
                        $tempdata['behavior'] = [
                            'down' => '向下',
                            'active' => '启用',
                        ];
                    }else if($value->sort == $maxsort){
                        $tempdata['behavior'] = [
                            'up' => '向上',
                            'active' => '启用',
                        ];
                    }else{
                        $tempdata['behavior'] = [
                            'up' => '向上',
                            'down' => '向下',
                            'active' => '启用'
                        ];
                    }
                }else{
                    $tempdata['behavior'] = [
                        'status' => '未审核',
                    ];
                }
                array_push($data,$tempdata);

            }
            return response()->json(['data'=>$data,'sum'=>$sum],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not+found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 向上操作
     */
    public function up(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            DB::beginTransaction();
            $dataup = FragmentType::find($id);
            if(empty($dataup)){
                return response()->json(['message'=>'无数据'],200);
            }
            $active = $dataup->active;
            if($active != 1)
            {
                return response()->json(['message'=>'数据不合法'],404);
            }
            $maxsort = FragmentType::orderBy('sort')->max('sort');
            $datadown = FragmentType::where('sort','=',($dataup->sort)-1)->first();
            if($dataup->sort > 1 && $dataup->sort <= $maxsort)
            {
                $dataup -> sort = ($dataup->sort)-1;
                $dataup -> time_update = time();
                $dataup -> operator_id = $admin->id;
                $dataup -> save();

                $datadown -> sort = ($datadown->sort)+1;
                $datadown -> time_update = time();
                $datadown -> operator_id = $admin->id;
                $datadown ->save();
                DB::commit();
                return response()->json(['message'=>'修改成功'],200);
            }else{
                return response()->json(['message'=>'数据不合法'],200);
            }

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 向下操作
     */
    public function down(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            DB::beginTransaction();
            $datadown = FragmentType::find($id);
            if(empty($datadown))
            {
                return response()->json(['message'=>'无数据'],200);
            }
            $active = $datadown->active;
            if($active != 1){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $maxsort = FragmentType::orderBy('sort')->max('sort');
            if($datadown->sort >= 1 && $datadown->sort < $maxsort)
            {
                $dataup = FragmentType::where('sort','=',($datadown->sort)+1)->first();
                $dataup -> sort = ($dataup->sort)-1;
                $dataup -> time_update = time();
                $dataup -> operator_id = $admin->id;
                $dataup -> save();

                $datadown -> sort = ($datadown->sort)+1;
                $datadown -> time_update = time();
                $datadown -> operator_id = $admin->id;
                $datadown ->save();
                DB::commit();
                return response()->json(['message'=>'修改成功'],200);
            }else{
                return response()->json(['message'=>'数据不合法'],200);
            }
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 变更分类是否停用
     */
    public function changeStop(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            foreach($id as $k => $v)
            {
                DB::beginTransaction();
                $data = FragmentType::find($v);
                if(empty($data))
                {
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
//            dd($active);
                DB::beginTransaction();
                if($active !== 1 && $active !== 3  ){
                    return response()->json(['message'=>'数据不合法'],200);
                }else if($active === 1){
                    $data -> active = 3;
                }else if($active === 3){
                    $data -> active = 1;
                }
                $data -> operator_id = $admin->id;

                $data -> time_update = time();

                $data -> save();
                DB::commit();
            }

            return response()->json(['message'=>'修改成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],200);
        }
    }



    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 屏蔽仓
     */
    public function shieldWareHouse(Request $request)
    {
        try{
            //  搜索条件
            //  关键字
            $name = $request->get('name');
            //  类别
            $type = $request->get('type_id','');
            //  操作员
            $operator = $request->get('operator_id','');
            //  下载量 0 为0  1 为 100  2 为 500 3 为1000   4 为5000  5 为1W  6 为5 W
            $count = $request->get('count',0);
            //  播放时长
            $duration = $request->get('duration','00:00');
            //  费用
            $integral = $request->get('integral','');
            //  发布时间
            $time = $request->get('time');
            //  页码
            $page = $request->get('page',1);
            switch ($time){
                case 0:
                    $time = 0;
                    break;
                case 1:
                    $time = strtotime(date('Y-m-d',time()));
                    break;
                case 2:
                    $time = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                    break;
                case 3:
                    $time = mktime(0,0,0,date('m'),1,date('Y'));
                    break;
                default:
                    $time = 0;
            }
            // 播放时长变形
            switch ($duration){
                case 0:
                    $duration = '00:00';
                    break;
                case 1:
                    $duration = '10:00';
                    break;
                case 2:
                    $duration = '30:00';
                    break;
                case 3:
                    $duration = '60:00';
                    break;
                default:
                    $duration = '00:00';
            }


            //取数据
            if(empty($type)){
                $maindata = Fragment::Name($name)->Operator($operator)->Duration($duration)->Count($count)->Integral($integral)->Time($time)->where('active','=','3')->forPage($page,$this->paginate)->get();
            }else{
                $maindata = FragmentType::find($type)->belongsToManyFragment()->Name($name)->Operator($operator)->Duration($duration)->Count($count)->Integral($integral)->Time($time)->where('active','=','3')->forPage($page,$this->paginate)->get();
            }

            $data = [];
            $type = [];
            foreach($maindata as $k => $value)
            {
                //  发布人
                $issuer = User::find($value->user_id)->nickname;
                //  类别
                foreach($value->belongsToManyFragmentType as $kk => $v)
                {
                    $type['type'.$kk] = $v->name;
                }
                //  时长
                $sumduration = floor(($value->duration)/60).':'.($value->duration)%60;
                //  操作员
                $operator = Administrator::find($value->operator_id)->name;
                //  可进行操作
                $behavior = ['behavior1'=>'取消屏蔽','behavior2'=>'删除',];

                $tempdata = [
                    'type' => $type,
                    'issuer' => $issuer,
                    'cover' => $value->cover,
                    'description' => $value->name,
                    'duration' => $sumduration,
                    'time_add' => date('Y-m-d H:i:s',$value->time_add),
                    'time_update' => date('Y-m-d H:i:s',$value->time_update),
                    'operator' => $operator,
                    'count' => $value->count,
                    'intergral' => $value->intergral,
                    'behavior'=>$behavior,

                ];
                array_push($data,$tempdata);
            }

            $sumnum = Fragment::where('active','=',3)->get()->count();
            $todaynew = Fragment::where('active','=',3)->where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();
            return response()->json(['data'=>$data,'sumnum'=>$sumnum,'todaynew'=>$todaynew],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 取消屏蔽
     */
    public function cancelShield(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            foreach($id as $k => $v)
            {
                $data = Fragment::find($v);
                if(empty($data))
                {
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
//            dd($active);
                DB::beginTransaction();
                if($active !== 3){
                    return response()->json(['message'=>'数据不合法'],200);
                }
                $data -> active = 1;
                $data -> operator_id = $admin->id;

                $data -> time_update = time();
                $data -> save();
                DB::commit();
            }

            return response()->json(['message'=>'取消成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error' => 'not_found'], 404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 删除片段
     */
    public function delete(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            foreach ($id as $k => $v)
            {
                $data = Fragment::find($v);
                if(empty($data))
                {
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
//            dd($active);
                DB::beginTransaction();
                if($active !== 3){
                    return response()->json(['message'=>'数据不合法'],200);
                }
                $data -> active = 2;
                $data -> operator_id = $admin->id;
                $data -> time_add = time();
                $data -> save();
            }

            DB::commit();
            return response()->json(['message'=>'删除成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error' => 'not_found'], 404);
        }
    }

}

