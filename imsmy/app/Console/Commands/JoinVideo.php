<?php

namespace App\Console\Commands;

use App\Facades\CloudStorage;
use App\Models\TweetJoin;
use Illuminate\Console\Command;

class JoinVideo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Join:video';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Join video';

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
        $tweets = TweetJoin::where('active','0')->take(5)->get(['tweet_id','join_id']);
        if (!$tweets->count()){
            die;
        }
        array_map([$this,'join'],$tweets->toArray());
    }

    public function join($tweet)
    {
        $json_id = $tweet['join_id'];
        $id = $tweet['tweet_id'];
        $notice = 'http://www.goobird.com/api/notification/join';
        CloudStorage::joint($id,$json_id,$notice);
    }
}
