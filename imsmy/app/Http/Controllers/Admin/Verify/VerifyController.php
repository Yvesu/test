<?php

namespace App\Http\Controllers\Admin\Verify;

use App\Http\Controllers\Controller;
use App\Models\UserVerify;
use App\Models\User;
use App\Models\UserVerifyLog;
use App\Http\Controllers\Admin\BaseSessionController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Auth;
use DB;

/**
 * 用户认证
 * Class VerifyController
 * @package App\Http\Controllers\Admin\Content
 */
class VerifyController extends BaseSessionController
{

    private $paginate = 20;

    /**
     * 首页
     * @return $this
     */
    public function index(Request $request)
    {
        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search'));
        $num = (int)$request->input('num',$this->paginate);

        // 认证状态，0为未审批，1为通过，2为不通过
        $active = (int)$request->get('active',1);

        // 获取集合
        $datas = UserVerify::where('verify_status',$active)->orderBy('id','desc');

        // 是否为搜索
        if($search){

            // 条件
            switch($condition){
                // 用户id
                case 1:
                    $datas = $datas->where('user_id','like','%'.$search.'%');
                    break;
                // 认证类型，1为个人认证，2为企业认证
                case 2:
                    $datas = $datas->where('verify',substr_count('个',$search) ? 1 : 2);
                    break;
                // 认证信息
                case 3:
                    $datas = $datas->where('verify_info','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }
        }

        // 筛选集合
        $datas = $datas -> paginate($num);

        // 搜索类型
        $cond = [1=>'用户ID',2=>'认证类型',3=>'认证信息'];

        // 登录用户
        $user = Auth::guard('web')->user();

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$num,
            'search'=>$search,
            'active'=>$active,
        ];

        // 返回视图
        return view('admin/user_verify/index',[
            'user'=>$user,
            'datas'=>$datas,
            'request'=>$res,
            'condition'=>$cond
        ]);
    }

    /**
     * 详情页
     * @return $this
     */
    public function details(Request $request)
    {
        try {
            // 获取id
            $id = (int)$request -> get('id');

            // 判断是否有值
            if(!$id) abort(404);

            // 获取基本信息
            $data = UserVerify::findOrFail($id);

            // 加载模板，附带信息
            return view('admin/user_verify/details',['data'=>$data]);

            // 异常，返回 404 页面
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 更改信息
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        try {

            // 获取id
            $id = (int)$request -> get('id');

            // 判断是否有值
            if(!$id) abort(404);

            // 获取基本信息
            $data = UserVerify::findOrFail($id);

            // 获取状态
            $active = (int)$request -> get('active');

            // 判断
            if($active !== 1 && $active !== 2) abort(404);

            // 修改状态
            $data -> verify_status = $active;

            // 开启事务
            DB::beginTransAction();

            // 修改 user 表中的数据
            User::findOrfail($data -> user_id) -> update(['verify'=>$data -> verify]);

            // 保存集合
            $data -> save();

            // 存入日志表
            UserVerifyLog::create([
                'admin_id'  => $user = Auth::guard('web')->user() -> id,
                'verify_id' => $id,
                'type'      => $active,
                'time_add'  => getTime()
            ]);

            // 提交事务
            DB::commit();

            // 重定向
            return redirect('admin/verify/details?id=' . $id);

        } catch (ModelNotFoundException $e) {

            // 事务回滚
            DB::rollBack();

            // 404 错误
            abort(404);
        }
    }


}
