<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\BaseSessionController;
use App\Http\Controllers\Controller;
use App\Services\VideoService;
use App\Services\ChannelService;
use App\Models\Admin\Administrator;
use App\Models\TweetManageLog;
use App\Models\ChannelTweet;
use App\Models\Label;
use App\Models\Tweet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use Auth;
use DB;
use Image;
class AjaxController extends BaseSessionController
{

    /**
     * ajax获取Video信息
     */
    public function video(Request $request)
    {
        // 视频频道
        $channel_id = $request->input('type',1);

        // 状态 正常为1，未审批为0，
        $active = $request->input('active',1);

        // 数量
        $num = 6;

        // 获取该频道下的动态
        $tweets = Tweet::whereHas('hasManyChannelTweet',function($query)use($channel_id){
            $query -> where('channel_id',$channel_id);
        })->get();

        return $tweets;

        $videoService = new VideoService();

        // 视频信息
        return response()->json($videoService->selectListByChannel($active,$channel_id,$num));
    }

    /**
     * 视频待审批页面
     */
    public function check(Request $request)
    {
        // 验证登录信息
        $user = Auth::guard('web')->user();

        // 获取频道信息
        $channelService = new ChannelService();
        $where = [['active',1]];
        $channel = $channelService-> selectList($where,[],'id,name');

        return view('admin/content/video/check')
            ->with('user',$user)
            ->with('channel',$channel);
    }

    /**
     * 视频已屏蔽页面
     */
    public function forbid(Request $request)
    {
        // 验证登录信息
        $user = Auth::guard('web')->user();

        // 获取频道信息
        $channelService = new ChannelService();
        $where = [['active',1]];
        $channel = $channelService-> selectList($where,[],'id,name');

        return view('admin/content/video/forbid')
            ->with('user',$user)
            ->with('channel',$channel);
    }

    /**
     * 视频审批处理
     */
    public function apply(Request $request)
    {
        // 接收要操作的视频id
        $id = (int)$request -> input('id');

        // 接收视频状态
        $active = $request -> input('active') == 1 ? 1 : 2;

        // 实例化
        $videoService = new VideoService();

        // 处理
        $result = $videoService->updateById($id,['active'=>$active]);

        // 如果active为2 屏蔽，删除 channel_tweet 表中的相应数据
        if($active != 1){
            ChannelTweet::ofTweetID($id)->delete();
        }

        // 写入日志 管理日志
        TweetManageLog::create([
            'admin_id'  => Auth::guard('web')->user() -> id,
            'data_id'   => $id,
            'active'    => $active,
            'time_add'  => getTime()
        ]);

        //返回处理操作成功信息
        if($result) return response() -> json(1);

        // 返回处理操作失败信息
        return response() -> json(2);
    }

    /**
     * 视频所属频道选择与状态更改
     */
    public function choose(Request $request)
    {
        // 接收要操作的视频id
        $id = $request -> input('video');

        // 所选择的视频
        $channel_id = $request -> input('channel_id');

        // 实例化
        $videoService = new VideoService();

        // 处理,将tweet表中该视频的状态改为激活状态，频道信息修改为相应的频道id
        $result = $videoService->updateById($id,['active'=>1,'channel_id'=>$channel_id]);

        // 删除 channel_tweet 表中的相应数据
        ChannelTweet::ofTweetID($id)->delete();

        // 获取时间
        $time = new Carbon();

        // 频道如果设置，存入 channel_tweet 表
        DB::table('channel_tweet')->insert(
            [
                'channel_id' => $channel_id,
                'tweet_id'   => $id,
                'updated_at' => $time,
                'created_at' => $time
            ]);

        //返回处理操作成功信息
        if($result) return back();

        // 返回处理操作失败信息
        return back();
    }
}