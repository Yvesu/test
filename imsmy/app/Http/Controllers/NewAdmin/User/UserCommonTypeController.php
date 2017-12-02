<?php

namespace App\Http\Controllers\NewAdmin\User;

use App\Models\Admin\Administrator;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;

class UserCommonTypeController extends Controller
{
    //

    /**
     * @return \Illuminate\Http\JsonResponse
     * 用户管理模块粉丝数量类别
     */
    public function getFansNum()
    {
        try{
            $fansNum = [
                [
                    'label'=>0,
                    'des'=>'全部',
                ],
                [
                    'label'=>10,
                    'des'=>'10以上',
                ],
                [
                    'label'=>20,
                    'des'=>'20以上',
                ],
                [
                    'label'=>50,
                    'des'=>'50以上',
                ],
                [
                    'label'=>100,
                    'des'=>'100以上',
                ],
                [
                    'label'=>200,
                    'des'=>'200以上',
                ],
                [
                    'label'=>500,
                    'des'=>'500以上',
                ],
                [
                    'label'=>1000,
                    'des'=>'1000以上',
                ],
                [
                    'label'=>5000,
                    'des'=>'5000以上',
                ],
                [
                    'label'=>10000,
                    'des'=>'1W+',
                ],
            ];
            return response()->json(['data'=>$fansNum],200);
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * 户管理模块播放数量类别
     */
    public function getPlayCount()
    {
        try{
            $playCount = [
                [
                    'label'=>0,
                    'des'=>'全部',
                ],
                [
                    'label'=>50,
                    'des'=>'50以上',
                ],
                [
                    'label'=>100,
                    'des'=>'100以上',
                ],
                [
                    'label'=>200,
                    'des'=>'200以上',
                ],
                [
                    'label'=>500,
                    'des'=>'500以上',
                ],
                [
                    'label'=>1000,
                    'des'=>'1000以上',
                ],
                [
                    'label'=>5000,
                    'des'=>'5000以上',
                ],
                [
                    'label'=>10000,
                    'des'=>'1W+',
                ],
            ];
            return response()->json(['data'=>$playCount],200);
        }catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * 户管理模块作品数量类别
     */
    public function productionNum()
    {
        try{

            $productionNum = [
                [
                    'label'=>0,
                    'des'=>'全部',
                ],
                [
                    'label'=>10,
                    'des'=>'10以上',
                ],
                [
                    'label'=>20,
                    'des'=>'20以上',
                ],
                [
                    'label'=>50,
                    'des'=>'50以上',
                ],
            ];

            return response()->json(['data'=>$productionNum],200);

        }catch (\Exception $e){

            return response()->json(['error' => $e->getMessage()], $e->getCode());

        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * 户管理模块资产数量类别
     */
    public function intergalNum()
    {
        try{
            $integral = [
                [
                    'label'=>0,
                    'des'=>'全部',
                ],
                [
                    'label'=>100,
                    'des'=>'100以上',
                ],
                [
                    'label'=>200,
                    'des'=>'200以上',
                ],
                [
                    'label'=>500,
                    'des'=>'500以上',
                ],
                [
                    'label'=>1000,
                    'des'=>'1000以上',
                ],
                [
                    'label'=>5000,
                    'des'=>'5000以上',
                ],
                [
                    'label'=>10000,
                    'des'=>'1W+',
                ],
            ];
            return response()->json(['data' => $integral],200);
        }catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * 审核人列表
     */
    public function checker()
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 精选操作
     */
    public function doChoiceness(Request $request)
    {
        try{
            $id = $request->get('id',null);
            if(is_null($id))
            {
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            foreach($id as $k => $v)
            {
                $data = User::find($v);
                if($data){
                    if($data->verify > 0 && $data->active != 2 && $data->active != 0)
                    {
                        $data->active = 2;
                        $data->choiceness_id = $admin->id;
                        $data->choiceness_time = time();
                        $data->save();
                    }
                }

            }
            DB::commit();
            return response()->json(['message'=>'加精成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 升级操作
     */
    public function levelUp(Request $request)
    {
        try{
            $id = $request->get('id',null);
            if(is_null($id))
            {
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            DB::beginTransaction();
            foreach ($id as $k => $v)
            {
                $data = User::find($v);
                if($data){
                    if($data->is_phonenumber == 1){
                        $data->is_vip += 1;
                        $data->save();
                    }
                }
            }

            DB::commit();
            return response()->json(['message'=>'升级成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 冻结
     */
    public function doStop(Request $request)
    {
        try{
            $id = $request->get('id',null);
            if(is_null($id))
            {
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            foreach ($id as $k => $v)
            {
                $data = User::find($v);
                if($data){
                    $data->active = 0;
                    $data->stop_id = $admin->id;
                    $data->stop_causes = $request->get('cause',null);
                    $data->stop_time = time();
                    $data->save();
                }
            }
            DB::commit();
            return response()->json(['message'=>'冻结成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 取消精选
     */
    public function cancelChoiceness(Request $request)
    {
        try{
            $id = $request->get('id',null);
            if(is_null($id))
            {
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            DB::beginTransaction();
            foreach ($id as $k => $v)
            {
                $data = User::find($v);
                if($data){
                    if($data->active == 2)
                    {
                        $data->active = 1;
                        $data->save();
                    }
                }
            }
            DB::commit();
            return response()->json(['message'=>'下架成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 解除冻结操作
     */
    public function cancelStop(Request $request)
    {
        try{
            $id = $request->get('id',null);
            if(is_null($id))
            {
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            DB::beginTransaction();
            foreach ($id as $k => $v)
            {
                $data = User::find($v);
                if($data){
                    if($data->active == 0)
                    {
                        $data->active = 1;
                        $data->save();
                    }
                }
            }
            DB::commit();
            return response()->json(['message'=>'解冻成功'],200);
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
            $id = $request->get('id',null);
            if(is_null($id))
            {
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$id);
            DB::beginTransaction();
            foreach ($id as $k => $v)
            {
                $data = User::find($v);
                if($data){
                    if($data->active == 0)
                    {
                        $data->active = 5;
                        $data->save();
                    }
                }
            }
            DB::commit();
            return response()->json(['message'=>'删除成功'],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


}
