<?php

namespace App\Api\Controllers;

use App\Facades\CloudStorage;
use App\Models\Mark;
use App\Models\MarkTweet;
use App\Models\Tweet;
use App\Models\TweetMark;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DownloadTweetController extends BaseController
{
    public function mark($id)
    {
//        try {
//            //获取动态信息
//            $tweet_info = Tweet::find($id);
//
//            //没有该动态
//            if (is_null($tweet_info))           return response()->json(['message'=>'There is no dynamic'],403);
//
//            //拒绝下载
//            if ($tweet_info->is_download === 0) return response()->json(['message' => 'Refused to download'], 403);
//
//            //获取加水印后地址
//            $url = MarkTweet::where('tweet_id',$id)->first();
//
//            //判断七牛加水印是否成功
//            if (is_null($url)) return response()->json(['message'=>'Wait a minute'],205);
//
//            //返回数据
//            return response()->json([
//                'data'  =>   CloudStorage::downloadUrl($url->url),
//            ]);
//        }catch (\Exception $e){
//            return response()->json(['message'=>'bad_request'],500);
//        }

 //---------------------------------------------------------------------------------------------
//        $willMark = TweetMark::where('active',1)->take(2)->get();
//
//        if (!$willMark->count()){
//            die();
//        }
//
//        $arrs = $willMark->toArray();
//
//        foreach ($arrs as $arr) {
//            //        //获取动态
//            $tweet = Tweet::find($arr['tweet_id']);
//
////        //视频地址
//            $tweet_url = $tweet->video;
//
////        //获取水印
//            $mark = Mark::where('active', 1)->find($arr['mark_id']);
//
//            $mark_url = $mark->mark_content;
//
//            $id = $arr['tweet_id'];
//
//            $noti = 'http://www.goobird.com/api/notification';
//
//            $user = User::find($tweet->user_id);
//
//            $nickname = $user->nickname;
//
//            CloudStorage::Mark($tweet_url, $mark_url, $id,$noti,$nickname);

//        }

        //------------------------------------------------------------------------------------
        $tweet = Tweet::find($id);
        $noti = 'http://www.goobird.com/api/notification';
        CloudStorage::joint($tweet,$noti);
    }
}
