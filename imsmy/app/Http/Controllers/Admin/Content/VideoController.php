<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Activity;
use App\Models\Admin\Administrator;
use App\Models\Channel;
use App\Models\User;
use App\Models\TweetManageLog;
use App\Models\ChannelTweet;
use App\Models\Topic;
use App\Models\Tweet;
use App\Models\TopicUser;
use App\Models\TweetActivity;
use App\Models\TweetTop;
use App\Models\TweetHot;
use App\Models\TweetsPush;
use App\Models\Subscription;
use App\Models\TweetTopic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use DB;
use Auth;
use Image;

class VideoController extends BaseSessionController
{

    private $paginate = 8;
    /**
     * Display a listing of the resource.
     *  通过视频的主页
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 登录信息
        $user = Auth::guard('web')->user();

        // 获取状态值，0表示停用，1表示正常
        $active = is_null($request->get('active')) || $request->get('active') == 1  ? 1 : 0;
        $tweets = Tweet::where('active',$active)->where('type',0);
        $official = $request->get('official');

        if (is_null($official)) {
            $tweets = $tweets->paginate($this->paginate);
        } else if ($official == 1) {
            $users = Administrator::whereNotNull('user_id')->get(['user_id'])->pluck('user_id')->all();

            $tweets = $tweets->whereIn('user_id',$users)->paginate($this->paginate);
        } else {
            $users = Administrator::whereNotNull('user_id')->get(['user_id'])->pluck('user_id')->all();
            $tweets = $tweets->whereNotIn('user_id',$users)->paginate($this->paginate);
        }

        return view('admin/content/video/index')
            ->with('user',$user)
            ->with('tweets',$tweets);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/content/video/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $screen_shot = $request->file('video-icon');

            // 获取上传截图的宽高
            $shot_width_height = getimagesize($screen_shot)[0].'*'.getimagesize($screen_shot)[1];

            // 获取登录用户的信息
            $user = Auth::guard('web')->user();

            $newTweet = [
                'type'     => 0,
                // 获取绑定的user_id
                'user_id'  => $user->user_id,
                'video'    => $request->get('key'),
                'content'  => $request->get('name') == '' ? null : $request->get('name'),
            ];

            // 通过自定义函数，判断内容中是否有话题名称，如果有则返回内容中的话题名称
            $topics = $this->regexTopic($newTweet['content']);

            // 获取随机数
            $rand = mt_rand(1000000,9999999);

            DB::beginTransaction();
            $tweet = Tweet::create($newTweet);

            // 根据用户ID及话题数组数据，创建不存在的话题，并返回所有话题的ID
            $select_topics = $this->createNewTopic($user->user_id, $topics, $tweet);

            // 判断话题ids是否为空
            if(!empty($select_topics)){

                // 绑定动态与话题
                $this->createTweetTopic($tweet, $select_topics);

                // 绑定用户与话题
                $this->createUserTopic($user->user_id, $select_topics);
            }

            // 源代码，修改文件名，将尺寸加进去 搜索标签：备忘录
//            $result = CloudStorage::putFile(
//                'tweet/' . $tweet->id . '/' . time() . $screen_shot->getClientOriginalName(),
//                $screen_shot
//            );

            // 新代码 第一个参数：上传到七牛后保存的文件名，第二个参数：要上传文件的本地路径
            $result = CloudStorage::putFile(
                'tweet/' . $tweet->id . '/' . time() . $rand.'_'.$shot_width_height.'_.'.$screen_shot->getClientOriginalExtension(),
                $screen_shot
            );

            // 新名字
            $new_key = 'tweet/' . $tweet->id . '/' . getTime().mt_rand(100000,999999).'.'.pathinfo($tweet->video,PATHINFO_EXTENSION);

            CloudStorage::rename($tweet->video, $new_key);
            $tweet->screen_shot = $result[0]['key'];
            $tweet->video = $new_key;

            // 获取时长
            $duration = json_decode(file_get_contents(CloudStorage::downloadUrl($tweet->video.'?avinfo')));

            // 保存时长
            $tweet->duration = $duration->format->duration;

            $tweet->save();

            # 更新 users 表中的作品总量数据

            // 判断是否为转发或原创
            if(isset($tweet->retweet)){

                User::findOrfail($user->user_id) -> increment('retweet_count');

            }else{

                // 作品总量加1
                User::findOrfail($user->user_id) -> increment('work_count');
            }

            // 获取 subscription 表中 集合
            $subscription = Subscription::where('to',$user->user_id) -> get();

            // 遍历集合
            foreach ($subscription as $item) {

                // 将 subscription 表中 unread 批量 +1
                $item -> unread ++;

                // 保存
                $item -> save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
        return redirect('/admin/content/video');
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
            // 具体视频详情
            $video = Tweet::with('belongsToManyChannel')->where('id',$id)->where('type',0)->firstOrFail();

            // 遍历频道信息，获取名字
            foreach($video->belongsToManyChannel as $k=>$value){

                $channel_name[$value['id']] = $value['name'];
            }

            // 获取视频的所属频道
            $video -> channel_name = implode('，',$channel_name);

            // 返回数据
            return view('/admin/content/video/show',['video'=>$video]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 编辑页面
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            // 获取视频集合
            $video = Tweet::with('belongsToManyChannel','hasOneHot','hasOneTop','hasOnePush')->where('id',$id)->where('type',0)->firstOrFail();

            // 所在的话题
            $topics_ids = TweetTopic::where('tweet_id',$id)->pluck('topic_id')->all();

            $topics_in = Topic::whereIn('id',$topics_ids)->get(['id','name']);

            // 所在的活动
            $activities_ids = TweetActivity::where('tweet_id',$id)->pluck('activity_id')->all();

            $activities_in = Activity::whereIn('id',$activities_ids)->get(['id','name']);

            // 获取在用的channel数据
            $channels = Channel::active()->get(['id','name']);

            // 获取所有在用话题
            $topics = Topic::active()->get(['id','name']);

            // 获取所有在用活动
            $activities = Activity::active()->get(['id','name']);

            // 获取在用的标签数据
//            $labels = Label::active()->get();

            // 返回该视频的编辑页面
            return view('/admin/content/video/edit',[
                'video'=>$video,
                'topics_in'=>$topics_in,
                'activities_in'=>$activities_in,
                'channels'=>$channels,
                'activities'=>$activities,
                'topics'=>$topics
            ]);
//
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 更改
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id 动态id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            // 获取该id的动态情况，tweet 表
            $video = Tweet::where('id',$id)
                ->where('type',0)
                ->whereNull('original')
                ->with('belongsToManyTopic','belongsToManyActivity','belongsToManyChannel','hasOneHot','hasOneTop','hasOnePush')
                ->firstOrFail();

            if ($request->has('active')) {

                // 获取要修改的状态
                $active = $request->get('active') == 1 ? 1 : 2;

                // 修改状态
                $video->active = $active;

                // 写入日志 管理日志
                TweetManageLog::create([
                    'admin_id'  => Auth::guard('web')->user() -> id,
                    'data_id'   => $id,
                    'active'    => $active,
                    'time_add'  => getTime()
                ]);

            } else {
                // 获取所有提交的数据
                $input = $request->all();

                // 将updated_at 时间转为时间戳，如果修改期间该tweet有变动，将返回到该tweet详情页面
                if (strtotime($video->updated_at) - strtotime($input['_time'])) {
                    return redirect('admin/content/video/' . $id);
                }

                // 开启事务模式
                DB::beginTransaction();

                // 判断置顶或推荐是否打开
                if (isset($input['top_check']) || isset($input['recommend_check'])) {

                    // 获取该动态在 zx_tweet_top 表中的集合
                    $tweet_top = $video->hasOneTop;

                    // 如果不存在，则创建集合
                    if(!$tweet_top){

                        // 创建集合
                        $tweet_top = TweetTop::create([
                            'tweet_id'      => $id,
                            'time_add'      => getTime(),
                            'time_update'   => getTime(),
                        ]);
                    }
                }

                // 如果设置了置顶，格式化时间，存入集合
                if (isset($input['top_check']) && $input['top_check'] === 'on') {

                    // 更改在集合中的设置
                    $tweet_top->top_expires = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['top_date'] . ' ' . $input['top_time']));

                    // 保存集合
                    $tweet_top->save();
                }

                // 如果设置了推荐，格式化时间，存入集合
                if (isset($input['recommend_check']) && $input['recommend_check'] === 'on') {

                    // 更改在集合中的设置
                    $tweet_top->recommend_expires = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['recommend_date'] . ' ' . $input['recommend_time']));

                    // 保存集合
                    $tweet_top->save();
                }

                // 如果设置了推送，格式化时间，存入集合
                if (isset($input['push_check']) && $input['push_check'] === 'on') {

                    // 匹配日期
                    if($input['push_date'] < date('Ymd') || !regex_date($input['push_date'])) return back();

                    // 查询是否已经推送过
                    $tweets_push = $video->hasOnePush;

                    // 查询推送条数是否已经到达20条
                    $tweets_count = TweetsPush::where('date',$input['push_date'])->count();

                    // 不能重复推送
                    if($tweets_push || $tweets_count>=20) return back();

                    // 存入数据库
                    TweetsPush::create([
                        'tweet_id'      => $id,
                        'date'          => $input['push_date'],
                        'time_add'      => getTime(),
                        'time_update'   => getTime()
                    ]);
                }

                // 如果设置了热门
                if (isset($input['hot_check']) && $input['recommend_check'] === 'on') {

                    // 检测是否已经设置过热门
                    $hot = $video->hasOneHot;

                    // 如果未设置热门,创建集合
                    if(!$hot){

                        TweetHot::create([
                            'tweet_id'      => $id,
                            'time_add'      => getTime(),
                            'time_updated'   => getTime()
                        ]);
                    }
                }

                // 话题
                if (isset($input['topics']) && ! empty($input['topics'])) {

                    // 检测该动态所属话题
                    $topics = $video->belongsToManyTopic->pluck('topic_id')->all();

                    // 如果未设置热门,创建集合
                    if($input['topics'] != $topics){

                        // 删除 tweet_topic 表中的相应数据
                        TweetTopic::ofTweetID($id)->delete();

                        // 初始化数组
                        $data_topics = [];

                        // 获取当前时间
                        $time = new Carbon();

                        // 遍历提交频道数据
                        foreach ($input['topics'] as $topic) {
                            $data_topics[] = [
                                'topic_id' => $topic,
                                'tweet_id'   => $id,
                                'updated_at' => $time,
                                'created_at' => $time
                            ];
                        }
                        // 保存数据
                        DB::table('tweet_topic')->insert($data_topics);
                    }
                }

                // 活动
                if (isset($input['activities']) && ! empty($input['activities'])) {

                    // 检测该动态所属话题
                    $topics = $video->belongsToManyActivity->pluck('activity_id')->all();

                    // 如果未设置热门,创建集合
                    if($input['activities'] != $topics){

                        // 删除 tweet_topic 表中的相应数据
                        TweetActivity::ofTweetID($id)->delete();

                        // 初始化数组
                        $data_activity = [];

                        // 获取当前时间
                        $time = getTime();

                        // 遍历提交频道数据
                        foreach ($input['activities'] as $activity) {
                            $data_activity[] = [
                                'activity_id'   => $activity,
                                'tweet_id'      => $id,
                                'time_add'      => $time,
                                'time_update'   => $time
                            ];
                        }
                        // 保存数据
                        DB::table('tweet_activity')->insert($data_activity);
                    }
                }

                // 如果设置了标签，并且标签有效,暂时关闭标签
//                if (! empty($input['label_name']) && Label::where('id',$input['label_name'])->active()->count()) {
//
//                    // 将标签存入集合
//                    $video->label_id = $input['label_name'];
//
//                    // 格式化时间，存入集合
//                    $video->label_expires = $this->dateToTime($input['label_date'],$input['label_time'],$input['_timezone']);
//                }

                // 频道如果设置，存入 channel_tweet 表
                if (isset($input['channels']) && ! empty($input['channels'])) {

                    // 获取 channel_tweet 表中的相应数据
                    $channels = ChannelTweet::ofTweetID($id)->pluck('channel_id')->all();

                    // 频道数据 有改变
                    if($input['channels'] != $channels){

                        // 删除 channel_tweet 表中的相应数据
                        ChannelTweet::ofTweetID($id)->delete();

                        // 初始化数组
                        $data_channels = [];

                        // 获取当前时间
                        $time = new Carbon();

                        // 遍历提交频道数据
                        foreach ($input['channels'] as $channel) {
                            $data_channels[] = [
                                'channel_id' => $channel,
                                'tweet_id'   => $id,
                                'updated_at' => $time,
                                'created_at' => $time
                            ];
                        }
                        // 保存数据
                        DB::table('channel_tweet')->insert($data_channels);
                    }
                }
            }
            // 保存
            $video->save();

            // 事务提交
            DB::commit();

            // 重定向到视频详情页
            return redirect('admin/content/video/' . $id);

        } catch (ModelNotFoundException $e) {

            // 事务回滚
            DB::rollBack();

            // 跳出404页面
            abort(404);
        }
    }

    /**
     * 旧版
     */
//    public function update(Request $request, $id)
//    {
//        try {
//            // 获取该id的动态情况，tweet 表
//            $video = Tweet::where('id',$id)->where('type',0)->whereNull('original')->firstOrFail();
//
//            if ($request->has('active')) {
//                $active = $request->get('active') == 1 ? 1 : 0;
//                $video->active = $active;
//            } else {
//                // 获取所有提交的数据
//                $input = $request->all();
//
//                // 将updated_at 时间转为时间戳，如果修改期间该tweet有变动，将返回到该tweet详情页面
//                if (strtotime($video->updated_at) - $input['_time']) {
//                    return redirect('admin/content/video/' . $id);
//                }
//
//                // 如果设置了置顶，格式化时间，存入集合
//                if (isset($input['top_check']) && $input['top_check'] === 'on') {
//                    $video->top_expires = $this->dateToTime($input['top_date'],$input['top_time'],$input['_timezone']);
//                }
//
//                // 如果设置了推荐，格式化时间，存入集合
//                if (isset($input['recommend_check']) && $input['recommend_check'] === 'on') {
//                    $video->recommend_expires = $this->dateToTime($input['recommend_date'],$input['recommend_time'],$input['_timezone']);
//                }
//
//                // 如果设置了标签，并且标签有效
//                if (! empty($input['label_name']) && Label::where('id',$input['label_name'])->active()->count()) {
//
//                    // 将标签存入集合
//                    $video->label_id = $input['label_name'];
//
//                    // 格式化时间，存入集合
//                    $video->label_expires = $this->dateToTime($input['label_date'],$input['label_time'],$input['_timezone']);
//                }
//
//                // 删除 channel_tweet 表中的相应数据
//                ChannelTweet::ofTweetID($id)->delete();
//
//                // 频道如果设置，存入 channel_tweet 表
//                if (isset($input['channels']) && ! empty($input['channels'])) {
//                    $data = [];
//                    $time = new Carbon();
//                    foreach ($input['channels'] as $channel) {
//                        $data[] = [
//                            'channel_id' => $channel,
//                            'tweet_id'   => $id,
//                            'updated_at' => $time,
//                            'created_at' => $time
//                        ];
//                    }
//                    DB::table('channel_tweet')->insert($data);
//                }
//            }
//            $video->save();
//            return redirect('admin/content/video/' . $id);
//        } catch (ModelNotFoundException $e) {
//            abort(404);
//        }
//    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function dateToTime($date,$time,$timezone)
    {
        return Carbon::createFromTimestampUTC(
            strtotime(Carbon::createFromFormat('Y-m-d H:i',$date . ' ' . $time)) + $timezone * 60
        );
    }

    /**
     * 验证动态的content是否包括话题,并返回话题名称
     * @param $content
     * @return array|null
     */
    protected function regexTopic($content)
    {
        // 使用自定义函数，返回匹配成功的次数及匹配的结果集所组成的数组
        $match_topic = regex_topic($content);

        // 判断成功匹配的次数
        if ($match_topic[0]) {

            // 返回去除#符号后的话题名称
            return array_unique(array_map(function($value){
                return str_replace('#','',$value);
            },$match_topic[1][0]));

        }
        return null;
    }

    /**
     * 根据用户ID及话题数组数据，创建不存在的话题，并返回所有话题的ID
     * @param $id
     * @param $topics
     * @param $tweet
     * @return mixed
     */
    protected function createNewTopic($id, $topics, $tweet)
    {
        if (empty($topics)) {
            return null;
        }

        // 查看是否有已经存在的话题集合
        $topics_exists = Topic::whereIn('name',$topics)->get();

        // 取出话题集合中的name值
        $select_topics = $topics_exists ->pluck('name')->all();

        // 初始化 topics 的id接收数组
        $topics_ids = [];

        // 判断是否有已存在的话题
        if($topics_exists->count()) {

            // 遍历数组
            foreach($topics_exists as $value){

                // 将相关话题的作品总数 加1
                Topic::findOrFail($value -> id) -> increment('work_count');

                $topics_ids[] = $value -> id;
            }
        }

        // 获取两个数组的差集
        $diff = array_diff($topics, $select_topics);

        $time = new Carbon();

        // 遍历存入表中
        foreach ($diff as $item) {

            $topic = Topic::create([
                'name'          => $item,
                'user_id'       => $id,
                'comment'       => $tweet->content,
                'work_count'    => 1,
                'created_at'    => $time,
                'updated_at'    => $time
            ]);

            $topics_ids[] = $topic -> id;
        }

        return $topics_ids;
    }

    /**
     * 绑定动态与话题
     * @param $tweet
     * @param $topics
     * @return false;
     */
    protected function createTweetTopic($tweet,$topics)
    {
        if ($topics === null) {
            return false;
        }
        $tweet_topics = [];
        foreach ($topics as $topic) {
            $tweet_topics[] = [
                'tweet_id'      => $tweet->id,
                'topic_id'      => $topic,
                'created_at'    => new Carbon(),
                'updated_at'    => new Carbon()
            ];
        }
        DB::table('tweet_topic')->insert($tweet_topics);
    }

    /**
     * 绑定用户与话题
     * @param $user_id 用户id
     * @param $topics  话题id数组
     * @return false;
     */
    protected function createUserTopic($user_id,$topics)
    {
        if ($topics === null) {
            return false;
        }
        $user_topics = [];
        foreach ($topics as $topic) {

            // 判断该用户是否已经参与该话题，如果未参与则存入数据库
            if(!TopicUser::where('topic_id',$topic)->where('user_id',$user_id)->get()->first()){

                // 将信息存入数组中
                $user_topics[] = [
                    'user_id'      => $user_id,
                    'topic_id'      => $topic,
                    'created_at'    => new Carbon(),
                    'updated_at'    => new Carbon()
                ];

                // 话题参与人数加1
                Topic::findOrFail($topic) -> increment('users_count');
            }
        }
        // 如果数组不为空，则存入数据库
        if(count($user_topics)) DB::table('topic_user')->insert($user_topics);
    }
}
