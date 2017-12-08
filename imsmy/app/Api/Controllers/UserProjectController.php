<?php

namespace App\Api\Controllers;

use App\Api\Transformer\Project\ProjectTransformer;
use App\Models\Project\UserProject;
use App\Models\Project\UserProjectConditions;
use App\Models\Project\UserProjectIntro;
use App\Models\FilmMenu;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use CloudStorage;
use Auth;
use DB;

/**
 * 用户项目管理模块
 * Class UserProjectController
 * @package App\Http\Controllers\Admin\Demand
 */
class UserProjectController extends BaseController
{
    private $projectTransformer;

    public function __construct(ProjectTransformer $projectTransformer){
        $this -> projectTransformer = $projectTransformer;
    }

    /**
     * 用户发布项目所请求数据 影片类型
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add()
    {
        try{
            // 获取影片类型
            $films = FilmMenu::active()->get(['id','name'])->all();

            return response()->json(['films'=>$films],200);

        }catch(ModelNotFoundException $e) {
            return response()->json(['error' => snake_case(class_basename($e->getModel())) . '_not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 用户发布项目保存
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

            // 获取现在时间
            $time = getTime();

            // 判断接收数据是否符合规范 判断影片类型是否存在
            if(!is_numeric($data['amount']) || !FilmMenu::find((int)$data['film_id']))
                return response()->json(['error' => 'bad_request'], 403);

            // 开启事务
            DB::beginTransAction();

            // 保存用户提交信息
            $project = UserProject::create([
                'user_id'       => $id,
                'name'          => removeXSS($data['name']),
                'amount'        => $data['amount'],
                'contacts'      => removeXSS($data['contacts']),
                'phone'         => (int)$data['phone'],
                'film_id'       => (int)$data['film_id'],
                'city'          => removeXSS($data['city']),
                'time_add'      => $time,
                'time_update'   => $time
            ]);

            // 保存用户提交的项目介绍
            UserProjectIntro::create([
                'project_id'    => $project->id,
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

                $new_key = 'project/' . $project->id . '/' . getTime() . $arr[sizeof($arr) - 1];

                // 修改地址
                $qiniu[$data['cover']] = $new_key;

                // 修改地址
                $project->cover = $new_key;
            }else{

                return response()->json(['error' => 'bad_request'], 403);
            }

            // 对上传视频进行重命名
            if (isset($data['video'])) {
                $arr = explode('/',$data['video']);
                $new_video_key = 'project/' . $project->id . '/' . $arr[sizeof($arr) - 1];
                $qiniu[$data['video']] = $new_video_key;
                $project -> video = $new_video_key;
            }

            // 对上传方案进行重命名
            if (isset($data['scheme'])) {
                $arr = explode('/',$data['scheme']);
                $new_scheme_key = 'project/' . $project->id . '/' . $arr[sizeof($arr) - 1];
                $qiniu[$data['scheme']] = $new_scheme_key;
                $project -> scheme = $new_scheme_key;
            }

            // 将存在七牛云上的内容进行重命名
            CloudStorage::batchRename($qiniu);

            // 对项目进行保存
            $project->save();

            // 事务提交
            DB::commit();

            return response()->json(['id'=>$project->id],201);

        }catch (\Exception $e) {

            // 事务回滚
            DB::rollback();

            return response()->json(['error' => 'not_found'],404);
        }
    }

    /**
     * 项目详情
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details($id)
    {
        try{
            // 获取项目
            $project = UserProject::with('belongsToUser','hasOneIntro','hasOneFilm','hasManyInvestor','hasManyTeam','hasManyProgress')
                        ->whereIn('active',[1,2])
                        ->findOrFail($id);

            // 返回数据
            return response()->json($this->projectTransformer->transform($project),200);

        } catch (\Exception $e) {

            return response()->json(['error' => 'not_found'],404);
        }
    }

    /**
     * 投资页面
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function invest($id)
    {
        try{

            // 获取集合
            $project = UserProject::with('hasManySupplement','hasManyInvestor')
                -> where('id',$id)
                -> whereIn('active',[1,2])
                -> first();

            // 投资条件
            $conditions = UserProjectConditions::whereBetween('amount',[100,5000])->active()->get();

            $data['name'] = $project -> name;
            $data['users_count'] = $project -> hasManyInvestor -> count();

            // 遍历基础条件
            foreach($conditions as $value){

                // 判断
                switch($value->amount){
                    case 100:
                        $data['conditions_100'][] = $value -> content;
                        break;
                    case 1000:
                        $data['conditions_1000'][] = $value -> content;
                        break;
                    case 5000:
                        $data['conditions_5000'][] = $value -> content;
                        break;
                }
            }

            // 判断是否有附带条件
            if(isset($project -> hasManySupplement)){

                // 遍历附带条件
                foreach($project -> hasManySupplement as $key => $value){

                    // 判断
                    switch($value->amount){
                        case 100:
                            $data['conditions_100'][] = $value -> content;
                            break;
                        case 1000:
                            $data['conditions_1000'][] = $value -> content;
                            break;
                        case 5000:
                            $data['conditions_5000'][] = $value -> content;
                            break;
                    }
                }
            }

            // 返回数据
            return response()->json($data,200);

        } catch (\Exception $e) {

            return response()->json(['error' => 'not_found'],404);
        }
    }

    /**
     * 支持页面
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function support($id,Request $request)
    {
        try{
            // 获取要查询的支持类型
            $type = $request -> get('type') == 2 ? 2 : 1;

            // 获取相应支持类型的集合
            $project = UserProject::with(['hasManySupport' => function($query) use($type) {
                $query -> where('type',$type);
            }])-> where('id',$id)
                -> whereIn('active',[1,2])
                -> first();

            // 投资条件
            $conditions = UserProjectConditions::where('amount',$type == 1 ? 1 : 10)->active()->get(['content']);

            $data['name'] = $project -> name;
            $data['users_count'] = $project -> hasManySupport -> count();

            // 遍历基础条件
            foreach($conditions as $value){

                $data['conditions'][] = $value -> content;
            }

            // 返回数据
            return response()->json($data,200);

        } catch (\Exception $e) {

            return response()->json(['error' => 'not_found'],404);
        }
    }

}
