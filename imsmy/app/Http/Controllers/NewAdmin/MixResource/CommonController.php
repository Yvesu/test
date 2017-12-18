<?php

namespace App\Http\Controllers\NewAdmin\MixResource;

use App\Models\DownloadCost;
use App\Models\Make\MakeEffectsFile;
use App\Models\Make\MakeEffectsFolder;
use App\Models\Make\TextureMixType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class CommonController extends Controller
{
    //
    /**
     * @return \Illuminate\Http\JsonResponse
     * 类别
     */
    public function type()
    {
        try{
            DB::beginTransaction();
            $type = MakeEffectsFolder::where('active','=',1)->get();
            $data = [];
            foreach($type as $k => $v)
            {
                $temp = [
                    'id'=>$v->id,
                    'type'=>$v->name,
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
     * 下载费用
     */
    public function downloadCost()
    {
        try{
            $data = DownloadCost::get();
            $intergal = [];
            foreach($data as $k => $v)
            {
                array_push($intergal,['intergal'=>$v->details]);
            }
            return response()->json(['data'=>$intergal],200);
        }catch (ModelNotFoundException $e){
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


    public function doRecommend(Request $request)
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
                $data = MakeEffectsFile::find($v);
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
                $data = MakeEffectsFile::find($v);
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
     * 进行分类
     */
    public function doType(Request $request)
    {
        try{
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id',null);
            $folder = $request->get('type',null);
            if(is_null($id)||is_null($folder)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeEffectsFile::find($v);
                if($data){
                    $data->folder_id = $folder;
                    $data->time_update = time();
                    $data->save();
                }else{
                    return response()->json(['message'=>'无数据'],200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'分类成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found']);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 屏蔽
     */
    public function doShield(Request $request)
    {
        try {
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id', null);
            if (is_null($id)) {
                return response()->json(['message' => '数据不合法'], 200);
            }
            $id = explode('|', $id);
            foreach ($id as $k => $v) {
                $data = MakeEffectsFile::find($v);
                if ($data) {
                    $data->active = 3;
                    $data->recommend = 0;
                    $data->doshield_id = $admin->id;
                    $data->dorecommend_id = $admin->id;
                    $data->time_update = time();
                    $data->save();
                } else {
                    return response()->json(['message' => '无数据'], 200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'屏蔽成功'],200);
        } catch (ModelNotFoundException $q) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 取消屏蔽
     */
    public function cancelShield()
    {
        try {
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            $id = $request->get('id', null);
            if (is_null($id)) {
                return response()->json(['message' => '数据不合法'], 200);
            }
            $id = explode('|', $id);
            foreach ($id as $k => $v) {
                $data = MakeEffectsFile::find($v);
                if ($data) {
                    $data->active = 1;
                    $data->doshield_id = $admin->id;
                    $data->time_update = time();
                    $data->save();
                } else {
                    return response()->json(['message' => '无数据'], 200);
                }
            }
            DB::commit();
            return response()->json(['message'=>'取消成功'],200);
        } catch (ModelNotFoundException $q) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 向上
     */
    public function up(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $id = $request->get('id');
            DB::beginTransaction();
            $dataup = MakeEffectsFolder::find($id);
            if($dataup){
                return response()->json(['message'=>'无数据'],200);
            }
            $active = $dataup->active;
            $maxsort = MakeEffectsFolder::where('active','=',$active)->orderBy('sort')->max('sort');
            $minsort = MakeEffectsFolder::where('active','=',$active)->orderBy('sort')->min('sort');
            $alldata = MakeEffectsFolder::where('active','=',$active)->orderBy('sort')->get();
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
            $datadown = MakeEffectsFolder::find($id);
            if($dataup){
                return response()->json(['message'=>'无数据'],200);
            }
            $active = $dataup->active;
            $maxsort = MakeEffectsFolder::where('active','=',$active)->orderBy('sort')->max('sort');
            $minsort = MakeEffectsFolder::where('active','=',$active)->orderBy('sort')->min('sort');
            $alldata = MakeEffectsFolder::where('active','=',$active)->orderBy('sort')->get();
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
                $data = MakeEffectsFolder::find($v);
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
            $stopReason = $request->get('stop_reason',null);
            if(is_null($id)||is_null($stopReason)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach($id as $k => $v)
            {
                $data = MakeEffectsFolder::find($v);
                if($data){
                    $data->active = 0;
                    $data->stop_time = time();
                    $data->stop_reason = $stopReason;
                    $data->operator_id = $admin->id;
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


    public function typeDelete(Request $request)
    {
        try{
            DB::beginTransaction();
            $id = $request->get('id',null);
            $stopReason = $request->get('stop_reason',null);
            if(is_null($id)||is_null($stopReason)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach ($id as $k=>$v){
                MakeEffectsFolder::find($v)->delete();
            }
            DB::commit();
            return response()->json(['message'=>'删除成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 删除资源文件
     */
    public function resourceDelete(Request $request)
    {
        try{
            DB::beginTransaction();
            $id = $request->get('id',null);
            $stopReason = $request->get('stop_reason',null);
            if(is_null($id)||is_null($stopReason)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            foreach ($id as $k=>$v){

                MakeEffectsFile::find($v)->delete();
            }
            DB::commit();
            return response()->json(['message'=>'删除成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function mixTexture()
    {
        try{
            DB::beginTransaction();
            $data = TextureMixType::get();
            $texture = [];
            foreach ($data as $k=>$v) {
                $temp = [
                        'id'=>$v->id,
                        'name'=>$v->name,
            ];
            array_push($texture,$temp);
        }
            DB::commit();
            return response()->json(['data'=>$texture],200);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }


    }

}
