<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Admin\Administrator;
use App\Models\View;
use App\Models\Activity;
use App\Models\Topic;
use App\Models\Tweet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use Auth;
use DB;
use Image;

/**
 * Class ViewController 发现页面 大家都在看 广告管理
 * @package App\Http\Controllers\Admin\Content
 */
class ViewController extends BaseSessionController
{

    private $paginate = 8;

    /**
     * 广告首页
     * @return $this
     */
    public function index(Request $request){

        // 登录用户
        $user = Auth::guard('web')->user();

        // 获取广告集合
        $ads = View::orderBy('id','desc');

        // 获取广告状态
        $style = $request->get('style',1);

        // style 0为未审批，1为正常在用，2为过期，3为屏蔽
        switch($style){
            case 0:
                $ads -> wait();
                break;
            case 1:
                $ads -> recommend();
                break;
            case 2:
                $ads -> overdue();
                break;
            default:
                $ads -> forbid();
        }

        // 取出相应数量
        $ads = $ads -> paginate($this->paginate);

        // 遍历集合，从local_user表中取数据
        $ads->each(function($ad){

            // 获取提交人的名称
            $ad -> user_name = Administrator::find($ad->user_id)->name;

            // 获取广告类型
            switch($ad -> type){
                case 0:
                    $ad -> type = '视频';
                    break;
                case 1:
                    $ad -> type = '图片';
                    break;
                case 2:
                    $ad -> type = '话题';
                    break;
                case 3:
                    $ad -> type = '活动';
                    break;
                default:
                    $ad -> type = '网页';
            }
        });

        // 设置返回数组
        $res = [
            'style'=>$request->input('style',1),
            'search'=>$request->input('search',''),
        ];

        // 返回数据
        return view('/admin/discovery_view/index',['user'=>$user,'ads'=>$ads,'request'=>$res]);
    }

    /**
     * 广告详情页
     * @return $this
     */
    public function details(Request $request){

        try {

            // 获取id
            $id = $request -> get('type_id');

            // 判断是否为数字
            if(!is_numeric($id)) abort(404);

            // 获取该广告集合
            $ads = View::findOrFail($id);

            // 核实编号 也就是 type_id
            switch($ads->type){
                case 0:
                    $ads -> type = '视频';
                    $data = Tweet::find($ads -> type_id);
                    break;
                case 1:
                    $ads -> type = '图片';
                    $data = Tweet::find($ads -> type_id);
                    break;
                case 2:
                    $ads -> type = '话题';
                    $data = Topic::find($ads -> type_id);
                    break;
                case 3:
                    $ads -> type = '活动';
                    $data = Activity::find($ads -> type_id);
                    break;
                default:
                    $ads -> type = '网页';
                    $data = 'url';
            }

            // 判断是否存在
            if(!$data) abort(404);

            // 获取提交人的名称
            $ads -> user_name = Administrator::find($ads->user_id)->name;

            // 返回数据
            return view('/admin/discovery_view/details',['ads'=>$ads,'data'=>$data]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 添加广告
     * @return $this
     */
    public function add(){

        // 广告类型
        $ads_type = [0=>'视频',1=>'影集',2=>'话题',3=>'活动',4=>'网页'];

        // 跳转地址
        return view('admin/discovery_view/create')->with(['ads_type'=>$ads_type]);
    }

    /**
     *   保存 新增 广告
     */
    public function insert(Request $request)
    {

        // 获取所有输入的
        $input = $request -> all();

        $icon = $request->file('topic-icon');

        // 判断广告类型
        if(!in_array($input['type'],[0,1,2,3,4])) return back();

        // 初始化,默认值为4，网页
        $url = '';

        // 核实编号 也就是 type_id
        switch($input['type']){
            case 0:
            case 1:
                $data = Tweet::find($input['number']);
                break;
            case 2:
                $data = Topic::find($input['number']);
                break;
            case 3:
                $data = Activity::find($input['number']);
                break;
            default:
                $regex_url = regex_url($input['number']);    // 全局函数，URL匹配
        }

        // 判断是否存在或合规
        if(in_array($input['type'],[0,1,2,3])) {
            if(!$data) return back()->with(['error'=>'未找到相应数据']);
            $type_id = $input['number'];
        }else{
            if(!$regex_url) return back()->with(['error'=>'格式不对']);
            $url = $input['number'];
            $type_id = 4;
        }

        // 获取图片尺寸
        $size = getimagesize($icon)[0].'*'.getimagesize($icon)[1];

        // 获取随机数
        $rand = mt_rand(1000000,9999999);

        # 有效期
        // 起始时间
        $from_time = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['from_date'] . ' ' . $input['from_time']));

        // 终止时间
        $end_time = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['end_date'] . ' ' . $input['end_time']));

        DB::beginTransaction();

        // 存储数据
        $ads = View::create([
            'user_id'           => session('admin')->id,
            'from_time'         => $from_time,
            'end_time'          => $end_time,
            'type'              => $input['type'],
            'type_id'           => $type_id,
            'url'               => $url,
            'time_add'          => getTime(),
            'time_update'       => getTime(),
        ]);

        // 上传文件，并重新命名
        $result = CloudStorage::putFile(
            'view/' . $ads->id . '/' . getTime() . $rand.'_'.$size.'_.'.$icon->getClientOriginalExtension(),
            $icon);

        // 判断是否上传成功，并提交
        if($result[1] !== null){
            DB::rollBack();
        } else {
            $ads->image = $result[0]['key'];
            $ads->save();
            DB::commit();
        }

        return redirect('/admin/advertisement/view/details?type_id=' . $ads->id);
    }

    /**
     *  保存编辑
     * @return $this
     */
    public function update(Request $request){
        try {

            // 获取id及要修改的状态
            $id = $request -> get('id');
            $active = $request -> get('active');

            // 判断是否为数字
            if(($active != 1 && $active != 2) || !is_numeric($id)) abort(404);

            // 获取该广告集合
            $ads = View::findOrFail($id);

            // 修改广告的状态
            $ads -> active = $active;

            // 保存
            $ads -> save();

            // 返回数据
            return redirect('admin/advertisement/view/index');

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

}
