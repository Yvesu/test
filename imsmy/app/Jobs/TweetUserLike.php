<?php

namespace App\Jobs;

use App\Models\Tweet;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;
use App\Models\TweetLike;
use App\Models\Notification;
use App\Models\Topic;
use App\Models\TweetActivity;
use DB;

/**
 * 用户点赞 添加或取消 一系列操作
 * Class TweetUserLike
 * @package App\Jobs
 */
class TweetUserLike implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $like;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($like)
    {
        $this -> like = $like;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $like = $this -> like;

            // 如果该点赞为增加
            if(1 == $like -> type) {

                // 将数据存入tweet_like 表
                $tweet_like = TweetLike::create([
                    'user_id'           => $like -> user_id,
                    'tweet_id'          => $like -> tweet_id,
                    'created_at'        => $like -> time,
                    'updated_at'        => $like -> time
                ]);

                $user_to = User::findOrFail($like -> notice_user_id, ['new_message_like']);

                // 开启事务
                DB::beginTransaction();

                // 判断是否开启了点赞提醒
                if(1 === $user_to -> new_message_like){

                    // 将数据存入noticecation表
                    Notification::create([
                        'user_id'           => $like -> user_id,
                        'notice_user_id'    => $like -> notice_user_id,
                        'type'              => 1,
                        'type_id'           => $like -> tweet_id,
                        'created_at'        => $like -> time,
                        'updated_at'        => $like -> time
                    ]);
                }

                // 该动态的总点赞量 +1
                Tweet::findOrFail($like -> notice_user_id) -> increment('like_count');

                // 该动态作者的总点赞量 +1
                User::findOrFail($like -> notice_user_id) -> increment('like_count');

                // 该动态参与的所有话题的点赞量 +1
                Topic::whereHas('hasManyTweetTopic', function($q) use($tweet_like) {
                    $q -> where('tweet_id', $tweet_like -> tweet_id);
                }) -> increment('like_count');

                $now = getTime();

                // 还在参赛期间内赛事点赞 +1
                TweetActivity::whereHas('belongsToActivity', function($q) use($now) {
                    $q -> where('expires', '>', $now);
                })
                    -> where('tweet_id', $tweet_like -> tweet_id)
                    -> increment('like_count', 1, ['time_update' => $now]);
            } else {

            }

            DB::commit();
        }catch(ModelNotFoundException $e){
            DB::rollBack();
        } catch (\Exception $e){
            DB::rollBack();
        }
    }
}
