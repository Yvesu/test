<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelTopic;
use App\Models\HotSearch;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\TweetsPush;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Validator;
use ImageProcess;
use CloudStorage;
use DB;
class PushController extends Controller
{
    private $paginate = 20;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 获取状态信息
        $active = $request->get('active',1);

        // 判断状态信息
        if(!in_array($active,[0,1,2])) return back();

        // 获取热词信息
        $pushes = TweetsPush::where('active',$active)->orderBy('date','desc')->paginate($this->paginate);

        // 返回数据
        return view('admin/content/push/index',['pushes'=>$pushes]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/content/push/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 获取所有提交的数据
        $input = $request->all();

        // 匹配日期
        if($input['push_date'] < date('Ymd') || !regex_date($input['push_date']) || !is_numeric($input['number'])) return back();

        // 查询是否已经推送过
        $tweets_push = TweetsPush::where('tweet_id',$input['number'])->first();

        // 查询推送条数是否已经到达20条
        $tweets_count = TweetsPush::where('date',$input['push_date'])->count();

        // 不能重复推送
        if($tweets_push || $tweets_count>=20) return back();

        // 存入数据库
        $push = TweetsPush::create([
            'tweet_id'      => $input['number'],
            'date'          => $input['push_date'],
            'time_add'      => getTime(),
            'time_update'   => getTime()
        ]);

        // 跳转到详情页
        if($push) return redirect('admin/content/push?active=0');

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
//            $search = TweetsPush::findOrFail($id);
//            return view('admin/content/push/edit')
//                ->with('search',$search);
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
            // 获取该热词的集合
            $search = TweetsPush::findOrFail($id);

            if($request->has('active')) {

                // 获取active
                $active = $request->get('active');

                // 判断active
                if(!in_array($active,[0,1,2])) return back();

                $search->active = $active;
            }else{

//                // 获取提交信息
//                $input = $request -> all();
//
//                // 判断热度
//                if(!is_numeric($input['sort']) || is_null(trim($input['hot_word']))) return back();
//
//                // 判断是否有变动
//                if($search->hot_word == trim($input['hot_word']) && $search->sort == $input['sort']) return back();
//
//                // 修改集合
//                $search->hot_word = trim($input['hot_word']);
//                $search->sort = $input['sort'];
            }

            // 保存集合
            $search->save();

            // 跳转到详情页
            return redirect('admin/content/push/');

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

}
