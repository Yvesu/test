<?php

namespace App\Http\Controllers\NewAdmin;

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
//        switch ($count){
//            case 0:
//                $count = 0;
//                break;
//            case 1:
//                $count = 100;
//                break;
//            case 2:
//                $count = 500;
//                break;
//            case 3:
//                $count = 1000;
//                break;
//            case 4:
//                $count = 5000;
//                break;
//            case 5:
//                $count = 10000;
//                break;
//            case 6:
//                $count = 50000;
//                break;
//            default:
//                $count = 0;
//                break;
//        }
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
                $maindata = MakeFilterFile::Name($name)->FolderId($folder_id)->OperatorId($operator_id)->Integral($integral)->Count($count)->Time($time)->where('active','=','1')->forPage($page,$this->paginate)->get();
            }else{
                $maindata = MakeFilterFolder::find($folder_id)->belongsToManyFilter()->Name($name)->OperatorId($operator_id)->Integral($integral)->Count($count)->Time($time)->where('active','=','1')->forPage($page,$this->paginate)->get();
            }
            //  取出发布人（管理员、用户）
//        foreach($maindata as $k => $v)
//        {
//           $user[$k] = $v->belongsToUser()->first();
//
//            $admin[$k] = Administrator::where('user_id',$user[$k]->id)->first();
//        }
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
                    'cover' => $value->cover,
                    'name' => $value->name,
                    'content' => $value->content,
                    'time_add' =>date('Y-m-d H:i:s',$value->time_add),
                    'operator' => $admin->name,
                    'count' => $value->count,
                    'intrgral' => $value->integral,
                    'active' => $value->active,

                ];
                if($value->recomment == 0)
                {
                    if($value->ishot == 0){
                        $tempdata['behavior'] = [
                            'recomment' => '推荐',
                            'ishot' => '热门',
                            'isshield' => '屏蔽',
                        ];
                    }else{
                        $tempdata['behavior'] = [
                            'recomment' => '推荐',
                            'ishot' => '取消热门',
                            'isshield' => '屏蔽',
                        ];
                    }

                }else{
                    if($value->ishot == 0){
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
                }
                $data['data'.$item] = $tempdata;

            }


            //  分类数据
            $type = MakeFilterFolder::where('active','=','1')->get();
            foreach ($type as $k => $v)
            {
                $folder1['folder'.$k]['name'] = $v->name;
                $folder1['folder'.$k]['id'] = $v->id;
            }

            //  操作员
            $administrator = Administrator::get();
            foreach ($administrator as $k => $v)
            {
                $operator['operator'.$k]['id'] = $v -> id;
                $operator['operator'.$k]['name'] = $v -> name;
            }

            //  时间
            $time = [
                'time1' => [
                    'label' => 0,
                    'des' => '不限时间',
                ],
                'time2' => [
                    'label' => 1,
                    'des' => '一天内',
                ],
                'time3' => [
                    'label' => 2,
                    'des' => '一周内',
                ],
                'time4' => [
                    'label' => 3,
                    'des' => '一月内',
                ]
            ];

            //  费用
            $cost = DownloadCost::get();
//        dd($cost);
            foreach ($cost as $k => $v)
            {
                $num['integral'.$k] = $cost[$k]->details;
            }

            //  下载量
            $count = [
                'count1' => 0,
                'count2' => 100,
                'count3' => 500,
                'count4' => 1000,
                'count5' => 5000,
                'count6' => 10000,
                'count7' => 500000,
            ];

            $sum = MakeFilterFile::get()->count();

            $todaynew = MakeFilterFile::where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();

            $arr = [
                'data' => $data,
                'type'=> $folder1,
                'operator' => $operator,
                'time' => $time,
                'integral' => $num,
                'count' => $count,
                'sum' => $sum,
                'todaynew' => $todaynew,
            ];
            $arr1 = [];
            array_push($arr1,$arr);
            return response()->json(['data'=>$arr1],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
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
            $data = MakeFilterFile::find($id);
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
     * 变更是否为热门滤镜
     */
    public function changishot(Request $request)
    {
        try{
            //  管理员信息
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
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
                $data -> save();
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
     * 屏蔽滤镜
     */
    public function doshield(Request $request)
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
            if($active == 1){
                DB::beginTransaction();
                $data -> active = 3;
                $data -> update = time();
                $data -> operator_id = $admin->id;
                $data -> save();
                DB::commit();
                return response()->json(['message'=>'屏蔽成功']);
            }else{
                return response()->json(['message'=>'数据不合法'],200);
            }

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
                $maindata = MakeFilterFile::Name($name)->FolderId($folder_id)->OperatorId($operator_id)->Integral($integral)->Count($count)->Time($time)->where('active','=','1')->forPage($page,$this->paginate)->get();
            }else{
                $maindata = MakeFilterFolder::find($folder_id)->belongsToManyFilter()->Name($name)->OperatorId($operator_id)->Integral($integral)->Count($count)->Time($time)->where('active','=','1')->forPage($page,$this->paginate)->get();
            }
            //  取出发布人（管理员、用户）
//        foreach($maindata as $k => $v)
//        {
//           $user[$k] = $v->belongsToUser()->first();
//
//            $admin[$k] = Administrator::where('user_id',$user[$k]->id)->first();
//        }
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
                    'cover' => $value->cover,
                    'name' => $value->name,
                    'content' => $value->content,
                    'time_add' =>date('Y-m-d H:i:s',$value->time_add),
                    'operator' => $admin->name,
                    'count' => $value->count,
                    'intrgral' => $value->integral,
                    'active' => $value->active,

                ];

                    if($value->ishot == 0){
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

                $data['data'.$item] = $tempdata;

            }


            //  分类数据
            $type = MakeFilterFolder::where('active','=','1')->get();
            foreach ($type as $k => $v)
            {
                $folder1['folder'.$k]['name'] = $v->name;
                $folder1['folder'.$k]['id'] = $v->id;
            }

            //  操作员
            $administrator = Administrator::get();
            foreach ($administrator as $k => $v)
            {
                $operator['operator'.$k]['id'] = $v -> id;
                $operator['operator'.$k]['name'] = $v -> name;
            }

            //  时间
            $time = [
                'time1' => [
                    'label' => 0,
                    'des' => '不限时间',
                ],
                'time2' => [
                    'label' => 1,
                    'des' => '一天内',
                ],
                'time3' => [
                    'label' => 2,
                    'des' => '一周内',
                ],
                'time4' => [
                    'label' => 3,
                    'des' => '一月内',
                ]
            ];

            //  费用
            $cost = DownloadCost::get();
//        dd($cost);
            foreach ($cost as $k => $v)
            {
                $num['integral'.$k] = $cost[$k]->details;
            }

            //  下载量
            $count = [
                'count1' => 0,
                'count2' => 100,
                'count3' => 500,
                'count4' => 1000,
                'count5' => 5000,
                'count6' => 10000,
                'count7' => 500000,
            ];

            $sum = MakeFilterFile::get()->count();

            $todaynew = MakeFilterFile::where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();

            $arr = [
                'data' => $data,
                'type'=> $folder1,
                'operator' => $operator,
                'time' => $time,
                'integral' => $num,
                'count' => $count,
                'sum' => $sum,
                'todaynew' => $todaynew,
            ];
            $arr1 = [];
            array_push($arr1,$arr);
            return response()->json(['data'=>$arr1],200);
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
            foreach($maindata as $item => $value)
            {

                $num = MakeFilterFile::where('folder_id','=',$value->id)->get()->count();
                $downloadnum = MakeFilterFile::where('folder_id','=',$value->id)->sum('count');
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

                $data['data'.$item] = $tempdata;
            }
            $arr1 = [
                'data' => $data,
                'sum' => $sum,
            ];
            $arr = [];
            array_push($arr,$arr1);
            return response()->json(['data'=>$arr],200);
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
            $active = $datadown->active;
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
                $maindata = MakeFilterFile::Name($name)->FolderId($folder_id)->OperatorId($operator_id)->Integral($integral)->Count($count)->Time($time)->where('active','=','3')->forPage($page,$this->paginate)->get();
            }else{
                $maindata = MakeFilterFolder::find($folder_id)->belongsToManyFilter()->Name($name)->OperatorId($operator_id)->Integral($integral)->Count($count)->Time($time)->where('active','=','3')->forPage($page,$this->paginate)->get();
            }
            //  取出发布人（管理员、用户）
    //        foreach($maindata as $k => $v)
    //        {
    //           $user[$k] = $v->belongsToUser()->first();
    //
    //            $admin[$k] = Administrator::where('user_id',$user[$k]->id)->first();
    //        }
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
                    'cover' => $value->cover,
                    'name' => $value->name,
                    'content' => $value->content,
                    'time_add' =>date('Y-m-d H:i:s',$value->time_add),
                    'operator' => $admin->name,
                    'count' => $value->count,
                    'intrgral' => $value->integral,
                    'active' => $value->active,
                    'behavior' => ['behavior1'=>'取消屏蔽','behavior2'=>'删除'],

                ];

                $data['data'.$item] = $tempdata;

            }


            //  分类数据
            $type = MakeFilterFolder::where('active','=','1')->get();
            foreach ($type as $k => $v)
            {
                $folder1['folder'.$k]['name'] = $v->name;
                $folder1['folder'.$k]['id'] = $v->id;
            }

            //  操作员
            $administrator = Administrator::get();
            foreach ($administrator as $k => $v)
            {
                $operator['operator'.$k]['id'] = $v -> id;
                $operator['operator'.$k]['name'] = $v -> name;
            }

            //  时间
            $time = [
                'time1' => [
                    'label' => 0,
                    'des' => '不限时间',
                ],
                'time2' => [
                    'label' => 1,
                    'des' => '一天内',
                ],
                'time3' => [
                    'label' => 2,
                    'des' => '一周内',
                ],
                'time4' => [
                    'label' => 3,
                    'des' => '一月内',
                ]
            ];

            //  费用
            $cost = DownloadCost::get();
    //        dd($cost);
            foreach ($cost as $k => $v)
            {
                $num['integral'.$k] = $cost[$k]->details;
            }

            //  下载量
            $count = [
                'count1' => 0,
                'count2' => 100,
                'count3' => 500,
                'count4' => 1000,
                'count5' => 5000,
                'count6' => 10000,
                'count7' => 500000,
            ];

            $sum = MakeFilterFile::get()->count();

            $todaynew = MakeFilterFile::where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();

            $arr = [
                'data' => $data,
                'type'=> $folder1,
                'operator' => $operator,
                'time' => $time,
                'integral' => $num,
                'count' => $count,
                'sum' => $sum,
                'todaynew' => $todaynew,
            ];
            $arr1 = [];
            array_push($arr1,$arr);
            return response()->json(['data'=>$arr1],200);
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

            $cost = DownloadCost::get();
            foreach ($cost as $item => $value)
            {
                $costType['type'.$item] = $value->details;
            }
            $textureMixType = TextureMixType::get();
            foreach($textureMixType as $item => $value)
            {
                $texturetype['type'.$item]['id'] = $value->id;
                $texturetype['type'.$item]['name'] = $value->name;
            }
            $data = [];
            array_push($data,['costType'=>$costType,'texturetype'=>$texturetype]);
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $e){
            return response(['error'=>'not_found'],404);
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
            $admin = Auth::guard('api')->user();
            $id = $admin->id;
//            dd($admin);
            $user_id = Administrator::find($id)->hasOneUser->id;
//            dd($user_id);
            $type = $request->get('type');
            $name = $request->get('name');
            $integral = $request->get('integral');
            $cover = $request->get('cover');
            $content = $request->get('content');
            $texture = $request->get('texture');
            $textureMixType = $request->get('textMixType');
            $keyword = $request->get('keyword');
            if(empty($type) || empty($name) || empty($integral) || empty($cover) || empty($content) || empty($texture) || empty($textureMixType) || empty($keyword))
            {
                return response()->json(['error'=>'不能有选项为空']);
            }

            DB::beginTransaction();
            $filter = new MakeFilterFile;
            $filter->user_id = $user_id;
            $filter->name = $name;
            $filter->integral = $integral;
            $filter->cover = $cover;
            $filter->content = $content;
            $filter->texturl = $texture;
            $filter->texture_mix_type_id = $textureMixType;
            $filter->time_add = time();
            $filter->time_update = time();
            $filter->operator_id = $id;
            $filter->vipfree = $vipfree;
            $filter->save();
            foreach($type as $k => $v){
                $filter_id = $filter->id;
                $folder_id = MakeFilterFolder::where('name','=',$v->name)->first()->id;
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

            foreach($keyword as $k => $v)
            {

                $filter_id = $filter->id;
                $keyword = Keywords::where('keyword', $v)->first();
                if ($keyword) {
                    $keyword_id = $keyword->id;

                } else {
                    $newKeyword = Keywords::create([
                        'keyword' => $item,
                        'create_at' => time(),
                        'update_at' => time()
                    ]);
                    $keyword_id = $newKeyword->id;

                }

                $filterKeyword = new FilterKeyword;
                $filterKeyword->keyword_id = $keyword_id;
                $filterKeyword->filter_id = $filter_id;
                $filterKeyword->time_add = time();
                $filterKeyword->time_update = time();
                $filterKeyword->save();

            }

            DB::commit();
            return response()->json(['message'=>'发布成功'],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

}
