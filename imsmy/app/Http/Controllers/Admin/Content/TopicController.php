<?php

namespace App\Http\Controllers\Admin\Content;

use App\Models\Channel;
use App\Models\ChannelTopic;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Topic;
use App\Models\TopicManageLog;
use App\Models\TopicExtension;
use App\Models\TweetTopic;
use App\Models\TopicUser;
use App\Models\Tweet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Routing\Controller;
use Validator;
use ImageProcess;
use CloudStorage;
use DB;
use Auth;

class TopicController extends BaseSessionController
{
    private $paginate = 10;

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
        $topics = Topic::where('active',$active);

        // 如果设置了 $official
        if($request->get('official')) $topics = $topics -> where('official',$official);

        // 是否为搜索
        if($search){

            // 条件
            switch($condition){
                case 1:
                    $topics = $topics->where('name','like','%'.$search.'%');
                    break;
                case 2:
                    $topics = $topics->where('comment','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }
        }

        // 筛选集合
        $topics = $topics -> orderBy('id','DESC') -> paginate((int)$request->input('num',10));

        // 搜索条件
        $cond = [1=>'名称',2=>'内容'];

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$request->input('num',10),
            'search'=>$search,
            'active'=>$active,
            'official'=>$request->get('official')
        ];

        // 返回视图
        return view('admin/content/topic/index',['topics'=>$topics,'request'=>$res,'condition'=>$cond]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/content/topic/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // 验证登录信息
        $user = Auth::guard('web')->user();

        $name = $request->get('name');
        $comment = $request->get('comment');
        $icon = $request->file('topic-icon');
        $color = $request -> get('options');

        // 获取绑定动态的编号
        $numbers = explode(',',trim($request -> get('tweet_number')));

        // 判断是否为数字
        foreach($numbers as $number){

            if(!is_numeric($number)) return back();
        }

        // 判断是否存在该动态
        $tweet = Tweet::whereIn('id',$numbers)->active()->get();

        // 动态部分不存在,动态已经在话题下，都将返回
        if($tweet->count() != count($numbers)) return back();

        // 获取动态内的发布者id
        $user_ids = $tweet -> pluck('user_id') -> unique();

        // 获取图片尺寸
        $size = getimagesize($icon)[0].'*'.getimagesize($icon)[1];

        // 获取随机数
        $rand = mt_rand(1000000,9999999);

        if(Topic::where('name',$name)->count()){
            \Session::flash('name', '"'.$name.'"'.trans('common.has_been_existed'));
            return redirect('/admin/content/topic/create')->withInput();
        }

        $input = array('chinese' => $name);
        $rules = array(
            'chinese' => 'chinese'
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return array_flatten($validator->getMessageBag()->toArray());
        }
        DB::beginTransaction();

        $topic = Topic::create([
            'name'      => $name,
            'comment'   => empty($comment) ? null : $comment,
            'official'  => 1,
            'size'       => $size,
            'color'      => $color,
            'user_id'    =>$user->user_id,
            'work_count' => $tweet -> count(),
            'users_count'=> $user_ids->count(),
        ]);
        if (isset($icon)) {
            $result = CloudStorage::putFile(
                'topic/' . $topic->id . '/' . getTime() . $rand.'_'.$size.'_.'.$icon->getClientOriginalExtension(),
                $icon);
        } else {
            $img = ImageProcess::text2Image(mb_substr($name,0,1));
            $data = $img->encode('data-url');
            $result = CloudStorage::put(
                'topic/' . $topic->id . '/' . time() . str_random() . '.png',
                $data->encode());
        }

        # 图片封面
        // 获取封面
        $photo = $request->file('cover-icon');

        // 如果有封面上传
        if(isset($photo)){

            // 获取随机数
            $rand = mt_rand(1000000,9999999);

            // 获取上传截图的宽高
            $shot_width_height = getimagesize($photo)[0].'*'.getimagesize($photo)[1];

            // 图片重命名，第一个参数：上传到七牛后保存的文件名，第二个参数：要上传文件的本地路径
            $photo_shot = CloudStorage::putFile(
                'topic/' . $topic->id . '/' . time() . $rand.'_'.$shot_width_height.'_.'.$photo->getClientOriginalExtension(),
                $photo
            );

            // 创建
            TopicExtension::create([
                'topic_id'      => $topic->id,
                'photo'   => $photo_shot[0]['key']
            ]);
        }else{

            # 视频简介
            // 获取视频截图
            $screen_shot = $request->file('video-icon');

            // 如果有视频上传
            if(isset($screen_shot) && $request->get('key')){

                // 获取随机数
                $rand = mt_rand(1000000,9999999);

                // 获取上传截图的宽高
                $shot_width_height = getimagesize($screen_shot)[0].'*'.getimagesize($screen_shot)[1];

                // 图片重命名，第一个参数：上传到七牛后保存的文件名，第二个参数：要上传文件的本地路径
                $video_shot = CloudStorage::putFile(
                    'topic/' . $topic->id . '/' . time() . $rand.'_'.$shot_width_height.'_.'.$screen_shot->getClientOriginalExtension(),
                    $screen_shot
                );

                // 视频重命名
                $new_key = 'topic/' . $topic->id . '/' . getTime().mt_rand(100000,999999).'.'.pathinfo($request->get('key'),PATHINFO_EXTENSION);

                CloudStorage::rename($request->get('key'), $new_key);

                TopicExtension::create([
                    'topic_id'      => $topic->id,
                    'video'         => $new_key,
                    'screen_shot'   => $video_shot[0]['key']
                ]);
            }
        }

        // 保存数据
        foreach($numbers as $number){

            // 将动态存入 tweet_topic 表
            TweetTopic::create([
                'tweet_id'  => $number,
                'topic_id'  => $topic->id
            ]);
        }

        // 将参与话题的用户id存入 topic_user 表
        foreach($user_ids as $key=>$value){

            TopicUser::create([
                'user_id'  => $value,
                'topic_id' => $topic->id
            ]);
        }

        if($result[1] !== null){
            DB::rollBack();
        } else {
            $topic->hash_icon = $result[0]['hash'];
            $topic->icon = $result[0]['key'];
            $topic->save();
            DB::commit();
        }

        return redirect('/admin/content/topic/' . $topic->id);
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
            // 话题基本信息
            $topic = Topic::findOrFail($id);

            // 获取话题视频信息
            $video = TopicExtension::where('topic_id',$id)->first();

            return view('admin/content/topic/show',['topic'=>$topic, 'video'=>$video]);

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
            $topic = Topic::findOrFail($id);
            return view('admin/content/topic/edit')
                ->with('topic',$topic);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function recommendChannel($id)
    {
        try {
            $topic = Topic::with('hasManyChannel')->findOrFail($id);
            $channels = Channel::active()->orWhere('name','热门')->get();
            $channel_topic = ChannelTopic::where('topic_id',$id)->first();
            return view('admin/content/topic/recommend_channel',['channel_topic'=>$channel_topic,'topic'=>$topic,'channels'=>$channels]);
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
            $topic = Topic::findOrFail($id);
            if($request->has('comment')) {
                $comment = $request->get('comment');
                $icon = $request->file('topic-icon');
                $topic->comment = empty($comment) ? null : $comment;
                if (isset($icon)) {
                    $result = CloudStorage::putFile(
                        'topic/' . $topic->id . '/' . time() . $icon->getClientOriginalName(),
                        $icon);
                    if($result[1] === null){
                        CloudStorage::delete($topic->icon);
                        $topic->hash_icon = $result[0]['hash'];
                        $topic->icon = $result[0]['key'];
                    }
                }

            } else if($request->has('active')) {

                // 获取要修改的状态
                $active = (int)$request->get('active') === 1 ? 1 : 2;

                // 修改状态
                $topic->active = $active;

                // 写入日志 管理日志
                TopicManageLog::create([
                    'admin_id'  => Auth::guard('web')->user() -> id,
                    'data_id'   => $id,
                    'active'    => $active,
                    'time_add'  => getTime()
                ]);
            } else if($request->has('_time')) {
                $input = $request->all();
                if (strtotime($topic->updated_at) - $input['_time']) {
                    return redirect('admin/content/topic/' . $id);
                }
                if (isset($input['recommend_check']) && 1 == $input['recommend_check']) {
                    // 带时区，时区需要调整
//                    $topic->recommend_expires = $this->dateToTime($input['recommend_date'],$input['recommend_time'],$input['_timezone']);
                    $topic->recommend_expires = Carbon::createFromFormat('Y-m-d H:i:s',$input['recommend_date'].' '.$input['recommend_time']);
                }

                ChannelTopic::ofTopicID($id)->delete();
                if (isset($input['channels']) && ! empty($input['channels'])) {
                    $data = [];
                    $time = new Carbon();
                    foreach ($input['channels'] as $channel) {
                        $data[] = [
                            'channel_id' => $channel,
                            'topic_id'   => $id,
                            'updated_at' => $time,
                            'created_at' => $time
                        ];
                    }
                    DB::table('channel_topic')->insert($data);
                }
            }
            $topic->save();
            return redirect('admin/content/topic/' . $id);
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
