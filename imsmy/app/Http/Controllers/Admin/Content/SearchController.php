<?php

namespace App\Http\Controllers\Admin\Content;

use App\Models\Channel;
use App\Models\HotSearch;
use App\Http\Controllers\Admin\BaseSessionController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;

class SearchController extends BaseSessionController
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
        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = $request -> get('search','');

        // 是否审批通过
        $active = (int)$request->get('active',1) === 1 ? 1 : 0;

        // 是否为官方
        $official = (int)$request->get('official') === 1 ? 1 : 0;

        // 获取话题集合
        $hots = HotSearch::where('active',$active);

        // 是否为搜索
        if($search){

            // 条件
            switch($condition){
                case 1:
                    $hots = $hots->where('hot_word','like','%'.$search.'%');
                    break;
                case 2:
                    $hots = $hots->where('sort','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }
        }

        // 筛选集合
        $hots = $hots -> orderBy('sort','DESC') -> paginate((int)$request->input('num',20));

        // 搜索条件
        $cond = [1=>'名称',2=>'热度'];

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$request->input('num',20),
            'search'=>$search,
            'active'=>$active,
        ];

        // 返回视图
        return view('admin/content/search/index',['hots'=>$hots,'request'=>$res,'condition'=>$cond]);
    }

//    public function index(Request $request)
//    {
//        // 获取状态信息
//        $active = $request->get('active',1);
//
//        // 判断状态信息
//        if(!in_array($active,[0,1,2])) return back();
//
//        // 获取热词信息
//        $hots = HotSearch::where('active',$active)->orderBy('sort','desc')->paginate($this->paginate);
//
//        // 返回数据
//        return view('admin/content/search/index')
//            ->with('hots',$hots);
//    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/content/search/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = post_check(trim($request->get('name')));

        if(strlen($request->get('name')) > 60 || !$name) return back();

        // 判断用户是否已经存在
        if(HotSearch::where('hot_word',$name)->count()){
            \Session::flash('hot_word', '"'.$name.'"'.trans('common.has_been_existed'));
            return redirect('/admin/content/search/create')->withInput();
        }

        $search = HotSearch::create([
            'hot_word'      => $name,
            'time_add'      => getTime(),
            'time_update'   => getTime()
        ]);

        // 跳转到详情页
        if($search) return redirect('admin/content/search?active=0');

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
            $search = HotSearch::findOrFail($id);
            return view('admin/content/search/edit')
                ->with('search',$search);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function recommendChannel($id)
    {
        try {
            $topic = Topic::with('hasManyChannel')->findOrFail($id);
            $channels = Channel::active()->get();
            return view('admin/content/topic/recommend_channel')
                ->with('topic', $topic)
                ->with('channels', $channels);
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
            $search = HotSearch::findOrFail($id);

            if($request->has('active')) {

                // 获取active
                $active = $request->get('active');

                // 判断active
                if(!in_array($active,[0,1,2])) return back();

                $search->active = $active;
            }else{

                // 获取提交信息
                $input = $request -> all();

                // 判断热度
                if(!is_numeric($input['sort']) || is_null(trim($input['hot_word']))) return back();

                // 判断是否有变动
                if($search->hot_word == trim($input['hot_word']) && $search->sort == $input['sort']) return back();

                // 修改集合
                $search->hot_word = trim($input['hot_word']);
                $search->sort = $input['sort'];
            }



            // 保存集合
            $search->save();

            // 跳转到详情页
            return redirect('admin/content/search/');

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function dateToTime($date,$time,$timezone)
    {
        return Carbon::createFromTimestampUTC(
            strtotime(Carbon::createFromFormat('Y-m-d H:i',$date . ' ' . $time)) + $timezone * 60
        );
    }
}
