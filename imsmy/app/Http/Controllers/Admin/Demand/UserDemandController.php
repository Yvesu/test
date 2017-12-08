<?php

namespace App\Http\Controllers\Admin\Demand;

use App\Models\UserDemand;
use App\Models\UserDemandJob;
use App\Models\FilmMenu;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Admin\BaseSessionController;
use Auth;
use DB;

/**
 * 用户需求管理模块
 * Class UserDemandController
 * @package App\Http\Controllers\Admin\Demand
 */
class UserDemandController extends BaseSessionController
{
    private $paginate = 10;

    /**
     * 用户需求列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try{

            // 搜索条件
            $condition = $request -> get('condition','');
            $search = $request -> get('search','');

            // 是否屏蔽
            $active = $request->get('active',1) == 1 ? 1 : 2;

            $datas = UserDemand::with('hasOneJob','hasOneFilm','hasOneCondition')->where('active',$active);

            // 是否为搜索
            if($search){

                // 条件
                switch($condition){
                    // 用户id
                    case 1:
                        $datas = $datas->where('user_id',(int)$search);
                        break;
                    // 岗位
                    case 2:
                        // 获取岗位id
                        $job_id = UserDemandJob::where('name',post_check($search))->first();
                        $datas = $datas->where('job_id',$job_id->id);
                        break;
                    // 影片
                    case 3:
                        // 获取影片 id
                        $film_id = FilmMenu::where('name',post_check($search))->first();
                        $datas = $datas->where('film_id',$film_id->id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> orderBy('id','DESC') -> paginate((int)$request->input('num',$this->paginate));

            // 搜索条件
            $cond = [1=>'用户',2=>'岗位',3=>'影片'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',$this->paginate),
                'search'=>$search,
                'active'=>$active,
            ];

            // 返回视图
            return view('/admin/user_demand/index',['datas'=>$datas,'request'=>$res,'condition'=>$cond]);
        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 详情
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function details(Request $request)
    {
        try {
            // 话题基本信息
            $data = UserDemand::with('hasOneJob','hasOneFilm','hasOneCondition')->findOrFail((int)$request->get('id'));

            // 将图片转义
            $data -> photos = json_decode($data -> accessory,true);

            return view('admin/user_demand/details',['data'=>$data]);

        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * 修改状态信息
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try{

            // 获取集合
            $data = UserDemand::findOrFail((int)$request -> get('id'));

            // 修改状态
            $active = (int)$request->input('active');

            // 如果状态做了修改
            if($data -> active != $active){

                // 修改状态
                $data -> active = $active === 1 ? 1 : 2;
            }

            // 保存
            $data -> save();

            // 判断
            return redirect('/admin/demand/details?id='.$request -> get('id'))->with('success', '修改成功');

        }catch(\Exception $e){
            abort(404);
        }
    }

}
