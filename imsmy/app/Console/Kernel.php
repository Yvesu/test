<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
         Commands\Inspire::class,
        Commands\Statistics::class,
        Commands\CountUser::class,

        \App\Console\Commands\Qicheck::class,
        \App\Console\Commands\CacheSensitiveWord::class,
        \App\Console\Commands\CacheKeywords::class,
        \App\Console\Commands\NoExitWord::class,

//        Commands\Ranking::class,
//        Commands\XmppFile::class,
//        Commands\CacheSave::class,
//        Commands\OmnipayOrder::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //鉴黄检测
        $schedule->command('Qicheck')->everyMinute();

        //敏感词
        $schedule->command('CacheSensitiveWord')->everyMinute();//->daily();

        //关键词
        $schedule->command('CacheKeywords:make')->everyMinute();//->daily();

        //生词
        $schedule->command('NoExitWord')->everyMinute(); //->hourly();

        // $schedule->command('inspire')
        //          ->hourly();
//        $schedule->command('statistics')
//            ->dailyAt('19:12');

        $schedule->command('statistics')
            ->daily();

//        $schedule->command('channel:ranking')
//            ->dailyAt('1:00');

	// 每分钟检测一下队列的监听事件  TODO 待开启
//        $schedule -> command('queue:work')
//            -> everyMinute();

        // TODO 待开启
//        $schedule->command('xmppFile')
//            -> dailyAt('1:00');

        // 每天清空过期支付订单  TODO 待开启
//        $schedule->command('omnipayOrder')
//            -> dailyAt('2:12');

//        $schedule -> command('cacheSave')
//            -> everyFiveMinutes();

        // 数据库备份    TODO 待开启
//        $schedule->command('backup:run --only-db')->cron('0 */4 * * * *');
//        $schedule->command('backup:clean')->daily()->at('00:10');
//        $schedule->command('backup:monitor')->daily()->at('10:00');


        //  统计每年月登录用户量

        $schedule->command('CountUser')->everyMinute();

    }
}
