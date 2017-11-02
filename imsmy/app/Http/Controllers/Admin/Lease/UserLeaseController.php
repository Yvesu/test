<?php

namespace App\Http\Controllers\Admin\Lease;

use App\Models\Lease\UserLease;
use App\Models\Lease\UserLeaseType;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Admin\BaseSessionController;
use Auth;

/**
 * 用户租赁管理模块
 * Class UserDemandController
 * @package App\Http\Controllers\Admin\Demand
 */
class UserLeaseController extends BaseSessionController
{
    private $paginate = 10;

    /**
     * 用户租赁列表
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

            // 获取数据
            $datas = UserLease::with('hasOneType','hasOneIntro')->where('active',$active);

            // 是否为搜索
            if($search){

                // 条件
                switch($condition){
                    // 用户id
                    case 1:
                        $datas = $datas->where('user_id',(int)$search);
                        break;
                        // 类型
                    case 2:
                        // 获取类型id
                        $type_id = UserLeaseType::where('name',post_check($search))->first();
                        $datas = $datas->where('type_id',$type_id->id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> orderBy('id','DESC') -> paginate((int)$request->input('num',$this->paginate));

            // 搜索条件
            $cond = [1=>'用户',2=>'类型'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',$this->paginate),
                'search'=>$search,
                'active'=>$active,
            ];

            // 返回视图
            return view('/admin/lease_manage/index',['datas'=>$datas,'request'=>$res,'condition'=>$cond]);
        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 详情
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function details(Request $request)
    {
        try {
            // 话题基本信息
            $data = UserLease::with('hasOneType','hasOneIntro')->findOrFail((int)$request->get('id'));

            // 将图片转义
            $data -> photos = json_decode($data -> accessory,true);

            return view('admin/lease_manage/details',['data'=>$data]);

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
            $data = UserLease::findOrFail((int)$request -> get('id'));

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
            return redirect('/admin/lease/details?id='.$request -> get('id'))->with('success', '修改成功');

        }catch(\Exception $e){
            abort(404);
        }
    }

}
