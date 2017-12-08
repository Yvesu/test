<?php

namespace App\Http\Controllers\NewAdmin\Video;

use App\Models\Admin\Administrator;
use App\Models\Channel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use function PHPSTORM_META\type;

class CommonController extends Controller
{
    //

    /**
     * @return \Illuminate\Http\JsonResponse
     * 分类类别
     */
    public function getType()
    {
        try{
            DB::beginTransaction();
            $data = Channel::where('active','!=',0)->get(['id','name']);
            $type = [];
            foreach ($data as $datum) {
                $tempType = [
                    'id'=>$datum->id,
                    'name'=>$datum->name,
                ];
                array_push($type,$tempType);
            }
            DB::commit();
            return response()->json(['data'=>$type],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);

        }
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
     * 获取操作员
     */
    public function operator()
    {
        try{
            DB::beginTransaction();
            $data = Administrator::get(['id','name']);
            $operator = [];
            foreach ($data as $k => $v)
            {
                $tempData = [
                    'operator_id'=>$v->id,
                    'operator_name'=>$v->name,
                ];
                array_push($operator,$tempData);
            }
            DB::commit();
            return response()->json(['data'=>$operator],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
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


    public function playNum()
    {
        try{
            $data = [
              [
                  'label'=>0,
                  'des' => '全部'
              ],
              [
                  'label'=>20,
                  'des'=>'20以上'],
              [
                  'label' => 50,
                  'des'=>'50以上'
              ],
              [
                  'label'=>100,
                  'des'=>'100以上'
              ],
              [
                  'label'=>200,
                  'des'=>'200以上'
              ],
              [
                  'label'=>500,
                  'des' => '500以上'
              ],
              [
                  'label'=>1000,
                  'des'=>'1000以上'
              ],
              [
                  'label'=>5000,
                  'des'=>'5000以上'
              ],
              [
                  'label'=>10000,
                  'des'=>'1W以上'
              ],
            ];
            return response()->json(['data'=>$data],200);
        }catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
