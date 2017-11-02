<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
        Commands\Statistics::class,
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
        // $schedule->command('inspire')
        //          ->hourly();
//        $schedule->command('statistics')
//            ->dailyAt('19:12');

        $schedule->command('statistics')
            ->daily();

        $schedule->call(function () {

            /**
             * 日任务
            **/
            //统计关键词
            $key_word = Keywords::orderBy('id','asc')->get();
            $count_pv = [];
            //查出缓存中的关键词访问次数PV
            foreach ($key_word as $k=>$v){
                if( Redis::get('KEYWORD:EXIST_PV:'.$v->keyword)  != null){
                    $count_pv[$v->keyword] = Redis::get('KEYWORD:EXIST_PV:'.$v->keyword);
                }
            }

            //查出缓存中的有效访问次数 IP
             $count_ip = [];
            foreach ($key_word as $k=>$v){
                if (count(Redis::lrange('VALID_HOT_WORD:EXIST'.$v->keyword,0,-1)) != 0 && count(Redis::lrange('VALID_HOT_WORD:EXIST'.$v->keyword,0,-1)) != null){
                    $count_ip[$v->keyword] = count(Redis::lrange('VALID_HOT_WORD:EXIST'.$v->keyword,0,-1));
                }
            }

            //依据缓存从数据库中取出关键词集合
            foreach ($count_pv as $k=>$v){
               $sql_key= Keywords::where('keyword','=',$k)->orderBy('id','asc')->get();
            }

            //依据缓存从数据库中查处关键词被搜索次数
            $count_day=[];
             $count_day_ip = [];
             $count_sum_ip =[];
            foreach ($sql_key as $k=>$v){
                $count_day[$v->keyword] = $v->count_week_pv;
                $count_day_ip[$v->keyword] = $v->count_week_ip;
                $count_sum_ip[$v->keyword] = $v->count_sum_ip;
            }

            //统计周累计IP
            $count_week_ip = [];
            foreach ($count_day_ip as $k=>$v){
                foreach ($count_ip  as $j=>$i){
                    if ($k == $j){
                        $count_week_ip[$k] = $v + $i;
                    }
                }
            }

            //统计周累计 IP
             $count_week= [];
            foreach ($count_day as $k=>$v){
                foreach ($count_pv  as $j=>$i){
                    if ($k == $j){
                        $count_week[$k] = $v + $i;
                    }
                }
            }

            //有效总数  IP
            $count_sum_ip = [];
            foreach ($count_sum_ip as $k=>$v){
                foreach ($count_ip  as $j=>$i){
                    if ($k == $j){
                        $count_sum_ip[$k] = $v + $i;
                    }
                }
            }

            //日统计  PV
            foreach ($count_pv as $k=>$v){
              Keywords::where('keyword','=',$k)->update(['count_day_pv'=>$v]);
            }

            //周统计  PV
            foreach ($count_week as $k=>$v){
                Keywords::where('keyword','=',$k)->update(['count_week_pv'=>$v,'count_sum_pv'=>$v]);
            }

            //有效日统计  IP
            foreach ($count_ip as $k=>$v){
                Keywords::where('keyword','=',$k)->update(['count_day_ip'=>$v]);
            }

            //有效周统计  IP
            foreach ($count_week_ip as $k=>$v){
                Keywords::where('keyword','=',$k)->update(['count_week_ip'=>$v,'count_sum_pv'=>$v]);
            }

            //有效总统计
            foreach ($count_sum_ip as $k=>$v){
                Keywords::where('keyword','=',$k)->update(['count_sum_ip'=>$v,'update_at'=>time()]);
            }



            /**
             * 数据库不存在关键词时
             */
            //查询出缓存中的陌生词
                    $key_word_noexist= Redis::lrange('HOTSEARCH:LIST:NO_EXIST',0,-1);
                    if ($key_word_noexist){
                        // 统计   PV
                        $count_noexist_pv = [];
                        foreach ($key_word_noexist as $k=>$v){
                            $count_noexist_pv[$v] = Redis::get('KEYWORD:NOEXIST_PV:'.$v);
                        }

                        //查询出敏感词
                        $sensitive_word = \DB::table('sensitive_word')->get();
                        $sensitive_words = [];
                        foreach ($sensitive_word as $v){
                            $sensitive_words[] = $v->sensitive_word;
                        }
                        //查询存在的陌生词
                        $noexist_word = \DB::table('noexist_word')->get();
                        $noexist_words = [];
                        $count_sum_pv = [];
                        $count_sum_ip = [];
                        foreach ($noexist_word as $v){
                            $noexist_words[] = $v->keyword;
                            $count_sum_pv[ $v->keyword ] =  $v->count_sum_pv;
                            $count_sum_ip[ $v->keyword ] =  $v->count_sum_ip;
                        }

                        $count_noexist_pv_sum = [];
                        foreach ($count_noexist_pv as $k=>$v){
                            foreach ($count_sum_pv as $j=>$i){
                                if ($k==$j){
                                    $count_noexist_pv_sum[$k] = $v+$i;
                                }
                            }
                        }

                        //填充到数据库
                        foreach ($count_noexist_pv as $k=>$v){
                            if (!in_array($k, $noexist_words)) {
                                if ($v > 200) {
                                    if (!in_array($k, $sensitive_words)) {
                                        \DB::table('noexist_word')->insert(['keyword' => $k, 'count_sum_pv' => $v]);
                                    }
                                }
                            } else {
                                if ($v > 200) {
                                    if (!in_array($k, $sensitive_words)) {
                                        foreach ($count_noexist_pv_sum as $j=>$i){
                                            if ($k=$j){
                                                \DB::table('noexist_word')->update(['count_sum_pv' => $i]);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        //有效统计 IP
                        foreach ($key_word_noexist as $k=>$v){   //VALID_HOT_WORD:NO_EXIST啊实打实多
                            $count_noexist_ip[$v] = count( Redis::lrange('VALID_HOT_WORD:NO_EXIST'.$v,0,-1));
                        }


                        $count_noexist_ip_sum = [];
                        foreach ($count_noexist_ip as $k=>$v){
                            foreach ($count_sum_ip as $j=>$i){
                                if ($k==$j){
                                    $count_noexist_ip_sum[$k] = $v+$i;
                                }
                            }
                        }

                        //填充到数据库
                        foreach ($count_noexist_ip as $k=>$v){
                            if (!in_array($k, $noexist_words)) {
                                if ($v > 100) {
                                    if (!in_array($k, $sensitive_words)) {
                                        \DB::table('noexist_word')->insert(['keyword' => $k, 'count_sum_ip' => $v]);
                                    }
                                }
                            }else {
                                if ($v > 100) {
                                    if (!in_array($k, $sensitive_words)) {
                                        foreach ($count_noexist_ip_sum as $j=>$i){
                                            if ($k=$j){
                                                \DB::table('noexist_word')->update(['count_sum_ip' => $i]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
        })->daily();



        $schedule->call(function () {
            /**
             *   周任务
             **/

            $keyword_week = Keywords::get();
            $count_week_pv = [];
            $count_week_ip = [];
            //本周统计
            foreach ($keyword_week as $k=>$v){
                $count_week_pv[$v->keyword] = $v->count_week_pv;
                $count_week_ip[$v->keyword] = $v->count_week_ip;
            }
            //写入上周统计 PV
            foreach ($count_week_pv as $k=>$v){
                \DB::table('keywords')->where('keyword','=',$k)->update(['count_prev_week_pv'=>$v,'count_week_pv'=>0]);
            }

            //写入上周统计 IP
            foreach ($count_week_ip as $k=>$v){
                \DB::table('keywords')->where('keyword','=',$k)->update(['count_prev_week_ip'=>$v,'count_week_ip'=>0]);
            }
        })->weekly();



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
    }
}
