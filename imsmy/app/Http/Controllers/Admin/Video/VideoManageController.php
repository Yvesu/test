<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Channel;
use App\Models\TweetManageLog;
use App\Models\TweetExamineAmount;
use App\Models\ChannelTweet;
use App\Models\TweetTempCheck;
use App\Models\Admin\Administrator;
use App\Models\TweetTopic;
use App\Models\TweetActivity;
use App\Models\TweetsPush;
use App\Models\TweetHot;
use App\Models\Subscription;
use App\Models\Activity;
use App\Models\Tweet;
use App\Models\TweetTop;
use App\Models\Topic;
use App\Models\TopicUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use Auth;
use DB;
use Image;

/**
 * 视频管理类 增删改查
 * Class VideoCheckController
 * @package App\Http\Controllers\Admin\Video
 */
class VideoManageController extends BaseSessionController
{

    protected $paginate = 10;

    /**
     * 审批通过视频信息
     */
    public function index(Request $request)
    {
        try{
            // 搜索条件
            $condition = (int)$request -> get('condition','');
            $search = post_check($request -> get('search',''));

            // 获取动态
            $datas = Tweet::with('hasManyChannel','hasOneContent')->active()->whereNull('original')->where('type',0);

            // 是否为搜索
            if($search){

                // 条件
                switch($condition){
                    // id
                    case 1:
                        $datas = $datas->where('id','like','%'.(int)$search.'%');
                        break;
                    // 频道
                    case 2:
                        $channel_id = Channel::where('name','like','%'.$search.'%')->firstOrFail()->id;
                        $datas = $datas->whereHas('hasManyChannelTweet',function($q)use($channel_id){
                            $q -> where('channel_id',$channel_id);
                        });
                        break;
                    // 内容
                    case 3:
                        $datas = $datas->whereHas('hasOneContent',function($q)use($search){
                            $q -> where('content','like','%'.$search.'%');
                        });
                        break;
                    default:
                        return back();
                }
            }

            // 获取集合
            $datas = $datas -> paginate((int)$request->input('num',$this->paginate));

            // 处理频道信息
            $datas -> each(function($data){

                // 遍历动态所属频道
                foreach($data -> hasManyChannel as $value){

                    $hasManyChannel[] = $value -> name;
                }

                // 将动态所属频道名称拼接成字符串
                $data -> channel_name = implode(',',$hasManyChannel);
            });

            // 搜索条件
            $cond = [1=>'ID',2=>'频道',3=>'内容'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',10),
                'search'=>$search,
            ];

            return view('admin/video/index')
                ->with([
                    'datas'=>$datas,
                    'request'=>$res,
                    'condition'=>$cond,
                ]);

        }catch(\Exception $e){

            // 事务回滚
            DB::rollBack();

            // 404报错
            abort(404);
        }
    }

    /**
     * 视频待审批页面
     */
    public function check(Request $request)
    {
        try{
            // 搜索条件
            $condition = (int)$request -> get('condition','');
            $search = post_check($request -> get('search',''));

            // 获取管理员id
            $admin_id = session('admin')->id;

            // 获取动态临时表中的动态id，不包含id为自己的动态id
            $tweet_ids = TweetTempCheck::where('admin_id','<>',$admin_id)->pluck('data_id')->all();

            // 获取该频道下的动态
            $datas = Tweet::with('hasOneContent')->whereNull('original')->where('type',0)->wait()->whereNotIn('id',$tweet_ids);

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
                        $datas = $datas->where('content','like','%'.$search.'%');
                        break;
                    default:
                        return back();
                }
            }

            // 获取集合
            $datas = $datas -> paginate((int)$request->input('num',$this->paginate));

            // 获取频道信息
            $channel = Channel::active()->whereNotIn('name',['关注','热门'])->get(['id','name'])->all();

            // 将该审批人员名下视频暂存表中的视频删除
            TweetTempCheck::where('admin_id',$admin_id)->delete();

            // 将新请求的视频id存至表中
            $datas -> each(function($data)use($admin_id){

                TweetTempCheck::create([
                    'admin_id' => $admin_id,
                    'data_id'  => $data -> id,
                    'time_add' => getTime()
                ]);
            });

            // 开启事务
//            DB::beginTransaction();

            // 将所取数据标记为当前管理员的id


//            dd($tweets);

            // 将信息存入审批日志表
//            TweetManageLog::create([]);

            // 事务提交
//            DB::commit();

            // 搜索条件
            $cond = [1=>'ID',2=>'内容'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',10),
                'search'=>$search,
            ];

            return view('admin/video/check')
                ->with([
                    'datas'=>$datas,
                    'channel'=>$channel,
                    'request'=>$res,
                    'condition'=>$cond,
                ]);

        }catch(\Exception $e){

            // 事务回滚
            DB::rollBack();

            // 404报错
            abort(404);
        }
    }

    /**
     * 发布视频
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('admin/video/create');
    }

    /**
     * 保存发布的视频
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function insert(Request $request)
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

            // 获取时长
            $duration = json_decode(file_get_contents(CloudStorage::downloadUrl($tweet->video.'?avinfo')));

            // 保存时长
            $tweet->duration = $duration->format->duration;

            // 新名字
            $new_key = 'tweet/' . $tweet->id . '/' . getTime().mt_rand(100000,999999).'.'.pathinfo($tweet->video,PATHINFO_EXTENSION);

            CloudStorage::rename($tweet->video, $new_key);
            $tweet->screen_shot = $result[0]['key'];
            $tweet->video = $new_key;

            $tweet->save();

            # 更新 users 表中的作品总量数据

            // 判断是否为转发或原创
            if(isset($tweet->retweet)){

                // 转发总量加1
                User::findOrFail($user->user_id) -> increment('retweet_count');
            }else{

                // 作品总量加1
                User::findOrFail($user->user_id) -> increment('work_count');
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
        return redirect('/admin/video/details?id='.$tweet->id);
    }

    /**
     * 视频详情
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detailsdetails(Request $request)
    {
        try {

            // 获取视频id
            $id = (int)$request -> get('id');

            // 具体视频详情
            $video = Tweet::with('belongsToManyChannel','hasOneContent')->where('id',$id)->where('type',0)->firstOrFail();

            // 如果动态为正常状态，则有频道信息
            if($video->active !== 1){

                // 获取视频的所属频道为空
                $video -> channel_name = '';
            }else{

                // 遍历频道信息，获取名字
                foreach($video->belongsToManyChannel as $k=>$value){

                    $channel_name[$value['id']] = $value['name'];
                }

                // 获取视频的所属频道
                $video -> channel_name = implode('，',$channel_name);
            }


            // 返回数据
            return view('/admin/video/details',['video'=>$video,'active'=>$video -> active]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 视频审批处理
     */
    public function apply(Request $request)
    {
        try{

            // 接收要操作的视频id
            $id = (int)$request -> input('id');

            // 频道id
            $channel_id = (int)$request -> input('channel_id');

            // 接收视频状态
            $active = (int)$request -> input('active') === 1 ? 1 : 2;

            // 开启事务
            DB::beginTransaction();

            // 处理
            Tweet::find($id)->update(['active'=>$active]);

            // 删除 channel_tweet 表中的相应数据
            ChannelTweet::ofTweetID($id)->delete();

            // 如果active为1，添加 channel_tweet 表中的相应数据
            if($active === 1){

                // 获取时间
                $time = new Carbon();

                // 频道如果设置，存入 channel_tweet 表
                ChannelTweet::create([
                        'channel_id' => $channel_id,
                        'tweet_id'   => $id,
                        'updated_at' => $time,
                        'created_at' => $time
                    ]);
            }

            // 当天审批视频数量，不重复
            $tweet_amount = TweetManageLog::where('admin_id',session('admin')->id)
                ->where('data_id',$id)
                ->where('time_add','>',strtotime('today'))
                ->first();

            // 如果当天已经审批过该视频，则不+1
            if(!$tweet_amount){

                // 获取数量统计集合
                $amount = TweetExamineAmount::where('admin_id',session('admin')->id)->where('date',date('Ymd'))->first();

                // 判断是否为重复审批，并保存数量+1
                if(!$amount) {

                    // 创建集合
                    TweetExamineAmount::create([
                        'admin_id'=>session('admin')->id,
                        'amount' => 1,
                        'date'=>date('Ymd'),
                        'time_add'=>getTime(),
                        'time_update'=>getTime(),
                    ]);
                }else{

                    $amount -> amount ++;

                    $amount -> time_update = getTime();

                    // 保存集合
                    $amount -> save();
                }
            }

            // 写入日志 管理日志
            TweetManageLog::create([
                'admin_id'      => Auth::guard('web')->user() -> id,
                'data_id'       => $id,
                'active'        => $active,
                'channel_ids'   => $channel_id,
                'time_add'      => getTime()
            ]);

            // 提交事务
            DB::commit();

            //返回处理操作成功信息
            return response() -> json(1);

        }catch(\Exception $e){

            // 事务回滚
            DB::rollBack();

            // 返回处理操作失败信息
            return response() -> json(2);
        }
    }

    /**
     * 编辑页面
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        try {

            // 接收要操作的视频id
            $id = (int)$request -> input('id');

            // 获取视频集合
            $video = Tweet::with('belongsToManyChannel','hasOneHot','hasOneTop','hasOnePush')->where('id',$id)->where('type',0)->firstOrFail();

            // 所在的话题
            $topics_ids = TweetTopic::where('tweet_id',$id)->pluck('topic_id')->all();

            $topics_in = Topic::whereIn('id',$topics_ids)->get(['id','name']);


            // 置顶和推荐时间
            if($video -> hasOneTop){

                $video -> top_expires = date('Y-m-d H:i:s',$video -> hasOneTop -> top_expires);
                $video -> recommend_expires = date('Y-m-d H:i:s',$video -> hasOneTop -> recommend_expires);
            }

            // 所在的活动
            $activities_ids = TweetActivity::where('tweet_id',$id)->pluck('activity_id')->all();

            $activities_in = Activity::whereIn('id',$activities_ids)->get(['id','name']);

            // 获取在用的channel数据
            $channels = Channel::active()->get(['id','name']);

            // 获取所有在用话题
            $topics = Topic::active()->get(['id','name']);

            // 获取所有在用活动
            $activities = Activity::active()->get(['id','name']);

            // 返回该视频的编辑页面
            return view('/admin/video/edit',[
                'video'=>$video,
                'topics_in'=>$topics_in,
                'activities_in'=>$activities_in,
                'channels'=>$channels,
                'activities'=>$activities,
                'topics'=>$topics,
                'active'=>$video -> active
            ]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 更改
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id 动态id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {

            // 接收要操作的视频id
            $id = (int)$request -> input('id');

            // 获取该id的动态情况，tweet 表
            $video = Tweet::where('id',$id)
                ->where('type',0)
                ->whereNull('original')
                ->with('hasManyTopicTweet','hasManyActivityTweet','hasManyChannelTweet','hasOneHot','hasOneTop','hasOnePush')
                ->firstOrFail();

//            dd($video-> hasOneTop -> top_expires);

            // 获取所有提交的数据
            $input = $request->all();

            // 将updated_at 时间转为时间戳，如果修改期间该tweet有变动，将返回到该tweet详情页面
            if (strtotime($video->updated_at) - strtotime($input['_time'])) {
                return redirect('admin/content/video/' . $id);
            }

            // 开启事务模式
            DB::beginTransaction();

            // 写入日志 管理日志
            $tweetManageLog = TweetManageLog::create([
                'admin_id'      => Auth::guard('web')->user() -> id,
                'data_id'       => $id,
                'time_add'      => getTime()
            ]);

            // 判断置顶和推荐 是否 全部关闭
            if (!isset($input['top_check']) && !isset($input['recommend_check'])) {

                # 判断是否之前打开过置顶或推荐，如果打开，则关闭
                // 获取该动态在 zx_tweet_top 表中的集合
                $tweet_top = $video->hasOneTop;

                // 如果存在，则删除
                if ($tweet_top) {

                    // 写入日志 管理日志
                    if($tweet_top -> top_expires) $tweetManageLog -> top_expires = 'off';
                    if($tweet_top -> recommend_expires) $tweetManageLog -> recommend_expires = 'off';

                    // 删除集合
                    $tweet_top->delete();
                }
            }

            // 判断置顶或推荐是否打开,如果有打开，则先创建集合，然后进一步修改并保存
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

                // 转换时间格式
                $top_expires = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['top_date'] . ' ' . $input['top_time']));

                // 与原来的设置不同
                if($top_expires != $tweet_top->top_expires){

                    // 更改在集合中的设置
                    $tweet_top->top_expires = $top_expires;

                    // 保存集合
                    $tweet_top->save();

                    // 写入日志
                    $tweetManageLog -> top_expires = $tweet_top -> top_expires;
                }

                // 判断是否关闭了 推荐
                if(!isset($input['recommend_check']) && !$tweet_top->recommend_check){

                    // 更改在集合中的设置
                    $tweet_top->recommend_expires = 0;

                    // 日志
                    $tweetManageLog -> recommend_expires = 'off';
                }
            }

            // 如果设置了推荐，格式化时间，存入集合
            if (isset($input['recommend_check']) && $input['recommend_check'] === 'on') {

                // 判断是否与原来的设置相同
                $recommend_expires = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['recommend_date'] . ' ' . $input['recommend_time']));

                // 判断是否与原来的设置相同
                if($recommend_expires != $tweet_top->recommend_expires){

                    // 更改在集合中的设置
                    $tweet_top->recommend_expires = $recommend_expires;

                    // 保存集合
                    $tweet_top->save();

                    // 写入日志
                    $tweetManageLog -> recommend_expires = $tweet_top -> recommend_expires;
                }

                // 判断是否关闭了 置顶
                if(!isset($input['top_expires']) && !$tweet_top->top_expires){

                    // 更改在集合中的设置
                    $tweet_top -> top_expires = 0;

                    // 日志
                    $tweetManageLog -> top_expires = 'off';
                }
            }

            // 如果设置了推送，格式化时间，存入集合
            if (isset($input['push_check']) && $input['push_check'] === 'on') {

                // 匹配日期
                if($input['push_date'] < date('Ymd') || !regex_date($input['push_date'])) return back()->with('error','推送日期不合规');

                // 查询是否已经推送过
                $tweets_push = $video->hasOnePush;

                // 查询推送条数是否已经到达20条
                $tweets_count = TweetsPush::where('date',$input['push_date'])->count();

                // 不能重复推送或已超出推送日的数量上限
                if($tweets_push || $tweets_count>=20){
                    \Session::flash('push', '推送日期不合规或数量已超限');
                    return redirect('/admin/video/edit?id='.$id)->withInput();
                }

                // 存入数据库
                TweetsPush::create([
                    'tweet_id'      => $id,
                    'date'          => $input['push_date'],
                    'time_add'      => getTime(),
                    'time_update'   => getTime()
                ]);

                // 写入日志
                $tweetManageLog -> push_date = $input['push_date'];
            }

            // 获取热门集合
            $hot = $video->hasOneHot;

            // 如果设置了热门
            if (isset($input['hot_check']) && $input['hot_check'] === 'on') {

                // 如果未设置热门,创建集合
                if(!$hot){

                    TweetHot::create([
                        'tweet_id'      => $id,
                        'time_add'      => getTime(),
                        'time_updated'   => getTime()
                    ]);
                }

                // 写入日志
                $tweetManageLog -> hot = 1;
            }else{

                // 关闭热门，如果热门集合存在，则删除
                if($hot) {

                    $hot -> delete();

                    // 写入日志
                    $tweetManageLog -> hot = 2;
                }
            }

            // 话题
            if (isset($input['topics']) && ! empty($input['topics'])) {

                // 检测该动态所属话题
                $topics = $video->hasManyTopicTweet->pluck('topic_id')->all();

                // 如果所属话题有修改，则删除旧的，重新创建新的集合，有待改进
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

                    // 写入日志
                    $tweetManageLog -> topic_ids = implode(',',$input['topics']);
                }
            }

            // 活动
            if (isset($input['activities']) && ! empty($input['activities'])) {

                // 检测该动态所属话题
                $topics = $video->hasManyActivityTweet->pluck('activity_id')->all();

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

                    // 写入日志
                    $tweetManageLog -> activity_ids = implode(',',$input['activities']);
                }
            }

            // 频道如果设置，存入 channel_tweet 表
            if (isset($input['channels']) && ! empty($input['channels'])) {

                // 获取 channel_tweet 表中的相应数据
                $channels = $video->hasManyChannelTweet->pluck('channel_id')->all();

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

                    // 写入日志
                    $tweetManageLog -> channel_ids = implode(',',$input['channels']);
                }
            }

            // 保存动态
            $video->save();

            // 保存集合
            $tweetManageLog->save();

            // 事务提交
            DB::commit();

            // 重定向到视频详情页
            return redirect('admin/video/details?id=' . $id);

        } catch (ModelNotFoundException $e) {

            // 事务回滚
            DB::rollBack();

            // 跳出404页面
            abort(404);
        }
    }

    /**
     * 屏蔽视频页面
     */
    public function recycle(Request $request)
    {
        try{
            // 搜索条件
            $condition = (int)$request -> get('condition','');
            $search = post_check($request -> get('search',''));

            // 获取该频道下的动态
            $datas = Tweet::with('hasOneContent')->whereNull('original')->where('type',0)->where('active',2);

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

            // 获取集合
            $datas = $datas -> paginate((int)$request->input('num',$this->paginate));

            // 获取频道信息
            $channel = Channel::active()->whereNotIn('name',['关注','热门'])->get(['id','name'])->all();

            // 搜索条件
            $cond = [1=>'ID',2=>'内容'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',10),
                'search'=>$search,
            ];

            return view('admin/video/recycle')
                ->with([
                    'datas'=>$datas,
                    'channel'=>$channel,
                    'request'=>$res,
                    'condition'=>$cond
                ]);

        }catch(\Exception $e){

            // 事务回滚
            DB::rollBack();

            // 404报错
            abort(404);
        }
    }

    /**
     * 视频审批数量统计
     */
    public function amount(Request $request)
    {
        try{

            // 搜索条件
            $search = post_check($request -> get('search'));
            $num = (int)$request->input('num',$this->paginate);

            // 获取集合
            $datas = TweetExamineAmount::orderBy('id','desc');

            // 是否为搜索
            if($search){

                // 条件
                switch($request -> get('condition')){

                    // 日期
                    case 1:
                        // 判断是否符合规则
                        $datas = $datas->where('date',(int)$search);
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
            $cond = [1=>'日期',2=>'审批人'];

            // 设置返回数组
            $res = [
                'condition' => $request -> get('condition'),
                'num'=>$num,
                'search'=>$search,
            ];

            // 返回视图
            return view('admin/video/amount',[
                'datas'=>$datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
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