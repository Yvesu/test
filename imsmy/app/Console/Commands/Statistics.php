<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use DB;
use App\Facades\CommandLog;

/**
 * 每天统计一次，绘制图表使用
 * Class Statistics
 * @package App\Console\Commands
 */
class Statistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Statistics';

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
        // 定时任务专用日志调取
        CommandLog::write('event', 'Statistics Start');
//        \Log::info('Statistics Start');
//        $this->statisticsChannel();
//        $this->statisticsTopic();
//        \Log::info('Statistics End');

    }

    // 目前基本没用频道的统计，暂时停用
//    private function statisticsChannel()
//    {
//        $data = [];
//        $channels = Channel::all();
//        $time = Carbon::today();
//        if (asset($channels)) {
//           foreach ($channels as $channel) {
//               $data[] = [
//                   'channel_id'      => $channel->id,
//                   'forwarding_time' => $channel->forwarding_time,
//                   'comment_time'    => $channel->comment_time,
//                   'work_count'      => $channel->work_count,
//                   'created_at'      => $time
//               ];
//           }
//           DB::table('statistics_channel')->insert($data);
//        }
//    }

    private function statisticsTopic()
    {
        $data = [];
        $topics = Topic::all();
        $time = Carbon::today();
        if (asset($topics)) {
            foreach ($topics as $topic) {
                $data[] = [
                    'topic_id'        => $topic->id,
                    'forwarding_time' => $topic->forwarding_time,
                    'comment_time'    => $topic->comment_time,
                    'work_count'      => $topic->work_count,
                    'created_at'      => $time
                ];
            }
            DB::table('statistics_topic')->insert($data);
        }
    }

}
