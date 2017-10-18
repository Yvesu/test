<?php

namespace App\Api\Controllers;

use App\Facades\CloudStorage;
use App\Models\UserDemand;
use App\Models\UserDemandCities;
use App\Models\UserDemandJob;
use App\Models\UserDemandCondition;
use App\Models\FilmMenu;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Auth;
use DB;

/**
 * 用户需求管理模块
 * Class UserDemandController
 * @package App\Http\Controllers\Admin\Demand
 */
class UserDemandController extends BaseController
{
    /**
     * 用户发布需求所请求数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cities()
    {
        try{
            // 获取需求热门前9个城市
            $cities = UserDemandCities::orderBy('count','desc')->take(9)->pluck('name');

            return response()->json(['cities'=>$cities],200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户发布需求的热门城市
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add()
    {
        try{
            // 获取需求发布所需信息
            $jobs = UserDemandJob::active()->get(['id','name','pid'])->all();

            // 获取影片类型
            $films = FilmMenu::active()->orderBy('sort','DESC')->get(['id','name'])->all();

            return response()->json(['jobs'=>$jobs,'films'=>$films],200);

        }catch(ModelNotFoundException $e) {
            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 用户发布需求保存
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert($id,Request $request)
    {
        try{
            // 匹配用户是否为认证用户
            if(!User::findOrFail($id)->verify) return response()->json(['error' => 'not_verify'], 402);

            // 接收数字型数据
            $data = $request -> only(['cost','job_id','film_id','from_time','end_time','cost_unit','cost_type']);

            foreach($data as $value) {

                if(!is_numeric($value))
                    return response()->json(['error' => 'bad_request'], 403);
            }

            $city = removeXSS($request -> get('city'));
            $job_condition = removeXSS($request -> get('job_condition'));
            $accessory = $request -> get('accessory');

            // 获取现在时间
            $time = getTime();

            // 定义检查类型数组
            $cost_type_check = [0=>1,1=>1,2=>1];

            // 判断接收数据是否符合规范 判断岗位是否存在，判断影片类型是否存在,及判断费用类型 总计，天，月
            if($data['cost'] <= 0 || $data['end_time'] < $time || $data['from_time'] < $time
                || !UserDemandJob::find($data['job_id']) || !FilmMenu::find($data['film_id'])
                || !isset($cost_type_check[$data['cost_type']]))
                return response()->json(['error' => 'bad_request'], 403);

            // 开启事务
            DB::beginTransAction();

            // 保存用户提交的岗位要求
            $condition = UserDemandCondition::create([
                'job_condition' => $job_condition,
                'time_add'      => $time,
                'time_update'   => $time,
            ]);

            // 保存用户提交信息
            $demand = UserDemand::create([
                'user_id'      => $id,
                'job_id'       => $data['job_id'],
                'condition_id' => $condition -> id,
                'film_id'      => $data['film_id'],
                'cost'         => $data['cost'],
                'cost_type'    => $data['cost_type'],
                'cost_unit'    => $data['cost_unit'],
                'city'         => $city,
                'from_time'    => $data['from_time'],
                'end_time'     => $data['end_time'],
                'time_add'     => $time,
                'time_update'  => $time
            ]);

            // 添加热门城市信息
            if($cities = UserDemandCities::where('name',$city)->first()){

                // 数量 +1
                $cities -> count ++;
                $cities -> save();
            }else{

                // 创建热门城市
                UserDemandCities::create([
                    'name'         => $city,
                    'time_add'     => $time,
                    'time_update'  => $time
                ]);
            }

            // 处理上传图片
            if(isset($accessory)){

                // 解析json格式数据
                $photos = json_decode($accessory,true);

                // 声明空数组
                $result = [];

                // 遍历重命名
                foreach ($photos as $photo) {
                    $arr = explode('/',$photo);
                    $new_key = 'demand/' . $demand->id . '/' . $arr[sizeof($arr) - 1];
                    $data[$photo] = $new_key;
                    $result[] = $new_key;
                }

                // 将数据转成json格式
                $demand->accessory = json_encode($result);

                // 将存在七牛云上的内容进行重命名
                CloudStorage::batchRename($data);
            }

            $demand->save();

            // 事务提交
            DB::commit();

            return response()->json(['id'=>$demand->id],201);

        }catch(ModelNotFoundException $e) {

            // 事务回滚
            DB::rollback();

            return response()->json(['error' => 'not_found'],404);
        } catch (\Exception $e) {

            // 事务回滚
            DB::rollback();

            return response()->json(['error' => 'not_found'],404);
        }
    }

    /**
     * 用户提前结束需求
     * TODO 暂时停用，未在路由中添加
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id,Request $request)
    {
        try{
            // 接收要结束的需求id
            if(!$demand_id = (int)$request -> get('demand_id'))
                return response()->json(['error' => 'bad_request'], 403);

            // 获取需求详情
            $demand = UserDemand::where('user_id',$id)->findOrFail($demand_id);

            // 判断是否已经删除
            if($demand->active === 2) return response()->json(['error' => 'bad_request'], 403);

            // 删除
            $demand->active = 2;

            // 保存
            $demand->save();

            return response()->json(['status'=>'ok'],200);

        }catch(ModelNotFoundException $e) {

            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found'],404);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

}
