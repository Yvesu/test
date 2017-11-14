<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Admin\Administrator;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\{Activity,Notification};
use App\Models\ReplyManageLog;
use App\Models\TweetManageLog;
use App\Models\TopicManageLog;
use App\Models\ActivityManageLog;
use App\Models\ZxUserComplains;
use App\Models\UserComplainsLog;
use App\Models\Topic;
use App\Models\Tweet;
use App\Models\TweetReply;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Auth;
use DB;

class ComplainsController extends BaseSessionController
{

    private $paginate = 8;

    /**
     * 投诉举报首页
     * @return $this
     */
    public function index(Request $request)
    {
        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search'));

        // 获取广告状态
        $style = (int)$request->get('style',1);

        // 获取集合
        $complains = ZxUserComplains::with('belongsToCause')->orderBy('id','desc');

        // style 0为未处理，1为已处理
        switch($style){
            case 0:
                $complains -> where('status',0);
                break;
            case 1:
                $complains -> where('status','<>',0);
                break;
            default:
                return back();
        }

        // 是否为搜索
        if($search){

            // 条件
            switch($condition){
                // id查询
                case 1:
                    $complains = $complains->where('id','like','%'.(int)$search.'%');
                    break;
                // 内容查询
                case 2:
                    $complains = $complains->where('content','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }
        }

        // 筛选集合
        $complains = $complains -> orderBy('id','DESC') -> paginate((int)$request->input('num',$this->paginate));

        // 遍历添加值
        foreach($complains as $complain){

            switch($complain->type){
                case 0:
                    $complain -> type = '动态';
                    break;
                case 1:
                    $complain -> type = '话题';
                    break;
                case 2:
                    $complain -> type = '活动';
                    break;
                case 3:
                    $complain -> type = '评论';
                    break;
            }

            // 处理该投诉的工作人员
            if($complain -> staff_id){

                $complain -> user_name = Administrator::find($complain -> staff_id)->name;
            }
        }

        // 搜索条件
        $cond = [1=>'ID',2=>'投诉内容'];

        // 登录用户
        $user = Auth::guard('web')->user();

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$request->input('num',$this->paginate),
            'search'=>$request -> get('search'),
            'style'=>$style,
        ];

        // 返回视图
        return view('admin/complains/index',[
            'user'=>$user,
            'complains'=>$complains,
            'request'=>$res,
            'condition'=>$cond
        ]);
    }
//    public function getIndex(Request $request){
//
//        // 登录用户
//        $user = Auth::guard('web')->user();
//
//        // 获取集合
//        $complains = ZxUserComplains::with('belongsToCause')->orderBy('id','desc');
//
//        // 获取投诉举报的状态
//        $style = $request->get('style',1);
//
//        // style 0为未处理，1为已处理
//        switch($style){
//            case 0:
//                $complains -> where('status',0);
//                break;
//            case 1:
//                $complains -> where('status',1) -> orWhere('status',2);
//                break;
//        }
//
//        // 取出相应数量
//        $complains = $complains -> paginate($this->paginate);
//
//        foreach($complains as $complain){
//
//            switch($complain->type){
//                case 0:
//                    $complain -> type = '动态';
//                    break;
//                case 1:
//                    $complain -> type = '话题';
//                    break;
//                case 2:
//                    $complain -> type = '活动';
//                    break;
//                case 3:
//                    $complain -> type = '评论';
//                    break;
//            }
//
//            // 处理该投诉的工作人员
//            if($complain -> staff_id){
//
//                $complain -> user_name = Administrator::find($complain -> staff_id)->name;
//            }
//        }
//
//        // 设置返回数组
//        $res = [
//            'style'=>$request->input('style',1),
//            'search'=>$request->input('search',''),
//        ];
//
//        // 返回数据
//        return view('/admin/complains/index',['user'=>$user,'complains'=>$complains,'request'=>$res]);
//    }

    /**
     * 详情页
     * @return $this
     */
    public function details(Request $request){

        try {

            // 获取id
            $id = $request -> get('id');

            // 判断是否为数字
            if(!is_numeric($id)) abort(404);

            // 获取该集合
            $complain = ZxUserComplains::findOrFail($id);

            // 核实编号
            switch($complain->type){
                case 0:
                    $complain -> type_name = '动态';
                    $data = Tweet::find($complain -> type_id);
                    break;
                case 1:
                    $complain -> type_name = '话题';
                    $data = Topic::find($complain -> type_id);
                    break;
                case 2:
                    $complain -> type_name = '活动';
                    $data = Activity::find($complain -> type_id);
                    break;
                case 3:
                    $complain -> type_name = '评论';
                    $data = TweetReply::find($complain -> type_id);
                    break;
            }

            // 判断是否存在
            if(!$data) abort(404);

            // 处理该投诉的工作人员
            if($complain -> staff_id){

                $complain -> user_name = Administrator::find($complain -> staff_id)->name;
            }

            // 返回数据
            return view('/admin/complains/details',['complain'=>$complain,'data'=>$data]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 保存修改
     * @return $this
     */
    public function update(Request $request){
        try {

            // 获取id及要修改的状态
            $id = $request -> get('id');
            $active = (int)$request -> get('active');

            // 判断是否为数字，1为正常，2为屏蔽
            if(($active !== 1 && $active !== 2)) abort(404);

            // 获取登录人员信息
            $user = Auth::guard('web')->user();

            // 开启事务
            DB::beginTransAction();

            // 获取该集合
            $complain = ZxUserComplains::findOrFail($id);

            // 日志数组
            $log = [
                'admin_id'  => $user -> id,
                'data_id'   => $complain -> type_id,
                'active'      => 2,
                'time_add'  => getTime()
            ];

            // 判断处理状态
            if($active == 2){
                switch($complain->type){
                    case 0:
                        Tweet::findOrFail($complain -> type_id)->update(['active'=>2]);
                        TweetManageLog::create($log);
                        break;
                    case 1:
                        Topic::findOrFail($complain -> type_id)->update(['active'=>2]);
                        TopicManageLog::create($log);
                        break;
                    case 2:
                        Activity::findOrFail($complain -> type_id)->update(['active'=>2]);
                        ActivityManageLog::create($log);
                        break;
                    case 3:
                        TweetReply::findOrFail($complain -> type_id)->update(['status'=>1]);
                        ReplyManageLog::create($log);
                        break;
                }

                // 成功受理用户投诉，并将处理消息以提醒消息方式发送到用户APP端
                Notification::create([
                    'user_id'           => 100000, // 系统账号
                    'notice_user_id'    => $complain->user_id,
                    'type'              => 7,
                    'type_id'           => $complain->id,
                ]);
            }

            // 修改状态
            $complain -> status = $active;

            // 处理人员
            $complain -> staff_id = session('admin')->id;

            // 处理时间
            $complain -> time_update = getTime();

            // 保存
            $complain -> save();

            // 存入日志表 准备删除
//            UserComplainsLog::create([
//                'admin_id'    => $user -> id,
//                'complain_id' => $id,
//                'type'        => $active,
//                'time_add'    => getTime()
//            ]);

            // 提交事务
            DB::commit();

            // 返回数据
            return redirect('admin/complains/index');

        } catch (ModelNotFoundException $e) {

            // 事务回滚
            DB::rollBack();

            abort(404);
        }
    }

}
