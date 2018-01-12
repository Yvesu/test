<?php

namespace App\Console\Commands;

use App\Facades\CloudStorage;
use App\Models\Tweet;
use App\Models\TweetTrasf;
use Illuminate\Console\Command;

class TweetTrans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweet:trans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TweetTrans';

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
        $tweet_arr = TweetTrasf::where('active','0')->take(5)->pluck('tweet_id');

        $tweet_arr = $tweet_arr -> all();

        if (!$tweet_arr){
            die();
        }

        $tweets = Tweet::whereIn('id',$tweet_arr)->get(['id','video','shot_width_height']);

        array_map([$this,'trans'],$tweets->toArray());
    }

    public function trans($tweet)
    {

        $shot_width_height = $tweet['shot_width_height'];
        $width = substr($shot_width_height,0,strrpos($shot_width_height,'*'));
        $height = substr($shot_width_height,strrpos($shot_width_height,'*')+1,strlen($shot_width_height));
        $url = CloudStorage::downloadUrl($tweet['video']);
        $file_url = ltrim(parse_url($url)['path'], '/');
        $notice = 'http://www.goobird.com/api/notification/trans';
        $tid = $tweet['id'];
        CloudStorage::transcoding_tweet($tid,'hivideo-video',$file_url,$width,$height,1,$notice);
    }
}
