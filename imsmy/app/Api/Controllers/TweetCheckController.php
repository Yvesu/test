<?php

namespace App\Api\Controllers;

use App\Facades\CloudStorage;
use App\Models\Tweet;
use App\Models\TweetQiniuCheck;
use App\Http\Controllers\Controller;

class TweetCheckController extends Controller
{
    public function check()
    {
        set_time_limit(0);

        $ids = \DB::table('tweet_to_qiniu')->pluck('tweet_id');

        foreach ($ids as $id) {

            $tweet = Tweet::find($id);

            if ($tweet) {
                // 图片鉴黄
                $url = CloudStorage::ImageCheck($tweet->screen_shot);   //封面路径

                $opts = [
                    'http' => [
                        'method' => 'GET',
                        'header' => "Content-type:application/x-www-form-urlencoded\r\n".
                            "Referer:http://www.goobird.com",
                    ],
                ];
                $context = stream_context_create($opts);
                $image_qpulp = file_get_contents($url, false, $context);

                $image_qpulp_res = json_decode($image_qpulp, true);

                if ($image_qpulp_res['result']['label'] == 0) {
                    // 七牛检测未通过  涉及色情
                    Tweet::where('id', '=', $tweet->id)->update(['active' => 6]);

                    //创建记录
                    $tweet_qiniu_check = TweetQiniuCheck::create([
                        'tweet_id' => $tweet->id,
                        'image_qpulp' => 2,
                        'create_time' => time(),
                    ]);
                } else if ($image_qpulp_res['result']['label'] == 1) {
                    // 七牛检测未通过  涉及色情
                    Tweet::where('id', '=', $tweet->id)->update(['active' => 6]);
                    //创建记录
                    $tweet_qiniu_check = TweetQiniuCheck::create([
                        'tweet_id' => $tweet->id,
                        'image_qpulp' => 1,
                        'create_time' => time(),
                    ]);
                } else {
                    $tweet_qiniu_check = TweetQiniuCheck::create([
                        'tweet_id' => $tweet->id,
                        'image_qpulp' => 0,
                        'create_time' => time(),
                    ]);
                }

                //政治人物检测
                $url_z = CloudStorage::qpolitician($tweet->screen_shot);  //tupian

                $opts_2 = [
                    'http' => [
                        'method' => 'GET',
                        'header' => "Content-type:application/x-www-form-urlencoded\r\n".
                            "Referer:http://www.goobird.com",
                    ],
                ];
                $context = stream_context_create($opts_2);
                $qpolitician = file_get_contents($url_z, false, $context);

                //取数据
                $qpolitician_result = json_decode($qpolitician, true);

                //写入检测记录
                foreach ($qpolitician_result['result']['detections'] as $v) {
                    if (array_key_exists("sample", $v)) {
                        //写入记录
                        TweetQiniuCheck::where('id', '=', $tweet_qiniu_check->id)->update(['qpolitician' => 1]);

                        //修改状态
                        Tweet::where('id', '=', $tweet->id)->update(['active' => 6]);
                    }
                }


                //短视频鉴黄
                $tupu_video = CloudStorage::privateUrl($tweet->video . '?tupu-video/nrop/f/5/s/30');

                $opts_2 = [
                    'http' => [
                        'method' => 'GET',
                        'header' => "Content-type:application/x-www-form-urlencoded\r\n".
                            "Referer:http://www.goobird.com",
                    ],
                ];
                $context = stream_context_create($opts_2);
                $tupu_video_res = file_get_contents($tupu_video, false, $context);

                $tupu_video_result = json_decode($tupu_video_res, true);

                if ($tupu_video_result['review']) {
                    if ($tweet->screen_shot == null) {
                        TweetQiniuCheck::create([
                            'user_id' => $id,
                            'tweet_id' => $tweet->id,
                            'tupu_video' => 1,
                            'create_time' => time(),
                        ]);
                    }

                    \DB::table('tweet_qiniu_check')->where('id', '=', $tweet_qiniu_check->id)->update(['tupu_video' => 1]);
                }
            }
            \DB::table('tweet_to_qiniu')->where('tweet_id',$id)->delete();
        }
    }

//    public function docheck()
//    {
//        function doCurlPostRequest($url,$requestString,$timeout = 5){
//            if($url == '' || $requestString == '' || $timeout <=0){
//                return false;
//            }
//            $con = curl_init((string)$url);
//            curl_setopt($con, CURLOPT_HEADER, false);
//            curl_setopt($con, CURLOPT_POSTFIELDS, $requestString);
//            curl_setopt($con, CURLOPT_POST,true);
//            curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
//            curl_setopt($con, CURLOPT_TIMEOUT,(int)$timeout);
//            return curl_exec($con);
//        }
//
//        $url = CloudStorage::downloadUrl('v.cdn.hivideo.com/fragment/demonstration/admin/101211/100001/20171111162929.mp4');
//
//       $key = ltrim(parse_url($url)['path'],'/');
//
//       $accessToken  =  CloudStorage::signAgain('v.cdn.hivideo.com/fragment/demonstration/admin/101211/100001/20171111162929.mp4');
//
//
//            'bucket= hivideo-video'. 'key=' .$key.'fops='.'notifyURL= '
//        doCurlPostRequest('')
//        $data = http_build_query($data);
//
//        $opts = array (
//            'http' => array (
//                'method' => 'POST',
//                'header'=> "Content-type: application/x-www-form-urlencoded \r\n".
//                    "Referer:http://www.goobird.com \r\n".
//                "Authorization: QBox ".parse_url($url)['path'] ,
//
//                'content' => $data
//            )
//        );
//        $context = stream_context_create($opts);
//        $html = file_get_contents('http://api.qiniu.com/pfop/', false, $context);
//        echo $html;
//
//    }


}           //    /var/www/imsmy/app/Api/Controllers/TweetCheckController.php


    //TweetCheckController::check();