<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Topic;
use App\Models\Admin\Administrator;
use App\Models\AdvertisingRotation;
use App\Models\Activity;
use App\Models\Tweet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use Auth;
use DB;

/**
 * Class ViewController 发现页面 各频道 广告管理
 * @package App\Http\Controllers\Admin\Content
 */
class AdsController extends BaseSessionController
{

    private $paginate = 20;

    /**
     * 频道广告首页
     * @return $this
     */
    public function index(Request $request)
    {
        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search'));

        // 获取广告状态
        $style = (int)$request->get('style',1);

        // 获取广告集合
        $ads = AdvertisingRotation::orderBy('id','desc');

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

        // 是否为搜索
        if($search){

            // 条件
            switch($condition){
                case 1:
                    $ads = $ads->where('id','like','%'.(int)$search.'%');
                    break;
                case 2:
                    // 名称
                    $ads = $ads->where('name','like','%'.$search.'%');;
                    break;
                default:
                    return back();
            }
        }

        // 筛选集合
        $ads = $ads -> orderBy('id','DESC') -> paginate((int)$request->input('num',$this->paginate));

        // 搜索条件
        $cond = [1=>'ID',2=>'名称'];

        // 登录用户
        $user = Auth::guard('web')->user();

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
            'condition' => $condition,
            'num'=>$request->input('num',20),
            'search'=>$request -> get('search'),
            'style'=>$style,
        ];

        // 返回视图
        return view('admin/channel_ads/index',['user'=>$user,'ads'=>$ads,'request'=>$res,'condition'=>$cond]);
    }

    /**
     * 广告详情页
     * @return $this
     */
    public function details(Request $request){

        try {

            // 获取id
            if(!$id = (int)$request -> get('type_id')) abort(404);

            // 获取该广告集合
            $ads = AdvertisingRotation::findOrFail($id);

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
            return view('/admin/channel_ads/details',['ads'=>$ads,'data'=>$data]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 添加频道广告
     * @return $this
     */
    public function add(){

        // 广告类型
        $ads_type = [0=>'视频',1=>'影集',2=>'话题',3=>'活动',4=>'网页'];

        // 跳转地址
        return view('admin/channel_ads/create')->with(['ads_type'=>$ads_type]);
    }

    /**
     *   保存提交的频道广告
     */
    public function insert(Request $request)
    {

        // 获取所有输入的
        $input = $request -> all();

        $number = (int)$request->get('number');
        $icon = $request->file('topic-icon');

        $time = getTime();

        // 判断广告类型
        if(!in_array($input['type'],[0,1,2,3,4])) return back();

        // 初始化,默认值为4，网页
        $url = '';

        // 核实编号 也就是 type_id
        switch($input['type']){
            case 0:
            case 1:
                $data = Tweet::find($number);
                break;
            case 2:
                $data = Topic::findOrFail($number);
                break;
            case 3:
                $data = Activity::find((int)$number);
                break;
            default:
                $regex_url = regex_url($number);    // 全局函数，URL匹配
        }

        // 判断是否存在或合规
        if(in_array($input['type'],[0,1,2,3])) {

            // 如果相关数据不存在，则返回相关错误信息
            if(!$data) {

                \Session::flash('type','未找打相关数据');
                return back()->withInput();
            }
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

        // 判断时间是否都存在
        if(!$input['from_date'] || !$input['from_time']) return back()->with(['error'=>'时间不允许为空']);

        # 有效期
        // 起始时间
        $from_time = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['from_date'] . ' ' . $input['from_time']));

        // 终止时间
        $end_time = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['end_date'] . ' ' . $input['end_time']));

        DB::beginTransaction();

        // 存储数据
        $ads = AdvertisingRotation::create([
            'user_id'           => session('admin')->id,
            'name'              => post_check($input['name']),
            'from_time'         => $from_time,
            'end_time'          => $end_time,
            'type'              => $input['type'],
            'type_id'           => $type_id,
            'url'               => $url,
            'time_add'          => $time,
            'time_update'       => $time,
        ]);

        // 上传文件，并重新命名
        $result = CloudStorage::putFile(
            'advertising/' . $ads->id . '/' . getTime() . $rand.'_'.$size.'_.'.$icon->getClientOriginalExtension(),
            $icon);

        // 判断是否上传成功，并提交
        if($result[1] !== null){
            DB::rollBack();
        } else {
            $ads->image = $result[0]['key'];
            $ads->save();
            DB::commit();
        }

        return redirect('/admin/advertisement/channel/details?type_id=' . $ads->id);
    }

    /**
     * 编辑频道广告
     * @return $this
     */
    public function update(Request $request){
        try {

            // 获取id及要修改的状态
            $id = (int)$request -> get('id');
            $active = (int)$request -> get('active');

            // 判断是否为数字
            if(($active !== 1 && $active !== 2)) abort(404);

            // 获取该广告集合
            $ads = AdvertisingRotation::findOrFail($id);

            // 修改广告的状态
            $ads -> active = $active;

            // 保存
            $ads -> save();

            // 返回数据
            return redirect('admin/advertisement/channel/index');

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

}
