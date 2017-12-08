<?php

namespace App\Http\Controllers\NewAdmin\Template;

use App\Models\Make\MakeTemplateFile;
use Auth;
use App\Models\Admin\Administrator;
use App\Models\Make\MakeTemplateFolder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TemplateCommonController extends Controller
{
    //
    /**
     * @return \Illuminate\Http\JsonResponse
     * 分类条件
     */
    public function type()
    {
        try{
            DB::beginTransaction();
            $type = MakeTemplateFolder::where('active','=',1)->get(['id','name']);
            $data = [];
            foreach($type as $k => $v)
            {
                $temp = [
                    'id' => $v->id,
                    'type' => $v->name,
                ];
                array_push($data,$temp);
            }
            DB::commit();
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 操作员列表
     */
    public function operator()
    {
        try{
            DB::beginTransaction();
            $data = Administrator::all();
            $checker = [];
            foreach ($data as $k => $v)
            {
                $tempData = [
                    'id' => $v->id,
                    'name' => $v->name,
                ];

                array_push($checker,$tempData);
            }
            DB::commit();
            return response()->json(['data'=>$checker],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 时间条件
     */
    public function time()
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
     * 时长条件
     */
    public function duration()
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
     * 下载量条件
     */
    public function count()
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
     * 推荐
     */
    public function recommend(Request $request)
    {
        try{
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeTemplateFile::find($v);
                if($data){
                    $data->recommend = 1;
                    $data->dorecommend_id = $admin->id;
                    $data->time_update = time();
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'推荐成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 取消推荐
     */
    public function cancelRecommend(Request $request)
    {
        try{
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeTemplateFile::find($v);
                if($data){
                    $data->recommend = 0;
                    $data->dorecommend_id = $admin->id;
                    $data->time_update = time();
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'取消成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 热门
     */
    public function hot(Request $request)
    {
        try{
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeTemplateFile::find($v);
                if($data){
                    $data->ishot = 1;
                    $data->ishot_id = $admin->id;
                    $data->time_update = time();
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
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
     * 取消热门
     */
    public function cancelHot(Request $request)
    {
        try{
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeTemplateFile::find($v);
                if($data){
                    $data->ishot = 0;
                    $data->ishot_id = $admin->id;
                    $data->time_update = time();
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'取消成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 屏蔽
     */
    public function shield(Request $request)
    {
        try{
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeTemplateFile::find($v);
                if($data){
                    $data->status = 2;
                    $data->recommend = 0;
                    $data->ishot =0;
                    $data->doshield_id = $admin->id;
                    $data->ishot_id = $admin->id;
                    $data->dorecommend_id = $admin->id;
                    $data->time_update = time();
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'屏蔽成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
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
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeTemplateFile::find($v);
                if($data){
                    $data->status = 1;
                    $data->doshield_id = $admin->id;
                    $data->time_update = time();
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'屏蔽成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 删除
     */
    public function delete(Request $request)
    {
        try{
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeTemplateFile::find($v);
                if($data){
                    $data->status = 0;
                    $data->time_update = time();
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'删除成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
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
            $dataup = MakeTemplateFolder::find($id);
            if($dataup){
                return response()->json(['message'=>'无数据'],200);
            }
            $active = $dataup->active;
            $maxsort = MakeTemplateFolder::where('active','=',$active)->orderBy('sort')->max('sort');
            $minsort = MakeTemplateFolder::where('active','=',$active)->orderBy('sort')->min('sort');
            $alldata = MakeTemplateFolder::where('active','=',$active)->orderBy('sort')->get();
            foreach($alldata as $k => $v)
            {
                if(($v->id)==$id){
                    if($k==0){
                        return response()->json(['message'=>'数据不合法'],200);
                    }
                    $datadown = $alldata[$k-1];
                }
            }
            if(!$datadown){
                return response()->json(['message'=>'数据不合法'],200);
            }
            if($dataup->sort > $minsort && $dataup->sort <= $maxsort)
            {
                $a = $datadown->sort;
                $b = $dataup->sort;
                $dataup -> sort = $a;
                $dataup -> time_update = time();
                $dataup -> operator_id = $admin->id;
                $dataup -> save();

                $datadown -> sort = $b;
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
     * 向下
     */
    public function down(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            DB::beginTransaction();
            $datadown = MakeTemplateFolder::find($id);
            if($dataup){
                return response()->json(['message'=>'无数据'],200);
            }
            $active = $dataup->active;
            $maxsort = MakeTemplateFolder::where('active','=',$active)->orderBy('sort')->max('sort');
            $minsort = MakeTemplateFolder::where('active','=',$active)->orderBy('sort')->min('sort');
            $alldata = MakeTemplateFolder::where('active','=',$active)->orderBy('sort')->get();
            foreach($alldata as $k => $v)
            {
                if(($v->id)==$id){
                    if($k==0){
                        return response()->json(['message'=>'数据不合法'],200);
                    }
                    $dataup = $alldata[$k+1];
                }
            }
            if(!$dataup){
                return response()->json(['message'=>'数据不合法'],200);
            }
            if($dataup->sort > $minsort && $dataup->sort <= $maxsort)
            {
                $a = $datadown->sort;
                $b = $dataup->sort;
                $dataup -> sort = $a;
                $dataup -> time_update = time();
                $dataup -> operator_id = $admin->id;
                $dataup -> save();

                $datadown -> sort = $b;
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
     * 停用
     */
    public function stop(Request $request)
    {
        try{
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeTemplateFolder::find($v);
                if($data){
                    $data->active = 0;
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'停用成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 启用
     */
    public function start(Request $request)
    {
        try{
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeTemplateFolder::find($v);
                if($data){
                    $data->active = 1;
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'启用成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


}
