<?php

namespace App\Http\Controllers\Admin\System;

use App\Models\TweetTrophyConfig;
use App\Http\Controllers\Admin\BaseSessionController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use CloudStorage;
use DB;
use Auth;

class TrophyController extends BaseSessionController
{
    private $paginate = 8;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 登录用户
        $user = Auth::guard('web')->user();

        // 获取集合
        $trophy = TweetTrophyConfig::orderBy('num');

        // 获取广告状态
        $style = $request->get('style',1);

        // style 0为未审批，1为正常在用，2为过期，3为屏蔽
        switch($style){
            case 0:
                $trophy -> wait();
                break;
            case 1:
                $trophy -> status();
                break;
            case 2:
                $trophy -> overdue();
                break;
            default:
                $trophy -> forbid();
        }

        // 取出相应数量
        $trophy = $trophy -> paginate($this->paginate);

        // 设置返回数组
        $res = [
            'style'=>$request->input('style',1),
            'search'=>$request->input('search',''),
        ];

        return view('admin/system_management/trophy/index',['user'=>$user,'trophy'=>$trophy,'request'=>$res]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/system_management/trophy/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 获取所有输入的
        $input = $request -> all();

        $icon = $request->file('topic-icon');

        // 判断金币数量合法性
        if(!is_numeric($input['gold_num'])) return back();

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
        $trophy = TweetTrophyConfig::create([
            'name'                  => post_check($input['trophy_name']),
            'num'                   => $input['gold_num'],
            'time_active_start'     => $from_time,
            'time_active_end'       => $end_time,
            'time_add'              => getTime(),
            'time_update'           => getTime(),
        ]);

        // 上传文件，并重新命名
        $result = CloudStorage::putFile(
            'trophy/' . $trophy->id . '/' . getTime() . $rand.'_'.$size.'_.'.$icon->getClientOriginalExtension(),
            $icon);

        // 判断是否上传成功，并提交
        if($result[1] !== null){
            DB::rollBack();
        } else {
            $trophy->picture = $result[0]['key'];
            $trophy->save();
            DB::commit();
        }

        return redirect('/admin/system_management/trophy/' . $trophy->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $trophy = TweetTrophyConfig::findOrFail($id);
            return view('admin/system_management/trophy/show',['trophy'=>$trophy]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $trophy = TweetTrophyConfig::findOrFail($id);
            return view('admin/content/trophy/edit')
                ->with('trophy',$trophy);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $trophy = TweetTrophyConfig::findOrFail($id);
            if($request->has('active')) {
                $active = $request->get('active') == 1 ? 1 : 0;
                $trophy->status = $active;
            }
            $trophy->save();
            return redirect('admin/system_management/trophy/' . $id);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

}
