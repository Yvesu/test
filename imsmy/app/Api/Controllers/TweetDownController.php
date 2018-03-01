<?php

namespace App\Api\Controllers;

use App\Facades\CloudStorage;
use App\Models\MarkTweet;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TweetDownController extends Controller
{
    public function down($id,$userid=0)
    {
        $tweet = Tweet::where('active',1)->find((int)$id);

        if (!$tweet) return response()->json(['message'=>'bad request'],404);

        if ($tweet->is_download === 0 && $tweet->user_id!==(int)$userid) return response()->json(['message'=>'Refused to download'],403);

        $downtweet = MarkTweet::where('tweet_id',$tweet->id)->first();

        if (!$downtweet){
            return response()->json(['message'=>'bad request'],500);
        }
        return response()->json([
            'download_url'=>  CloudStorage::downloadUrl($downtweet->url),
        ],200);
    }
}
