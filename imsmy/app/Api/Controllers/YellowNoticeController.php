<?php

namespace App\Api\Controllers;

use App\Models\NoExitWord;
use App\Models\Tweet;
use App\Models\TweetQiniuCheck;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class YellowNoticeController extends Controller
{
    public function notice()
    {
        $NotifyData = file_get_contents("php://input");

        $res = json_decode($NotifyData)->code;

        if ($res ===0 ){
            $keyword= json_decode($NotifyData)->items[0]->key;
            $tweet_id=getNeedBetween($keyword, '&' , '&&' );
            $new_url = 'v.cdn.hivideo.com/'.json_decode($NotifyData)->items[0]->key;

            //动态的信息
            $tweet =  Tweet::find( $tweet_id);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://'.$new_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_REFERER, "http://www.hivideo.com");
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result);
            if($result->message !== 'success'){
                TweetQiniuCheck::create([
                    'user_id'   => $tweet->user_id,
                    'tweet_id' => $tweet_id,
                    'tupu_video' => 1,
                    'create_time' => time(),
                ]);
                \DB::table('tweet_qiniu_check')->where('id', '=', $tweet_id->id)->update(['tupu_video' => 1]);

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
