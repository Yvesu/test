<?php

namespace App\Api\Controllers;

use App\Facades\CloudStorage;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class DeleteController extends BaseController
{
    //删除动态
    public function tweet(Request $request)
    {
        try {
            if (!is_numeric($id = $request->get('id'))) return response()->json(['message' => 'bad_request'], 403);

            //修改动态的状态为个人删除
            $tweet = Tweet::findOrFail($id);

            $user_id = $tweet->user_id;

            //将动态的状态改为用户自己删除
            $mysql_res = $tweet->update(['active' => 3]);

            //删除图片与视频资源
            $http = \Config::get('constants.HTTP');

            //视频地址
            $video_url = ltrim(parse_url($http . $tweet->video)['path'], '/');

            //图片地址
            $image_url = ltrim(parse_url($http . $tweet->screen_shot)['path'], '/');

            //删除视频
            $video_res = CloudStorage::deleteFile('hivideo-video', $video_url);

            //删除图片
            $image_res = CloudStorage::deleteFile('hivideo-img', $image_url);

            if ($mysql_res || $video_res === 'success' || $image_res === 'success') {
                Log::info('delete tweet is ' . $id . ' failed');
            }
            if ($mysql_res) {
                User::find($user_id)->decrement('work_count');
                return response()->json(['message' => 'success'], 200);
            } else {
                return response()->json(['message' => 'failed'], 500);
            }
        }catch (\Exception $e){
            Log::info('DELETE IS ERROR');
            return response()->json(['messgae'=>'bad_request'],500);
        }
    }

}
