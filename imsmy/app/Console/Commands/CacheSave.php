<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Cache;
use App\Jobs\TweetUserLike;
use App\Models\TweetActivity;
use App\Models\User;
use App\Models\Topic;

class CacheSave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cacheSave';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'save the cache changes';

    /**
     * Execute the console command.
     *
     * 调用队列，将用户缓存中对动态的点赞数据放入队列
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info('CacheSave Start');
        $this->tweetLike();
        $this->tweetActivity();
        $this->user();
        \Log::info('CacheSave End');
    }

    // 调用队列，将用户缓存中对动态的点赞数据放入队列
    private function tweetLike()
    {
        // 取出缓存数据
        $like_quque = Cache::pull('tweet_like_quque');

        if($like_quque) {

            foreach($like_quque as $key => $value) {

                $tweet_like = json_decode($value);

                dispatch(new TweetUserLike($tweet_like));
            }
        }
    }

    // 将缓存中的数据更新到数据表中
    private function tweetActivity()
    {
        $tweet_activity = Cache::pull('tweet_activity');

        foreach($tweet_activity as $value) {
            TweetActivity::findOrFail($value -> id) -> update([
                'like_count'    => $value -> like_count,
                'time_update'   => getTime()
            ]);
        }
    }

    // 将缓存中 user 表的数据更新到数据表中
    private function user()
    {
        $ids = Cache::pull('user_change');

        foreach($ids as $id) {

            // 获取更新后的缓存数据
            $cache = Cache::pull('user_'.$id);

            $data = User::findOrFail($id);

            $fields = [
                'nickname',
                'avatar',
                'hash_avatar',
                'video_avatar',
                'sex',
                'cover',
                'verify',
                'verify_info',
                'honor',
                'signature',
                'background',
                'location',
                'location_id',
                'nearby_id',
                'birthday',
                'phone_model',
                'phone_serial',
                'phone_sdk_int',
                'umeng_device_token',
                'xmpp',
                'advertisement',
                'status',
                'stranger_comment',
                'stranger_at',
                'stranger_private_letter',
                'location_recommend',
                'search_phone',
                'new_message_comment',
                'new_message_fans',
                'new_message_like',
                'fans_count',
                'new_fans_count',
                'follow_count',
                'work_count',
                'retweet_count',
                'trophy_count',
                'collection_count',
                'like_count',
                'topics_count',
                'last_ip',
                'last_token',
                'updated_at',
            ];

            foreach($fields as $field) {
                $data -> $field = $cache -> $field;
            }

            $data -> save();
        }
    }
}
