<?php

namespace App\Console\Commands;

use App\Facades\CloudStorage;
use App\Models\PrivateLetter;
use App\Models\Tweet;
use App\Models\TweetContent;
use App\Models\TweetQiniuCheck;
use Illuminate\Console\Command;

class Qicheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Qicheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'qicheck';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        set_time_limit(0);

        $ids = \DB::table('tweet_to_qiniu')->take(2)->where('active',1)->pluck('tweet_id');

        if ($ids->all()) {
            foreach ($ids as $id) {

                $tweet = Tweet::find($id);
                \DB::table('tweet_to_qiniu')->where('tweet_id', $id)->update(['active' => 2]);
                if ($tweet) {
                    // 图片鉴黄
                    $url = CloudStorage::ImageCheck($tweet->screen_shot);   //封面路径

                    $opts = [
                        'http' => [
                            'method' => 'GET',
                            'header' => "Content-type:application/x-www-form-urlencoded\r\n" .
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
                            'user_id'   => $tweet->user_id,
                            'tweet_id' => $tweet->id,
                            'image_qpulp' => 2,
                            'create_time' => time(),
                        ]);

                        //创建私信
                        $tweet =  Tweet::find( $tweet->id);
                        $tweet_content = TweetContent::where('tweet_id',$tweet->id)->first()->content;
                        $time = time();
                        $tweet_content = $tweet_content ? "您最新发送的动态<{$tweet_content}>可能涉及违规,我们将尽快为您处理..." : "您于 ".date('Y-m-d H:i:s')." 发布的动态可能涉及违规,我们将尽快为您处理..." ;
                        PrivateLetter::create([
                            'from' => 1000437,
                            'to'    => $tweet->user_id,
                            'content'   => $tweet_content,
                            'created_at' => $time,
                            'updated_at' =>$time,
                        ]);

                    } else if ($image_qpulp_res['result']['label'] == 1) {
                        // 七牛检测未通过  涉及色情
                        Tweet::where('id', '=', $tweet->id)->update(['active' => 6]);
                        //创建记录
                        $tweet_qiniu_check = TweetQiniuCheck::create([
                            'user_id'   => $tweet->user_id,
                            'tweet_id' => $tweet->id,
                            'image_qpulp' => 1,
                            'create_time' => time(),
                        ]);

                        //创建私信
                        $tweet =  Tweet::find( $tweet->id);

                        $tweet_content = TweetContent::where('tweet_id',$tweet->id)->first()->content;

                        $time = time();
                        $tweet_content = $tweet_content ? "您最新发送的动态<{$tweet_content}>可能涉及违规,我们将尽快为您处理..." : "您于 ".date('Y-m-d H:i:s')." 发布的动态可能涉及违规,我们将尽快为您处理..." ;
                        PrivateLetter::create([
                            'from' => 1000437,
                            'to'    => $tweet->user_id,
                            'content'   => $tweet_content,
                            'created_at' => $time,
                            'updated_at' =>$time,
                        ]);

                    } else {
                        $tweet_qiniu_check = TweetQiniuCheck::create([
                            'user_id'   => $tweet->user_id,
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
                            'header' => "Content-type:application/x-www-form-urlencoded\r\n" .
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

                            //创建私信
                            $tweet =  Tweet::find( $tweet->id);

                            $tweet_content = TweetContent::where('tweet_id',$tweet->id)->first()->content;
                            $tweet_content = $tweet_content ? "您最新发送的动态<{$tweet_content}>可能涉及违规,我们将尽快为您处理..." : "您于 ".date('Y-m-d H:i:s')." 发布的动态可能涉及违规,我们将尽快为您处理..." ;
                            $time = time();
                            PrivateLetter::create([
                                'from' => 1000437,
                                'to'    => $tweet->user_id,
                                'content'   => $tweet_content,
                                'created_at' => $time,
                                'updated_at' =>$time,
                            ]);
                        }
                    }


                    //短视频鉴黄
                    $tupu_video = CloudStorage::privateUrl($tweet->video . '?tupu-video/nrop/f/5/s/30');

                    $opts_2 = [
                        'http' => [
                            'method' => 'GET',
                            'header' => "Content-type:application/x-www-form-urlencoded\r\n" .
                                "Referer:http://www.goobird.com",
                        ],
                    ];
                    $context = stream_context_create($opts_2);
                    $tupu_video_res = file_get_contents($tupu_video, false, $context);

                    $tupu_video_result = json_decode($tupu_video_res, true);

                    if ($tupu_video_result['review']) {
                        if ($tweet->screen_shot == null) {
                            TweetQiniuCheck::create([
                                'user_id' => $tweet->user_id,
                                'tweet_id' => $tweet->id,
                                'tupu_video' => 1,
                                'create_time' => time(),
                            ]);
                        }
                        \DB::table('tweet_qiniu_check')->where('id', '=', $tweet_qiniu_check->id)->update(['tupu_video' => 1]);

                            //创建私信
                            $tweet =  Tweet::find( $tweet->id);

                            $tweet_content = TweetContent::where('tweet_id',$tweet->id)->first()->content;
                            $tweet_content = $tweet_content ? "您最新发送的动态<{$tweet_content}>可能涉及违规,我们将尽快为您处理..." : "您于 ".date('Y-m-d H:i:s')." 发布的动态可能涉及违规,我们将尽快为您处理..." ;
                            $time = time();
                            PrivateLetter::create([
                                'from' => 1000437,
                                'to'    => $tweet->user_id,
                                'content'   => $tweet_content,
                                'created_at' => $time,
                                'updated_at' =>$time,
                            ]);
                    }
                }
            }
        }
    }
}
