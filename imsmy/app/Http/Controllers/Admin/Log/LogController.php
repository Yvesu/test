<?php

namespace App\Http\Controllers\Admin\Log;

use App\Models\Channel;
use App\Models\Topic;
use App\Models\Activity;
use App\Models\TweetReply;
use App\Models\TweetManageLog;
use App\Models\SitesConfigManageLog;
use App\Models\UserManageLog;
use App\Models\TopicManageLog;
use App\Models\ActivityManageLog;
use App\Models\ReplyManageLog;
use App\Models\Admin\Administrator;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\BaseSessionController;
use Auth;
use DB;

/**
 * 后台操作日志查看
 * Class LogController
 * @package App\Http\Controllers\Admin\Log
 */
class LogController extends BaseSessionController
{
    protected $paginate = 20;

    /**
     * 动态操作列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tweet(Request $request){

        try{

            // 搜索条件
            $search = post_check($request -> get('search'));
            $num = (int)$request->input('num',$this->paginate);

            // 获取集合
            $datas = TweetManageLog::orderBy('id','desc');

            // 是否为搜索
            if($search){

                // 条件
                switch($request -> get('condition')){

                    // 动态ID
                    case 1:
                        // 判断是否符合规则
                        $datas = $datas->where('data_id','like','%'.$search.'%');
                        break;

                    // 审批人
                    case 2:

                        // 获取审批人的id
                        $admin_id = Administrator::where('name',$search)->first()->id;

                        $datas = $datas->where('admin_id',$admin_id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> paginate($num);

            // 遍历集合，从local_user表中取数据
            $datas->each(function($data){

                // 获取审批人的名称
                $data -> user_name = Administrator::find($data->admin_id)->name;

                // 频道名称
                $data -> channel_name = implode(',',Channel::whereIn('id',explode(',',$data->channel_ids))->pluck('name')->all());

                // 所属话题
                $data -> topic_name = implode(',',Topic::whereIn('id',explode(',',$data->topic_ids))->pluck('name')->all());

                // 所属活动
                $data -> activity_name = implode(',',Activity::whereIn('id',explode(',',$data->topic_ids))->pluck('name')->all());

            });

            // 搜索类型
            $cond = [1=>'动态ID',2=>'审批人'];

            // 设置返回数组
            $res = [
                'condition' => $request -> get('condition'),
                'num'=>$num,
                'search'=>$search,
            ];

            // 返回视图
            return view('admin/log/tweet',[
                'datas'=>$datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 用户操作列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function user(Request $request){

        try{

            // 搜索条件
            $search = post_check($request -> get('search'));
            $num = (int)$request->input('num',$this->paginate);

            // 获取集合
            $datas = UserManageLog::orderBy('id','desc');

            // 是否为搜索
            if($search){

                // 条件
                switch($request -> get('condition')){

                    // 用户ID
                    case 1:
                        // 判断是否符合规则
                        $datas = $datas->where('data_id','like','%'.$search.'%');
                        break;

                    // 审批人
                    case 2:

                        // 获取审批人的id
                        $admin_id = Administrator::where('name',$search)->first()->id;

                        $datas = $datas->where('admin_id',$admin_id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> paginate($num);

            // 遍历集合，从local_user表中取数据
            $datas->each(function($data){

                // 获取审批人的名称
                $data -> user_name = Administrator::find($data->admin_id)->name;
            });

            // 搜索类型
            $cond = [1=>'用户ID',2=>'审批人'];

            // 设置返回数组
            $res = [
                'condition' => $request -> get('condition'),
                'num'=>$num,
                'search'=>$search,
            ];

            // 返回视图
            return view('admin/log/user',[
                'datas'=>$datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 话题操作列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function topic(Request $request){

        try{

            // 搜索条件
            $search = post_check($request -> get('search'));
            $num = (int)$request->input('num',$this->paginate);

            // 获取集合
            $datas = TopicManageLog::orderBy('id','desc');

            // 是否为搜索
            if($search){

                // 条件
                switch($request -> get('condition')){

                    // 话题ID
                    case 1:
                        // 判断是否符合规则
                        $datas = $datas->where('data_id','like','%'.$search.'%');
                        break;

                    // 审批人
                    case 2:

                        // 获取审批人的id
                        $admin_id = Administrator::where('name',$search)->first()->id;

                        $datas = $datas->where('admin_id',$admin_id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> paginate($num);

            // 遍历集合，从local_user表中取数据
            $datas->each(function($data){

                // 获取审批人的名称
                $data -> user_name = Administrator::find($data->admin_id)->name;

                // 获取话题名称
                $data -> topic_name = Topic::find($data->data_id)->name;
            });

            // 搜索类型
            $cond = [1=>'话题ID',2=>'审批人'];

            // 设置返回数组
            $res = [
                'condition' => $request -> get('condition'),
                'num'=>$num,
                'search'=>$search,
            ];

            // 返回视图
            return view('admin/log/topic',[
                'datas'=>$datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 活动操作列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function activity(Request $request){

        try{

            // 搜索条件
            $search = post_check($request -> get('search'));
            $num = (int)$request->input('num',$this->paginate);

            // 获取集合
            $datas = ActivityManageLog::orderBy('id','desc');

            // 是否为搜索
            if($search){

                // 条件
                switch($request -> get('condition')){

                    // 活动ID
                    case 1:
                        // 判断是否符合规则
                        $datas = $datas->where('data_id','like','%'.$search.'%');
                        break;

                    // 审批人
                    case 2:

                        // 获取审批人的id
                        $admin_id = Administrator::where('name',$search)->first()->id;

                        $datas = $datas->where('admin_id',$admin_id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> paginate($num);

            // 遍历集合，从local_user表中取数据
            $datas->each(function($data){

                // 获取审批人的名称
                $data -> user_name = Administrator::find($data->admin_id)->name;

                // 获取话题名称
                $data -> activity_name = Activity::find($data->data_id)->name;
            });

            // 搜索类型
            $cond = [1=>'活动ID',2=>'审批人'];

            // 设置返回数组
            $res = [
                'condition' => $request -> get('condition'),
                'num'=>$num,
                'search'=>$search,
            ];

            // 返回视图
            return view('admin/log/activity',[
                'datas'=>$datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 评论操作列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reply(Request $request){

        try{

            // 搜索条件
            $search = post_check($request -> get('search'));
            $num = (int)$request->input('num',$this->paginate);

            // 获取集合
            $datas = ReplyManageLog::orderBy('id','desc');

            // 是否为搜索
            if($search){

                // 条件
                switch($request -> get('condition')){

                    // 评论ID
                    case 1:
                        // 判断是否符合规则
                        $datas = $datas->where('data_id','like','%'.$search.'%');
                        break;

                    // 审批人
                    case 2:

                        // 获取审批人的id
                        $admin_id = Administrator::where('name',$search)->first()->id;

                        $datas = $datas->where('admin_id',$admin_id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> paginate($num);

            // 遍历集合，从local_user表中取数据
            $datas->each(function($data){

                // 获取审批人的名称
                $data -> user_name = Administrator::find($data->admin_id)->name;

                // 获取评论内容
                $data -> reply_content = TweetReply::find($data->data_id)->content;
            });

            // 搜索类型
            $cond = [1=>'评论ID',2=>'审批人'];

            // 设置返回数组
            $res = [
                'condition' => $request -> get('condition'),
                'num'=>$num,
                'search'=>$search,
            ];

            // 返回视图
            return view('admin/log/reply',[
                'datas'=>$datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 网站配置操作列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function maintain(Request $request){

        try{

            // 搜索条件
            $search = post_check($request -> get('search'));
            $num = (int)$request->input('num',$this->paginate);

            // 获取集合
            $datas = SitesConfigManageLog::orderBy('id','desc');

            // 是否为搜索
            if($search){

                // 条件
                switch($request -> get('condition')){

                    // 动态ID
                    case 1:
                        // 判断是否符合规则
                        $datas = $datas->where('data_id','like','%'.$search.'%');
                        break;

                    // 审批人
                    case 2:

                        // 获取审批人的id
                        $admin_id = Administrator::where('name',$search)->first()->id;

                        $datas = $datas->where('admin_id',$admin_id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> paginate($num);

            // 遍历集合，从local_user表中取数据
            $datas->each(function($data){

                // 获取审批人的名称
                $data -> user_name = Administrator::find($data->admin_id)->name;
            });

            // 搜索类型
            $cond = [1=>'ID',2=>'审批人'];

            // 设置返回数组
            $res = [
                'condition' => $request -> get('condition'),
                'num'=>$num,
                'search'=>$search,
            ];

            // 返回视图
            return view('admin/log/maintain',[
                'datas'=>$datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

}
