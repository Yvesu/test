<?php

namespace App\Api\Controllers;

use App\Facades\CloudStorage;
use App\Models\Mark;
use App\Models\MarkTweet;
use App\Models\NoExitWord;
use App\Models\Tweet;
use App\Models\TweetMark;
use App\Models\TweetToCheck;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Qiniu\Processing\ImageUrlBuilder;

class QiNiuNotificationController extends BaseController
{
    public function notification()
    {
         $NotifyData = file_get_contents("php://input");

//        $NotifyData = '{"id":"z0.5a3ba18eb946531900ed0af4","pipeline":"1381295464.chijiu","code":0,"desc":"The fop was completed successfully","reqid":"P0wAAOI3xQhOTQIV","inputBucket":"image","inputKey":"test.mp4","items":[{"cmd":"avthumb/mp4/s/640x360/vb/1.4m/wmImage/aHR0cDovLzEwMS4yMDAuNzUuMTYzL2hvbWUvaW1nL2xvZ28ucG5n/wmGravity/NorthWest/wmOffsetX/10/wmOffsetY/10/wmConstant/0|saveas/aW1hZ2U6XzIwMV90ZXN0Lm1wNA==","code":0,"desc":"The fop was completed successfully","hash":"lrXeSh0mgGniri8xcd10KtwmjCuu","key":"&201&&test.mp4","returnOld":0}]}';

        //判断结果
        $res = json_decode($NotifyData)->code;

        if ($res ===0 ){
            $keyword= json_decode($NotifyData)->items[0]->key;
            $tweet_id=getNeedBetween($keyword, '&' , '&&' );
            $new_url = 'v.cdn.hivideo.com/'.json_decode($NotifyData)->items[0]->key;
            $time = time();

            \DB::beginTransaction();
            $result = MarkTweet::create([
                'tweet_id'      =>  $tweet_id,
                'url'           =>  $new_url,
                'active'        =>  0,
                'create_time'   =>  $time,
            ]);

            if ($result){
                \DB::commit();
                TweetMark::where('tweet_id',$tweet_id)->update(['active'=>2]);
            }else{
                TweetMark::where('tweet_id',$tweet_id)->update(['active'=>1]);
                \DB::rollBack();
            }

        }else{

            $keyword= json_decode($NotifyData)->items[0]->key;
            $tweet_id=getNeedBetween($keyword, '_' , '__' );
            $new_url = 'v.cdn.hivideo.com/'.json_decode($NotifyData)->items[0]->key;
            $time = time();

            MarkTweet::create([
                'tweet_id'      =>  $tweet_id,
                'url'           =>  $new_url,
                'active'        =>  $res,
                'create_time'   =>  $time,
            ]);
        }








    }



}
