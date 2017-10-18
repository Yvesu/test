<?php

namespace App\Http\Controllers\Admin\Content;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\ActivityManageLog;
use App\Models\Activity;
//use App\Models\Channel;
use App\Models\Tweet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use Validator;
use ImageProcess;
use CloudStorage;
use DB;

class ActivityController extends BaseSessionController
{
    private $paginate = 10;

    /**
     * 首页列表
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
        $active = (int)$request->get('active',1);

        // 判断状态
        switch($active){
            // 竞赛中
            case 1:
                $activity = Activity::active()->ofExpires();
                break;
            // 待审批
            case 2:
                $activity = Activity::whereActive(0);
                break;
            // 已完成
            case 3:
                $activity = Activity::active()->ofOver();
                break;
            // 停用
            case 0:
                $activity = Activity::whereActive(2);
                break;
        }

        // 是否为搜索
        if($search){

            // 条件
            switch($condition){
                case 1:
                    $activity = $activity->where('id','like',$search);
                    break;
                case 2:
                    $activity = $activity->where('comment','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }
        }

        // 筛选集合
        $activity = $activity -> orderBy('id','DESC') -> paginate((int)$request->input('num',10));

        // 搜索条件
        $cond = [1=>'ID',2=>'内容'];

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$request->input('num',10),
            'search'=>$search,
            'active'=>$active,
        ];

        // 返回视图
        return view('admin/content/activity/index',['topics'=>$activity,'request'=>$res,'condition'=>$cond]);

    }

    /**
     * 详情页
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // 基本信息
            $data = Activity::findOrFail($id);

            return view('admin/content/activity/show',[
                'data'=>$data
            ]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * 赛事下的动态列表
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function tweets($id,Request $request)
    {
        try{
            // 搜索条件
            $condition = (int)$request -> get('condition','');
            $search = post_check($request -> get('search',''));
            $activity_id = (int)$id;

            // 获取动态
            $datas = Tweet::with('hasOneContent')
                -> whereHas('belongsToActivityTweet',function($query)use($activity_id){
                    $query -> where('activity_id',$activity_id);
                })->orderBy('like_count','DESC');

            // 排名
            $rank = $datas->pluck('id');

            // 是否为搜索
            if($search){

                // 条件
                switch($condition){
                    // id
                    case 1:
                        $datas = $datas->where('id','like','%'.(int)$search.'%');
                        break;
                    // 内容
                    case 2:
                        $datas = $datas->whereHas('hasOneContent',function($q)use($search){
                            $q -> where('content','like','%'.$search.'%');
                        });
                        break;
                    default:
                        return back();
                }
            }

            // 获取集合
            $data = $datas -> paginate((int)$request->input('num',$this->paginate));

            // 搜索条件
            $cond = [1=>'ID',2=>'内容'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',10),
                'search'=>$search,
            ];

            return view('admin/content/activity/tweets')
                ->with([
                    'data'=>$data,
                    'request'=>$res,
                    'condition'=>$cond,
                    'id'=>$activity_id,
                    'rank'=>array_flip($rank->all())
                ]);

        }catch(\Exception $e){

            // 404报错
            abort(404);
        }
    }

    /**
     * 更新操作 只修改状态
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $topic = Activity::findOrFail($id);

            // 获取要修改的状态
            $active = (int)$request->get('active') === 1 ? 1 : 2;

            // 修改状态
            $topic->active = $active;

            // 写入日志 管理日志
            ActivityManageLog::create([
                'admin_id'  => Auth::guard('web')->user() -> id,
                'data_id'   => $id,
                'active'    => $active,
                'time_add'  => getTime()
            ]);

            $topic->save();

            return redirect('admin/content/activity/' . $id);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/content/activity/create');
    }

    /**
     * 暂时停止后台发布赛事
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function insert(Request $request)
    {
        try {

            // 验证登录信息
            $user = Auth::guard('web')->user();

            $comment = $request->get('comment');
            $icon = $request->file('topic-icon');
//            $color = $request -> get('options');

            if(!$bonus = (int)$request->get('gold')) return back();

            // 获取绑定动态的编号
//            $numbers = explode(',',trim(trim($request -> get('tweet_number')),','));

            // 判断是否为数字
//            foreach($numbers as $number){
//
//                if(!(int)$number) return back();
//            }

            // 获取动态集合
//            $tweet = Tweet::whereIn('id',$numbers)->active()->get();

            // 有动态不存在,将返回
//            if($tweet->count() != count($numbers)) {
//                \Session::flash('tweet', '动态信息有误');
//                return redirect('/admin/content/activity/create')->withInput();
//            }

            // 获取图片尺寸
            if (isset($icon)) {
                $size = getimagesize($icon)[0] . '*' . getimagesize($icon)[1];
            }

            // 获取随机数
            $rand = mt_rand(10000,99999);

            // 判断是否存在该活动，如果已经存在，返回存在信息
//            if(Activity::where('name',$name)->count()){
//                \Session::flash('name', '"'.$name.'"'.trans('common.has_been_existed'));
//                return redirect('/admin/content/activity/create')->withInput();
//            }

//            $input = array('chinese' => $name);
//            $rules = array(
//                'chinese' => 'chinese'
//            );
//            $validator = Validator::make($input, $rules);
//            if ($validator->fails()) {
//                return array_flatten($validator->getMessageBag()->toArray());
//            }
            DB::beginTransaction();
            $activity = Activity::create([
//                'name'      => $name,
                'user_id'      => $user->user_id,
                'bonus'      => $bonus,
                'comment'   => empty($comment) ? null : $comment,
                'official'  => 1,
//                'color' => $color,
            ]);

            if (isset($icon)) {
                $result = CloudStorage::putFile(
                    'activity/' . $activity->id . '/' . getTime() . $rand.'_'.$size.'_.'.$icon->getClientOriginalExtension(),
                    $icon);
            } else {
                $img = ImageProcess::text2Image(mb_substr($comment,0,1));
                $data = $img->encode('data-url');
                $result = CloudStorage::put(
                    'activity/' . $activity->id . '/' . time() . str_random() . '.png',
                    $data->encode());
            }

//            # 视频简介
//            // 获取视频截图
//            $screen_shot = $request->file('video-icon');
//
//            // 如果有视频上传
//            if(isset($screen_shot) && $request->get('key')){
//
//                // 获取随机数
//                $rand = mt_rand(1000000,9999999);
//
//                // 获取上传截图的宽高
//                $shot_width_height = getimagesize($screen_shot)[0].'*'.getimagesize($screen_shot)[1];
//
//                // 图片重命名，第一个参数：上传到七牛后保存的文件名，第二个参数：要上传文件的本地路径
//                $video_shot = CloudStorage::putFile(
//                    'activity/' . $activity->id . '/' . time() . $rand.'_'.$shot_width_height.'_.'.$screen_shot->getClientOriginalExtension(),
//                    $screen_shot
//                );
//
//                // 视频重命名
//                $new_key = 'activity/' . $activity->id . '/' . getTime().mt_rand(100000,999999).'.'.pathinfo($request->get('key'),PATHINFO_EXTENSION);
//
//                CloudStorage::rename($request->get('key'), $new_key);
//
//                ActivityExtension::create([
//                    'activity_id'      => $activity->id,
//                    'video'         => $new_key,
//                    'screen_shot'   => $video_shot[0]['key']
//                ]);
//            }

            // 保存数据
//            foreach($numbers as $number){
//
//                // 将动态存入 tweet_activity 表
//                TweetActivity::create([
//                    'tweet_id'  => $number,
//                    'activity_id'  => $activity->id
//                ]);
//            }

            // 将参与活动的用户id存入 activity_user 表
//            TweetActivity::create([
//                'user_id'  => $user->user_id,
//                'activity_id' => $activity->id
//            ]);

            // 存入 statistics_topic 表 TODO 需要修改表
//            StatisticsActivity::create([
//                'activity_id'      => $activity->id,
//                'time_add'      => getTime(),
//                'time_update'   => getTime(),
//            ]);

            if($result[1] !== null){
                DB::rollBack();
            } else {
//                $activity->hash_icon = $result[0]['hash'];
                $activity->icon = $result[0]['key'];
                $activity->save();
                DB::commit();
            }

            return redirect('/admin/content/activity/' . $activity->id);

            } catch (ModelNotFoundException $e) {
                abort(404);
        }
    }

    /**
     * 编辑页面，停用
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function edit($id)
//    {
//        try {
//            $topic = Activity::findOrFail($id);
//            return view('admin/content/activity/edit')
//                ->with('topic',$topic);
//        } catch (ModelNotFoundException $e) {
//            abort(404);
//        }
//    }

    /**
     * 推荐频道，停用
     * @param $id
     * @return mixed
     */
    public function recommendChannel($id)
    {
        try {
            $topic = Activity::findOrFail($id);
//            $channels = Channel::active()->get();
            return view('admin/content/activity/recommend_channel')
                ->with('topic', $topic);
//                ->with('channels', $channels);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 推荐设置
     * @param $id
     * @return mixed
     */
    public function recommend($id, Request $request)
    {
        try {
//            $user = Auth::guard('web')->user();

            $activity = Activity::findOrFail($id);

            $recommend_start = strtotime($request->get('recommend_start'));
            $recommend_expires = strtotime($request->get('recommend_expires'));

            // 推荐赛事的开始与结束日期不能大于赛事的结束日期
            if(!$recommend_start && !$recommend_expires)
                return back();

            // 推荐赛事的开始与结束日期不能大于赛事的结束日期
            if($activity -> expires <= $recommend_start || $activity -> expires <= $recommend_expires)
                return back();

            $activity -> update([
                'recommend_start'   => $recommend_start,
                'recommend_expires'   => $recommend_expires,
                'time_update'   => getTime(),
            ]);

            return redirect('admin/content/activity/'.$activity->id);
        } catch (ModelNotFoundException $e) {
            return back();
        }
    }

    /**
     * 更新操作 包括修改内容，暂时停用
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function update(Request $request, $id)
//    {
//        try {
//
//            $topic = Activity::findOrFail($id);
//
//            if($request->has('comment')) {
//                $comment = $request->get('comment');
//                $icon = $request->file('topic-icon');
//                $topic->comment = empty($comment) ? null : $comment;
//
//                if (isset($icon)) {
//                    $result = CloudStorage::putFile(
//                        'topic/' . $topic->id . '/' . time() . $icon->getClientOriginalName(),
//                        $icon);
//                    if($result[1] === null){
//                        CloudStorage::delete($topic->icon);
//                        $topic->hash_icon = $result[0]['hash'];
//                        $topic->icon = $result[0]['key'];
//                    }
//                }
//
//            } else if($request->has('active')) {
//
//                // 获取要修改的状态
//                $active = (int)$request->get('active') === 1 ? 1 : 2;
//
//                // 修改状态
//                $topic->active = $active;
//
//                // 写入日志 管理日志
//                ActivityManageLog::create([
//                    'admin_id'  => Auth::guard('web')->user() -> id,
//                    'data_id'   => $id,
//                    'active'    => $active,
//                    'time_add'  => getTime()
//                ]);
//
//            } else if($request->has('_time')) {
//
//                $input = $request->all();
//                if (strtotime($topic->updated_at) - $input['_time']) {
//                    return redirect('admin/content/activity/' . $id);
//                }
//                if (isset($input['recommend_check']) && $input['recommend_check'] === 'on') {
//                    $topic->recommend_expires = $this->dateToTime($input['recommend_date'],$input['recommend_time'],$input['_timezone']);
//                }
//
//                ChannelTopic::ofTopicID($id)->delete();
//                if (isset($input['channels']) && ! empty($input['channels'])) {
//                    $data = [];
//                    $time = new Carbon();
//                    foreach ($input['channels'] as $channel) {
//                        $data[] = [
//                            'channel_id' => $channel,
//                            'topic_id'   => $id,
//                            'updated_at' => $time,
//                            'created_at' => $time
//                        ];
//                    }
//                    DB::table('channel_topic')->insert($data);
//                }
//            }
//
//            $topic->save();
//
//            return redirect('admin/content/activity/' . $id);
//
//        } catch (ModelNotFoundException $e) {
//            abort(404);
//        }
//    }

    // 停用
//    public function dateToTime($date,$time,$timezone)
//    {
//        return Carbon::createFromTimestampUTC(
//            strtotime(Carbon::createFromFormat('Y-m-d H:i',$date . ' ' . $time)) + $timezone * 60
//        );
//    }
}
