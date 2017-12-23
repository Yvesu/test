<?php

namespace App\Console\Commands;

use App\Models\Mark;
use App\Models\Tweet;
use App\Models\TweetMark;
use App\Facades\CloudStorage;
use App\Models\User;
use Illuminate\Console\Command;

class MarkTweet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mark:tweet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $willMark = TweetMark::where('active',1)->take(2)->get();

        if (!$willMark->count()){
            die();
        }

        $arrs = $willMark->toArray();

        foreach ($arrs as $arr) {
            //        //获取动态
            $tweet = Tweet::find($arr['tweet_id']);

//        //视频地址
            $tweet_url = $tweet->video;

//        //获取水印
            $mark = Mark::where('active', 1)->find($arr['mark_id']);

            $mark_url = $mark->mark_content;

            $id = $arr['tweet_id'];

            $noti = 'http://www.goobird.com/api/notification';

            $user = User::find($tweet->user_id);

            $nickname = $user->nickname;

            CloudStorage::Mark($tweet_url, $mark_url, $id,$noti,$nickname);

        }

    }

}
