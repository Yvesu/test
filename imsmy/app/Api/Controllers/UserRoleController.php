<?php

namespace App\Api\Controllers;

use App\Http\Controllers\PremiseController;
use App\Models\Role\UserRole;
use App\Models\Role\UserRoleAudition;
use App\Models\Role\UserRoleBiography;
use App\Models\Role\UserRoleDetails;
use App\Models\Role\UserRoleIntro;
use App\Models\Role\UserRoleType;
use Illuminate\Http\Request;
use App\Models\FilmMenu;
use App\Models\User;
use App\Http\Requests;
use CloudStorage;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 角色管理
 *
 * Class UserRoleController
 * @package App\Api\Controllers
 */
class UserRoleController extends PremiseController
{

    /**
     * 角色添加页面所需资料
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add($id)
    {
        try{
            // 匹配用户是否为认证用户
            if(!User::findOrFail($id)->verify) return response()->json(['error' => 'not_verify'], 402);

            // 获取影片类型
            $films = FilmMenu::active()->get(['id','name'])->all();

            // 角色类型
            $roles = UserRoleType::active()->get(['id','name'])->all();

            return response()->json(['films'=>$films,'roles'=>$roles],200);

        }catch(ModelNotFoundException $e) {

            return response()->json(['error' => 'not_found'],404);
        }catch(\Exception $e){

            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 用户发布角色保存
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert($id,Request $request)
    {
        try{
            // 匹配用户是否为认证用户
            if(!User::findOrFail($id)->verify) return response()->json(['error' => 'bad_request'], 403);

            // 获取用户提交的所有数据
            $data = $request -> all();

            // 获取提交时间
            $time = getTime();

            // 判断接收数据是否符合规范
            if(!is_numeric($data['film_id']) || !is_numeric($data['time_from']) || !is_numeric($data['time_end'])
                || $data['time_from'] < $time || $data['time_end'] < $time)
                return response()->json(['error' => 'bad_request'], 403);

            // 验证电影类型是否存在
            FilmMenu::findOrFail($data['film_id']);

            // 定义数组
            $array_role = [
                'user_id'       => $id,
                'title'         => removeXSS($data['title']),
                'director'      => removeXSS($data['director']),
                'film_id'       => $data['film_id'],
                'time_from'     => $data['time_from'],
                'time_end'      => $data['time_end'],
                'period'        => removeXSS($data['period']),
                'site'          => removeXSS($data['site']),
                'time_add'      => $time,
                'time_update'   => $time
            ];

            // 开启事务
            DB::beginTransAction();

            // 保存用户提交信息
            $role = UserRole::create($array_role);

            // 剧情介绍
            UserRoleIntro::create([
                'role_id'       => $role->id,
                'intro'         => removeXSS($data['intro']),
                'time_add'      => $time,
                'time_update'   => $time,
            ]);

            // 初始化一个数组
            $qiniu = [];

            // 对上传封面进行重命名
            if (isset($data['cover'])) {

                // 重命名
                $arr = explode('/',$data['cover']);

                $new_key = 'role/' . $role->id . '/' . getTime() . $arr[sizeof($arr) - 1];

                // 修改地址
                $qiniu[$data['cover']] = $new_key;

                // 修改地址
                $role->cover = $new_key;
            }else{

                return response()->json(['error' => 'bad_request'], 403);
            }

            $role -> save();

            // 多个角色详情 json格式
            $role_details = json_decode($data['role_details'],true);

            foreach($role_details as $value){

                if(!is_numeric($value['age']) || !is_numeric($value['type_id']))
                    return response()->json(['error' => 'bad_request'], 403);

                $detail = UserRoleDetails::create([
                    'role_id'       => $role->id,
                    'name'          => removeXSS($value['name']),
                    'age'           => $value['age'],
                    'type_id'       => $value['type_id'],
                    'time_add'      => $time,
                    'time_update'   => $time,
                ]);

                UserRoleBiography::create([
                    'details_id'    => $detail->id,
                    'intro'         => removeXSS($value['intro']),
                    'time_add'      => $time,
                    'time_update'   => $time,
                ]);
            }

            // 角色试镜要求 json格式
            $auditions = json_decode($data['auditions'],true);

            foreach($auditions as $audition){

                UserRoleAudition::create([
                    'role_id'       => $role->id,
                    'content'       => removeXSS($audition['content']),
                    'time_add'      => $time,
                    'time_update'   => $time,
                ]);
            }

            // 将存在七牛云上的内容进行重命名
            CloudStorage::batchRename($qiniu);

            // 事务提交
            DB::commit();

            return response()->json(['id'=>$role->id],201);

        }catch(ModelNotFoundException $e) {

            // 事务回滚
            DB::rollback();

            return response()->json(['error' => 'not_found'],404);
        }catch (\Exception $e) {

            // 事务回滚
            DB::rollback();

            return response()->json(['error' => 'not_found'],404);
        }
    }

}