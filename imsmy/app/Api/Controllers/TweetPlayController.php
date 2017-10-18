<?php
namespace App\Api\Controllers;

use App\Models\UserTweetsHistory;
use App\Models\Tweet;
use App\Models\Activity;
use App\Models\Topic;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Auth;
use DB;

/**
 * 该类为被调用接口，主要用于播放次数自增
 * Class TweetPlayController
 * @package App\Api\Controllers
 */
class TweetPlayController extends BaseController
{
    /**
     * 该动态播放次数 +1
     * @param int $tweet_id 动态id
     * @param $user
     * @return bool
     */
    public function countIncrement($tweet_id, $user)
    {
        try{
            // 动态
            $tweet = Tweet::findOrFail($tweet_id);

            $time = getTime();

            // 开启事务
            DB::beginTransAction();

            // 动态播放次数 +1
            $tweet->increment('browse_times');

            // 判断动态类型是否为视频，收集观看记录
            if(0 === $tweet->type)

                // 如果用户登录，保存用户的观看记录
                if($user){

                    // 将信息存入 zx_user_tweets_history 表
                    UserTweetsHistory::create([
                        'tweet_id'      => $tweet_id,
                        'user_id'       => $user->id,
                        'time_add'      => $time,
                        'time_update'   => $time
                    ]);
                }

            // 所在话题总浏览次数 +1
            Topic::whereHas('hasManyTweetTopic', function($q) use($tweet_id) {
                $q -> where('tweet_id',$tweet_id);
            }) -> increment('forwarding_time');

            // 所在赛事总浏览次数 +1
            Activity::whereHas('hasManyTweetActivity', function($q) use($tweet_id) {
                $q -> where('tweet_id',$tweet_id);
            }) -> ofExpires()
                -> increment('forwarding_time', 1, ['time_update'=>$time]);

            // 提交
            DB::commit();

            // 返回数据
            return 200;

        } catch (ModelNotFoundException $e) {
            // 回滚
            DB::rollBack();
            return false;
        } catch (\Exception $e) {
            // 回滚
            DB::rollBack();
            return false;
        }
    }

    /**
     * 自动播放时，视频播放次数 +1
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function videoIncrement($id)
    {
        try{

            // 判断该动态是否存在
            Tweet::able()->where('type',0)->findOrFail($id);

            // 判断用户是否为登录状态
            $user = Auth::guard('api')->user();

            // 播放次数 +1
            $this -> countIncrement($id,$user);

            return response()->json(['status'=>'ok'],201);

        } catch (ModelNotFoundException $e) {
            return $this->response()->json(['error'=>'bad_request'],403);
        } catch (\Exception $e) {
            return $this->response()->json(['error'=>'bad_request'],403);
        }
    }

}