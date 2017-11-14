<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\ActivityBonusSet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use DB;

class CompetitionController extends BaseSessionController
{
    private $paginate = 20;

    /**
     * 赛事奖金分配设置首页
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 是否审批通过
        $active = 1 == $request->get('active',1) ? 1 : 0;

        // 获取话题集合
        $data = ActivityBonusSet::whereActive($active)
            -> orderBy('level')
            -> paginate($this -> paginate,['id','level','count_user','amount','prorata','time_add','time_update']);

        // 设置返回数组
        $res = [
            'num'=>$request->input('num',20),
            'active'=>$active,
        ];

        // 返回视图
        return view('admin/content/competition/index',['data'=>$data,'request'=>$res]);
    }

    /**
     * 添加  由于算法限制，暂时关闭添加功能
     *
     * @return \Illuminate\Http\Response
     */
//    public function add()
//    {
//        return view('admin/content/competition/add');
//    }

    /**
     * 编辑赛事奖金分配
     *
     * @param Request $request
     * @return $this
     */
    public function edit(Request $request)
    {
        try {
            // 类型
            $type = $request -> get('type');

            $data = ActivityBonusSet::active()
                -> orderBy('level')
                -> get(['id', 'level', 'count_user', 'amount', 'prorata']);

            return view('admin/content/competition/edit')
                ->with(['data' => $data, 'type' => $type]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * 更新赛事设置
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            // 类型
            $type = $request -> get('type');

            // 接收数据
            $input = $request -> all();

            $data = ActivityBonusSet::active() -> get(['id', 'count_user', 'amount', 'prorata']);

            $time = getTime();

            DB::beginTransaction();

            switch($type){
                case 1:
                    foreach($data as $key => $value){
                        $value -> count_user = (int)$input['id_'.$value->id];
                        $value -> time_update = $time;
                        $value -> save();
                    }
                    break;
                case 2:
                    // 判断和是不是等于100
                    $sum = 0;
                    foreach($data as $k => $v){
                        $sum += $input['id_'.$v->id];
                    }
                    if(100 !== $sum) return back();

                    foreach($data as $key => $value){
                        $value -> amount = $input['id_'.$value->id]/100;
                        $value -> time_update = $time;
                        $value -> save();
                    }
                    break;
                case 3:
                    // 判断和是不是大于100
                    if($input['prorata']>100) return back();

                    foreach($data as $key => $value){
                        $value -> prorata = $input['prorata']/100;
                        $value -> time_update = $time;
                        $value -> save();
                    }
                    break;
            }

            DB::commit();

            return redirect('admin/content/competition/index');
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            abort(404);
        } catch (\Exception $e) {
            DB::rollback();
            abort(404);
        }
    }
}
