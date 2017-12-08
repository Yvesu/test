<?php

namespace App\Http\Controllers\NewAdmin;

use CloudStorage;
use App\Models\Admin\Administrator;
use App\Models\DownloadCost;
use App\Models\Keywords;
use App\Models\Make\FilterFolder;
use App\Models\Make\FilterKeyword;
use App\Models\Make\MakeFilterFile;
use App\Models\Make\MakeFilterFolder;
use App\Models\Make\TextureMixType;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    private $protocol = 'http://';

    private $paginate = 20;
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 显示滤镜页面
     */
    public function index(Request $request)
    {
        try{
            //  搜索条件
            //  关键字
            $name = $request->get('name');
            //  类别
            $folder_id = $request->get('folder_id','');
//        dd($folder_id);
            //  操作员
            $operator_id = $request->get('operator_id','');
            //  费用
            $integral = $request->get('integral','');
            //  下载量 0 为0  1 为 100  2 为 500 3 为1000   4 为5000  5 为1W  6 为5 W
            $count = $request->get('count',0);
            //  页码
            $page = $request->get('page',1);

            //  时间  0 全部  1一天内    2 一周内    3 一月内
            $time = $request->get('time',0);
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
            //  取出滤镜表中的数据
            if(empty($folder_id))
            {
                $maindata = MakeFilterFile::Name($name)->FolderId($folder_id)->OperatorId($operator_id)->Integral($integral)->Counta($count)->Time($time)->where('active','=','1')->forPage($page,$this->paginate)->get();
            }else{
                $maindata = MakeFilterFolder::find($folder_id)->belongsToManyFilter()->Name($name)->OperatorId($operator_id)->Integral($integral)->Counta($count)->Time($time)->where('active','=','1')->forPage($page,$this->paginate)->get();
            }

            $data = [];
            //  取出操作员 并和其余数据存入到数组中
            foreach($maindata as $item => $value)
            {

                $admin = Administrator::where('id',$value->operator_id)->first();
//                $type = $value->belongsToFolder()->first();
                foreach ($value->belongsToManyFolder as $k=>$folder)
                {
                    $type['type'.$k] = $folder->name;
                }
                $tempdata = [
                    'id' => $value->id,
                    'type' => $type,
                    'cover' => $this->protocol.$value->cover,
                    'name' => $value->name,
                    'content' => $this->protocol.$value->texture,
                    'time_add' =>date('Y-m-d H:i:s',$value->time_add),
                    'operator' => $admin->name,
                    'count' => $value->count,
                    'intrgral' => $value->integral,
                    'active' => $value->active,
                    'recommend' => $value->recommend,
                    'ishot' => $value->ishot,

                ];
//                dd($value);
                if($tempdata['recommend'] == 0)
                {
                    if($tempdata['ishot'] == 0){
                        $tempdata['behavior'] = [
                            'recomment' => '推荐',
                            'ishot' => '热门',
                            'isshield' => '屏蔽',
                            'dotype' => '分类'
                        ];
                    }else{
                        $tempdata['behavior'] = [
                            'recomment' => '推荐',
                            'ishot' => '取消热门',
                            'isshield' => '屏蔽',
                            'dotype' => '分类'
                        ];
                    }

                }else{
                    if($tempdata['ishot'] == 0){
                        $tempdata['behavior'] = [
                            'recomment' => '取消推荐',
                            'ishot' => '热门',
                            'isshield' => '屏蔽',
                            'dotype' => '分类'
                        ];
                    }else{
                        $tempdata['behavior'] = [
                            'recomment' => '取消推荐',
                            'ishot' => '取消热门',
                            'isshield' => '屏蔽',
                            'dotype' => '分类'
                        ];
                    }
                }
                array_push($data,$tempdata);
            }
            $sum = MakeFilterFile::get()->count();

            $todaynew = MakeFilterFile::where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();
            return response()->json(['data'=>$data,'sum' => $sum,'todaynew' => $todaynew,],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 变更推荐位滤镜
     */

    public function changerecommend(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            foreach($id as $k => $v)
            {
                $data = MakeFilterFile::find($v);
                if(empty($data))
                {
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
                if($active == 1){

                    DB::beginTransaction();

                    if($data -> recommend == 0)
                    {
                        $data -> recommend = 1;
                    }else{
                        $data -> recommend = 0;
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
     * 变更是否为热门滤镜
     */
    public function changishot(Request $request)
    {
        try{
            //  管理员信息
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
            foreach ($id as $k => $v)
            {
                $data = MakeFilterFile::find($id);
                if(empty($data))
                {
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
                if($active == 1){
                    DB::beginTransaction();
                    if($data->ishot == 1)
                    {
                        $data ->ishot = 0;
                    }else{
                        $data -> ishot =1;
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 变更分类
     */
    public function changeType(Request $request)
    {
        try{
            $type = $request->get('type');
            $id = $request->get('id');
            foreach($id as $k => $v)
            {
                $data = MakeFilterFile::find($v);
                if(empty($data)){
                    return response()->json(['message'=>'无数据'],200);
                }
                $active = $data->active;
                if($active == 1){
                    foreach($type as $kk => $vv)
                    {
                        DB::beginTransaction();
                        FilterFolder::where('filter_id',$v)->delete();
                        $fragmentType = new FilterFolder;
                        $fragmentType ->filter_id = $v;
                        $fragmentType ->folder_id = $vv;
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
     * 屏蔽滤镜
     */
    public function doshield(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            foreach ($id as $k => $v)
            {
                $data = MakeFilterFile::find($v);
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
     * 推荐为滤镜
     */
    public function recommend(Request $request)
    {
        try{
            //  搜索条件
            //  关键字
            $name = $request->get('name');
            //  类别
            $folder_id = $request->get('folder_id','');
//        dd($folder_id);
            //  操作员
            $operator_id = $request->get('operator_id','');
            //  费用
            $integral = $request->get('integral','');
            //  下载量 0 为0  1 为 100  2 为 500 3 为1000   4 为5000  5 为1W  6 为5 W
            $count = $request->get('count',0);

            //  页码
            $page = $request->get('page',1);

            //  时间  0 全部  1一天内    2 一周内    3 一月内
            $time = $request->get('time',0);
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
            //  取出滤镜表中的数据
            if(empty($folder_id))
            {
                $maindata = MakeFilterFile::Name($name)->FolderId($folder_id)->OperatorId($operator_id)->Integral($integral)->Counta($count)->Time($time)->where('active','=','1')->where('recommend','=','1')->forPage($page,$this->paginate)->get();
            }else{
                $maindata = MakeFilterFolder::find($folder_id)->belongsToManyFilter()->Name($name)->OperatorId($operator_id)->Integral($integral)->Counta($count)->Time($time)->where('active','=','1')->where('recommend','=','1')->forPage($page,$this->paginate)->get();
            }

            $data = [];
            //  取出操作员 并和其余数据存入到数组中
            foreach($maindata as $item => $value)
            {

                $admin = Administrator::where('id',$value->operator_id)->first();
                foreach ($value->belongsToManyFolder as $k=>$folder)
                {
                    $type['type'.$k] = $folder->name;
                }
                $tempdata = [
                    'id' => $value->id,
                    'type' => $type,
                    'cover' => $this->protocol.$value->cover,
                    'name' => $value->name,
                    'content' => $this->protocol.$value->texture,
                    'time_add' =>date('Y-m-d H:i:s',$value->time_add),
                    'operator' => $admin->name,
                    'count' => $value->count,
                    'intrgral' => $value->integral,
                    'active' => $value->active,
                    'ishot' => $value->ishot,

                ];

                    if($tempdata['ishot'] == 0){
                        $tempdata['behavior'] = [
                            'recomment' => '取消推荐',
                            'ishot' => '热门',
                            'isshield' => '屏蔽',
                        ];
                    }else{
                        $tempdata['behavior'] = [
                            'recomment' => '取消推荐',
                            'ishot' => '取消热门',
                            'isshield' => '屏蔽',
                        ];
                    }

                array_push($data,$tempdata);

            }

            $sum = MakeFilterFile::get()->count();

            $todaynew = MakeFilterFile::where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();

            return response()->json(['data'=>$data,'type'=> $type1,'operator' => $operator1,'time' => $time,'integral' => $num1  ,'count' => $count,'sum' => $sum,'todaynew' => $todaynew,],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 滤镜分类
     */

    public function type(Request $request)
    {
        try{
            $active = $request->get('active',1);
            if($active == 2){
                return response()->json(['message'=>'无数据'],200);
            }
            $maindata1 = MakeFilterFolder::where('active','=',$active)->orderBy('sort')->get();
            $maxsort = $maindata1->max('sort');
            $sum = $maindata1->count();
            $page = $request->get('page',1);
            $maindata = MakeFilterFolder::where('active','=',$active)->orderBy('sort')->forPage($page,$this->paginate)->get();
//            dd($maxsort);
            $data = [];
            foreach($maindata as $item => $value)
            {

//
                $num = MakeFilterFolder::find($value->id)->belongsToManyFilter->count();
                $downloadnum = MakeFilterFolder::find($value->id)->belongsToManyFilter->sum('count');
                $operator = Administrator::where('id','=',$value->operator_id)->first()->name;
                $creater = Administrator::where('id','=',$value->create_id)->first()->name;
                $tempdata = [
                    'id' => $value->id,
                    'name' => $value->name,
                    'time' => date('Y-m-d H:i:s',$value->time_add),
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
                            'active' => '取消停用',
                        ];
                    }else if($value->sort == $maxsort){
                        $tempdata['behavior'] = [
                            'up' => '向上',
                            'active' => '取消停用',
                        ];
                    }else{
                        $tempdata['behavior'] = [
                            'up' => '向上',
                            'down' => '向下',
                            'active' => '取消停用'
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
            return response()->json(['error'=>'not_found'],404);
        }


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 分类向上变更
     */
    public function up(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            DB::beginTransaction();
            $dataup = MakeFilterFolder::find($id);
            if(empty($dataup))
            {
                return response()->json(['message'=>'无数据'],200);
            }
            $active = $dataup->active;
            if($active != 1){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $maxsort = MakeFilterFolder::orderBy('sort')->max('sort');
            $datadown = MakeFilterFolder::where('sort','=',($dataup->sort)-1)->first();
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
     * 分类排序向下变更
     */

    public function down(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
//            dd($maxsort);
            DB::beginTransaction();
            $datadown = MakeFilterFolder::find($id);
            if(empty($datadown))
            {
                return response()->json(['message'=>'无数据'],200);
            }
            $active = $datadown->active;
            if($active != 1){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $maxsort = MakeFilterFolder::orderBy('sort')->max('sort');
            if($datadown->sort >= 1 && $datadown->sort < $maxsort)
            {
                $dataup = MakeFilterFolder::where('sort','=',($datadown->sort)+1)->first();
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
     * 变更是否停用
     */
    public function changestop(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            $data = MakeFilterFolder::find($id);
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
            return response()->json(['message'=>'修改成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],200);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 创建分类
     */
    public function makenewtype(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $create_id = $admin->id;
            $time_add = time();
            $time_update = time();
            $operator_id = $admin->id;
            $name = $request->get('name','');
            if(empty($name)){
                return response()->json(['error'=>'分类名称不能为空'],200);
            }
            $active = $request->get('active',0);
            DB::beginTransaction();
            $data = new MakeFilterFolder;
            if($active == 1)
            {
                $sort =1 + MakeFilterFolder::max('sort');
                $data ->sort = $sort;
            }
            $data -> name = $name;
            $data -> active = $active;
            $data -> time_add = $time_add;
            $data -> time_update = $time_update;
            $data -> create_id  = $create_id;
            $data -> operator_id = $operator_id;
            $data -> save();
            DB::commit();
            return response()->json(['message'=>'添加成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 屏蔽仓
     */
    public function shieldwarehouse(Request $request)
    {
        try{
            //  搜索条件
            //  关键字
            $name = $request->get('name');
            //  类别
            $folder_id = $request->get('folder_id','');
    //        dd($folder_id);
            //  操作员
            $operator_id = $request->get('operator_id','');
            //  费用
            $integral = $request->get('integral','');
            //  下载量 0 为0  1 为 100  2 为 500 3 为1000   4 为5000  5 为1W  6 为5 W
            $count = $request->get('count',0);
            //  页码
            $page = $request->get('page',1);

            //  时间  0 全部  1一天内    2 一周内    3 一月内
            $time = $request->get('time',0);
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
            //  取出滤镜表中的数据
            if(empty($folder_id))
            {
                $maindata = MakeFilterFile::Name($name)->FolderId($folder_id)->OperatorId($operator_id)->Integral($integral)->Counta($count)->Time($time)->where('active','=','3')->forPage($page,$this->paginate)->get();
            }else{
                $maindata = MakeFilterFolder::find($folder_id)->belongsToManyFilter()->Name($name)->OperatorId($operator_id)->Integral($integral)->Counta($count)->Time($time)->where('active','=','3')->forPage($page,$this->paginate)->get();
            }
            $data = [];
            //  取出操作员 并和其余数据存入到数组中
            foreach($maindata as $item => $value)
            {

                $admin = Administrator::where('id',$value->operator_id)->first();
    //                $type = $value->belongsToFolder()->first();
                foreach ($value->belongsToManyFolder as $k=>$folder)
                {
                    $type['type'.$k] = $folder->name;
                }
                $tempdata = [
                    'id' => $value->id,
                    'type' => $type,
                    'cover' => $this->protocol.$value->cover,
                    'name' => $value->name,
                    'content' => $this->protocol.$value->texture,
                    'time_add' =>date('Y-m-d H:i:s',$value->time_add),
                    'operator' => $admin->name,
                    'count' => $value->count,
                    'intrgral' => $value->integral,
                    'active' => $value->active,
                    'behavior' => ['behavior1'=>'取消屏蔽','behavior2'=>'删除'],

                ];

//                $data['data'.$item] = $tempdata;
                array_push($data,$tempdata);

            }
            $sum = MakeFilterFile::get()->count();

            $todaynew = MakeFilterFile::where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();

            return response()->json(['data'=>$data,'type'=>$folder,'operator'=>$operator1,'time'=>$time,'integral'=>$num1,'count'=>$count,'sum'=>$sum,'todaynew'=>$todaynew],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 取消屏蔽
     */
    public function cancelshield(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            $data = MakeFilterFile::find($id);
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
            return response()->json(['message'=>'取消成功'],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 删除滤镜
     */
    public function delete(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            $data = MakeFilterFile::find($id);
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
            DB::commit();
            return response()->json(['message'=>'删除成功'],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error' => 'not_found'], 404);
        }
    }



    public function hotsearch(Request $request)
    {
        try{

        }catch (ModelNotFoundException $e){}
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * 显示发布页面
     */
    public function addfilter(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $admin_info = Administrator::with('hasOneUser')->where('id',$admin->id)->firstOrFail(['user_id']);
            $user_id = $admin_info->user_id;
            $olddata = MakeFilterFile::where('name','=','')->where('user_id','=',$user_id)->first();
//            dd($olddata);
            if($olddata){
                return response()->json(['user_id'=>$user_id,'id'=>$olddata->id],200);
            }else{
                $filter = new MakeFilterFile;
                $filter->user_id = $user_id;
                $filter->time_add = time();
                $filter->time_update = time();
                $filter->save();
                return response()->json(['user_id'=>$user_id,'id'=>$filter->id],200);
            }

        }catch (ModelNotFoundException $e){
            return response(['error'=>'not_found'],404);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 获得资费列表
     */
    public function getIntegral()
    {
        try{
            $cost = DownloadCost::get();
            $integral = [];
            foreach ($cost as $item => $value)
            {
                $costType=['integral' => $value->details];
                array_push($integral,$costType);
            }
            return response()->json(['data'=>$integral],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * 获得滤镜纹理混合分类
     */
    public function getTextureMixType()
    {
        try{
            $textureMixType2 = [];
            $textureMixType = TextureMixType::get();
            foreach($textureMixType as $item => $value)
            {
                $textureMixtype1=[
                    'id' => $value->id,
                    'name' => $value->name
                ];
                array_push($textureMixType2,$textureMixtype1);
            }
            return response()->json(['data'=>$textureMixType2],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }




    /**
     * @return \Illuminate\Http\JsonResponse
     * 滤镜添加分类
     */
    public function addFilterType()
    {
        try{
            $type = MakeFilterFolder::where('active','=',1)->get();
            $data = [];
            foreach ($type as $item => $value)
            {
                $temporydata['type'.$item] = [
                    'id' => $value->id,
                    'name' => $value->name,
                ];

                array_push($data,$temporydata['type'.$item]);
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>not_found],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 执行发布滤镜
     */
    public function doAddFilter(Request $request)
    {
        try{
            $keys1 = [];
            $keys2 = [];
            $admin = Auth::guard('api')->user();
            $id = $admin->id;
//            dd($admin);
            $user_id = Administrator::find($id)->hasOneUser->id;
//            dd($user_id);
            $filter1_id = $request->get('id');
            $type = explode('|',$request->get('type',null));
            $type = array_unique($type);
            $type = array_slice($type,0,2,true);
            $name = $request->get('name',null);
            $integral = $request->get('integral',null);
            $cover = $request->get('cover',null);
            $content = $request->get('content',null);
            $texture = $request->get('texture',null);
            $textureMixType = $request->get('textMixType',null);

            $vipfree = $request->get('vipfree',null);
            if(is_null($vipfree) || is_null($request->get('type',null)) || is_null($name) || is_null($integral) || is_null($cover) || is_null($content) )
            {
                return response()->json(['error'=>'不能有选项为空']);
            }

            DB::beginTransaction();
            $filter = MakeFilterFile::find($filter1_id);
            $filter->user_id = $user_id;
            $filter->name = $name;
            $filter->integral = $integral;
            $filter->cover = 'img.cdn.hivideo.com/'.$cover;
            $filter->content = 'file.cdn.hivideo.com/'.$content;
            if($texture != 'undefined'){
                $filter->texture = 'img.cdn.hivideo.com/'.$texture;
                array_push($keys1,$texture);
            }
            if($textureMixType != 'undefined'){
                $filter->texture_mix_type_id = $textureMixType;
            }
            $filter->texture_mix_type_id = $textureMixType;
            $filter->time_add = time();
            $filter->time_update = time();
            $filter->operator_id = $id;
            $filter->vipfree = $vipfree;
            $filter->save();
            array_push($keys1,$cover);
            array_push($keys2,$content);
            $keyPairs1 = array();
            $keyPairs2 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            foreach ($keys2 as $key)
            {
                $keyPairs2[$key] = $key;
            }
            $srcbucket2 = 'hivideo-file-ects';
            $srcbucket1 = 'hivideo-img-ects';
            $destbucket1 = 'hivideo-img';
            $destbucket2 = 'hivideo-file';

            $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket2,$destbucket2);
            foreach($type as $k => $v){
                $filter_id = $filter->id;
                $folder_id = MakeFilterFolder::where('id','=',$v)->first()->id;
                if($folder_id)
                {
                    $filter_floder =new FilterFolder;
                    $filter_floder->filter_id = $filter_id;
                    $filter_floder->folder_id = $folder_id;
                    $filter_floder->time_add = time();
                    $filter_floder->time_update = time();
                    $filter_floder->save();
                }
            }

            if(!is_null($request->keyword)){
                $keyword = explode('|',$request->get('keyword',null));
                $keyword = array_unique($keyword);
                foreach($keyword as $k => $v)
                {

                    $filter_id = $filter->id;
                    $keyword = Keywords::where('keyword', $v)->first();
                    if ($keyword) {
                        $keyword_id = $keyword->id;

                    } else {
                        $newkeyword = new Keywords;
                        $newkeyword ->keyword = $v;
                        $newkeyword ->create_at = time();
                        $newkeyword ->update_at = time();
                        $newkeyword ->save();
                        $keyword_id = $newkeyword->id;

                    }

                    $filterKeyword = new FilterKeyword;
                    $filterKeyword->keyword_id = $keyword_id;
                    $filterKeyword->filter_id = $filter_id;
                    $filterKeyword->time_add = time();
                    $filterKeyword->time_update = time();
                    $filterKeyword->save();

                }
            }

            $finishtime = date('Y-m-d H:i:s',time());

            DB::commit();
            return response()->json(['message'=>'发布成功','message1'=>$message1,'message2'=>$message2,'finishtime' => $finishtime],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

}
