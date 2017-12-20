<?php

namespace App\Api\Controllers;

use App\Facades\CloudStorage;
use App\Models\Mark;
use App\Models\Tweet;
use App\Models\TweetMark;
use App\Models\TweetToCheck;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Qiniu\Processing\ImageUrlBuilder;

class QiNiuNotificationController extends BaseController
{
    public function notification()
    {
        $tweets = TweetMark::where('active',1)->take(2)->get(['id','tweet_id']);

        array_map([$this,'mark'],$tweets->toArray());

    }
    private function mark(array $arr)
    {
       try{
            $tweet = Tweet::find($arr['tweet_id']);

           if ($tweet->screen_shot){
                //实例化
               $imageUrlBuilder = new ImageUrlBuilder();
               //获取图片地址
               $url = CloudStorage::downloadUrl($tweet->screen_shot);

               $mark = Mark::where('active',1)->first();

               $waterLink = $imageUrlBuilder->waterImg($url, $mark->mark_content,'30','SouthEast','100','10','0.1');

           }

       }catch (\Exception $e){
           return response()->json(['message'=>'bad_request'],500);
       }
    }


}
