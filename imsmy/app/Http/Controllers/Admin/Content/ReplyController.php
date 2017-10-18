<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\ZxUserComplains;
use App\Models\UserComplainsLog;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\TweetReply;
use App\Models\ReplyManageLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Auth;
use DB;
class ReplyController extends BaseSessionController
{

    private $paginate = 20;

    /**
     * 评论首页
     * @return $this
     */
    public function index(Request $request)
    {
        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search'));
        $active = (int)$request->get('active',1);

        // 获取状态,active 1为正常，2为屏蔽
        $status = $active === 1 ? 0 : 1;

        // 获取集合 status 0为正常，1为屏蔽
        $datas = TweetReply::where('status',$status)->orderBy('id','desc');

        // 是否为搜索
        if($search){

            // 条件
            switch($condition){
                case 1:
                    $datas = $datas->where('id','like','%'.(int)$search.'%');
                    break;
                case 2:
                    $datas = $datas->where('content','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }
        }

        // 筛选集合
        $datas = $datas -> orderBy('id','DESC') -> paginate((int)$request->input('num',$this->paginate));

        // 搜索条件
        $cond = [1=>'ID',2=>'内容'];

        // 登录用户
        $user = Auth::guard('web')->user();

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$request->input('num',$this->paginate),
            'search'=>$request -> get('search'),
            'active'=>$active,
        ];

        // 返回视图
        return view('admin/reply/index',[
            'user'=>$user,
            'datas'=>$datas,
            'request'=>$res,
            'condition'=>$cond
        ]);
    }

    /**
     * 投诉举报首页
     * @return $this
     */
//    public function index(Request $request){
//
//        // 登录用户
//        $user = Auth::guard('web')->user();
//
//        // 获取集合
//        $replies = TweetReplyCheck::with('belongsToReply');
//
//        // 获取状态
//        $style = $request->get('style',1);
//
//        // style 1为已审核，0为未审核，2为正常，3为屏蔽
//        switch($style){
//            case 0:
//                $replies -> wait();
//                break;
//            case 1:
//                $replies -> active();
//                break;
//            case 2:
//                $replies -> whereHas('belongsToReply',function($query){
//                    return $query -> where('status',0);
//                });
//                break;
//            default:
//                $replies -> whereHas('belongsToReply',function($query){
//                    return $query -> where('status',1);
//                });
//        }
//
//        // 取出相应数量
//        $replies = $replies ->orderBy('id','desc') -> paginate($this->paginate);
//
//        // 判断集合数量
//        if($replies->count()){
//
//            // 遍历集合
//            foreach($replies as $reply){
//
//                // 如果为未审批，则修改状态为已经审批
//                if($style == 0 && $reply -> active == 0){
//
//                    // 修改为已审批
//                    $reply -> active = 1;
//
//                    // 处理时间
//                    $reply -> time_update = getTime();
//
//                    // 保存
//                    $reply -> save();
//
//                    // 设置返回集合
//                    $reply -> active = 0;
//                    $reply -> time_update = 0;
//                }
//
//                // 处理该投诉的工作人员,添加至集合中
//                if($reply -> staff_id){
//
//                    $reply -> user_name = Administrator::where('user_id',$user->user_id)->first()->name;
//                }
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
//        return view('/admin/reply/index',['user'=>$user,'replies'=>$replies,'request'=>$res]);
//    }

    /**
     * ajax 屏蔽评论
     * @return $this
     */
    public function ajax(Request $request){

        try {

            // 登录用户
            $user = Auth::guard('web')->user();

            // 获取id
            $id = (int)$request -> get('id');

            // 判断是否为数字
            if(!$id) return response()->json(['status'=>0]);

            // 开启事务
            DB::beginTransAction();

            // 获取评论的集合
            $reply = TweetReply::find($id);

            // 判断该评论是否存在
            if(!$reply) return response()->json(['status'=>0]);

            // 屏蔽
            $reply -> status = 1;

            // 保存
            $reply -> save();

            // zx_user_complains 表
            if($complain = ZxUserComplains::where('type',3)->where('type_id',$id)->first()) {

                // 修改状态
                $complain->update(['time_update' => getTime(), 'status' => 2, 'staff_id' => $user->id]);

                // 存入日志 管理 表中
                ReplyManageLog::create([
                    'admin_id'  => $user -> id,
                    'data_id'   => $id,
                    'type'      => 2,
                    'time_add'  => getTime()
                ]);
            }

            // 提交事务
            DB::commit();

            // 返回
            return response()->json(['status'=>1]);

        } catch (ModelNotFoundException $e) {

            // 事务回滚
            DB::rollBack();

            return response()->json(['status'=>0]);
        }
    }


}
