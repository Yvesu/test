<?php

namespace App\Api\Controllers;

use App\Models\Lease\UserLease;
use App\Models\Lease\UserLeaseIntro;
use App\Models\Lease\UserLeaseType;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use CloudStorage;
use Auth;
use DB;

/**
 * 用户租赁管理模块
 * Class UserDemandController
 * @package App\Http\Controllers\Admin\Demand
 */
class UserLeaseController extends BaseController
{
    /**
     * 用户发布租赁所请求数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function types($id)
    {
        try{
            // 匹配用户是否为认证用户
            if(!User::findOrFail($id)->verify) return response()->json(['error' => 'not_verify'], 402);

            // 获取租赁发布所需信息
            $types = UserLeaseType::active()->get(['id','name','pid'])->all();

            return response()->json(['types'=>$types],200);

        } catch (\Exception $e) {

            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户发布租赁保存
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert($id,Request $request)
    {
        try{
            // 匹配用户是否为认证用户
            if(!User::findOrFail($id)->verify) return response()->json(['error' => 'not_verify'], 402);

            // 获取用户提交的所有数据
            $data = $request -> all();

            // 获取现在时间
            $time = getTime();

            // 判断接收数据是否符合规范
            if(!is_numeric($data['cost']) || !is_numeric($data['type_id']))
                return response()->json(['error' => 'bad_request'], 403);

            // 费用类型，值为数组的键，0 1,执行速度更快
            $cost_type = array(0=>1,1=>1);

            // 判断影片类型是否存在,及判断费用类型 天，月
            if(!UserLeaseType::find($data['type_id']) || !isset($cost_type[$data['cost_type']]))
            return response()->json(['error' => 'bad_request'], 403);

            // 开启事务
            DB::beginTransAction();

            // 保存用户提交信息
            $lease = UserLease::create([
                'user_id'      => $id,
                'type_id'      => $data['type_id'],
                'cost'         => $data['cost'],
                'cost_type'    => $data['cost_type'],
                'ad'           => removeXSS($data['ad']),
                'ad_details'   => removeXSS($data['ad_details']),
                'time_add'     => $time,
                'time_update'  => $time
            ]);

            // 保存用户提交的介绍
            UserLeaseIntro::create([
                'lease_id'      => $lease->id,
                'intro'         => removeXSS($data['intro']),
                'time_add'      => $time,
                'time_update'   => $time,
            ]);

            // 处理上传图片
            if(isset($data['accessory'])){

                // 解析json格式数据
                $photos = json_decode($data['accessory'],true);

                // 声明空数组
                $result = [];

                // 遍历重命名
                foreach ($photos as $photo) {
                    $arr = explode('/',$photo);
                    $new_key = 'lease/' . $lease->id . '/' . $arr[sizeof($arr) - 1];
                    $data[$photo] = $new_key;
                    $result[] = $new_key;
                }

                // 将数据转成json格式
                $lease->accessory = json_encode($result);

                // 将存在七牛云上的内容进行重命名
                CloudStorage::batchRename($data);
            }

            $lease->save();

            // 事务提交
            DB::commit();

            return response()->json(['id'=>$lease->id],201);

        } catch (\Exception $e) {

            // 事务回滚
            DB::rollback();

            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户提前结束租赁
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id,Request $request)
    {
        try{
            // 接收要结束的租赁id
            if(!$lease_id = (int)$request -> get('lease_id'))
                return response()->json(['error' => 'bad_request'], 403);

            // 获取租赁详情
            $lease = UserLease::where('user_id',$id)->findOrFail($lease_id);

            // 判断是否已经删除
            if($lease->active === 2) return response()->json(['error' => 'bad_request'], 403);

            // 删除
            $lease->active = 2;

            // 保存
            $lease->save();

            return response()->json(['status'=>'ok'],200);

        } catch (\Exception $e) {

            // 事务回滚
            DB::rollback();

            return response()->json(['error' => 'not_found'], 404);
        }
    }

}
