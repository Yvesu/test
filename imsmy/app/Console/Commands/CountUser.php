<?php

namespace App\Console\Commands;

use App\Models\User\UserLoginLogSum;
use App\Models\User\UserLoginLogSumIp;
use Illuminate\Console\Command;
use App\Models\User\UserLoginLog;
use Illuminate\Support\Facades\DB;

class CountUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CountUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计用户登录量';

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
        //
        $year = date('Y',time());
        $month = date('m',time());
        $re1 =  UserLoginLogSum::where('year','=',$year)->first();
        DB::beginTransaction();
        if($re1)
        {
            $re2 = UserLoginLogSum::where('month','=',$month)->first();
            if($re2)
            {
                $todayStart = strtotime(date('Y-m-d 0:0:0',time()));
                //  今日0:00-1:59
                for ($i = 0;$i <24;)
                {
                    $a = $i.':00';
                    $numIos = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('way','=','ios')->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                    $numAndroid = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('way','=','Android')->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                    $numWeb = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('way','=','web')->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                    $sumIos = UserLoginLogSum::where('year','=',$year)->where('way','=','ios')->where('month','=',$month)->first();
                    $sumIos -> $a += $numIos;
                    $sumIos -> time_add = time();
                    $sumIos -> save();
                    $sumAndroid = UserLoginLogSum::where('year','=',$year)->where('way','=','Android')->where('month','=',$month)->first();
                    $sumAndroid -> $a += $numAndroid;
                    $sumAndroid ->time_add = time();
                    $sumAndroid -> save();
                    $sumWeb = UserLoginLogSum::where('year','=',$year)->where('way','=','Web')->where('month','=',$month)->first();
                    $sumWeb -> $a += $numWeb;
                    $sumWeb ->time_add = time();
                    $sumWeb -> save();
                    $numIp = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('login_time','<',$todayStart+(($i+2)*3600))->groupBy('ip')->get()->count();
                    $sumIp = UserLoginLogSumIp::where('year','=',$year)->where('month','=',$month)->first();
                    $sumIp -> $a += $numIp;
                    $sumIp -> time_add = time();
                    $sumIp -> save();
                    $i = $i+2;
                }
            }else{
                $newMonthIp = new UserLoginLogSumIp;
                $newMonthIp -> month = $month;
                $newMonthIp -> year = $year;
                $newMonthIp -> time_add = time();
                $newMonthIp -> save();
                $newMonth = new UserLoginLogSum;
                $newMonth -> month = $month;
                $newMonth -> year = $month;
                $newMonth -> time_add = time();
                $newMonth -> save();
                $todayStart = strtotime(date('Y-m-d 0:0:0',time()));
                //  今日0:00-1:59
                for ($i = 0;$i <24;)
                {
                    $a = $i.':00';
                    $numIos = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('way','=','ios')->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                    $numAndroid = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('way','=','Android')->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                    $numWeb = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('way','=','web')->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                    $sumIos = UserLoginLogSum::where('year','=',$year)->where('way','=','ios')->where('month','=',$month)->first();
                    $sumIos -> $a += $numIos;
                    $sumIos ->time_add = time();
                    $sumIos -> save();
                    $sumAndroid = UserLoginLogSum::where('year','=',$year)->where('way','=','Android')->where('month','=',$month)->first();
                    $sumAndroid -> $a += $numAndroid;
                    $sumAndorid -> time_add = time();
                    $sumAndroid -> save();
                    $sumWeb = UserLoginLogSum::where('year','=',$year)->where('way','=','Web')->where('month','=',$month)->first();
                    $sumWeb -> $a += $numWeb;
                    $sumWeb ->time_add = time();
                    $sumWeb -> save();
                    $numIp = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('login_time','<',$todayStart+(($i+2)*3600))->groupBy('ip')->get()->count();
                    $sumIp = UserLoginLogSumIp::where('year','=',$year)->where('month','=',$month)->first();
                    $sumIp -> $a += $numIp;
                    $sumIp ->time_add = time();
                    $sumIp -> save();
                    $i = $i+2;
                }
            }

        }else{
            $newYearIp = new UserLoginLogSumIp;
            $newYearIp -> year = $year;
            $newYearIp -> month = $month;
            $newYearIp -> save();
            $newYear = new UserLoginLogSum;
            $newYear -> year = $year;
            $newYear -> month = $month;
            $newYear -> save();
            $todayStart = strtotime(date('Y-m-d 0:0:0',time()));
            //  今日0:00-1:59
            for ($i = 0;$i <24;)
            {
                $a = $i.':00';
                $numIos = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('way','=','ios')->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                $numAndroid = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('way','=','Android')->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                $numWeb = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('way','=','web')->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                $sumIos = UserLoginLogSum::where('year','=',$year)->where('way','=','ios')->where('month','=',$month)->first();
                $sumIos -> $a += $numIos;
                $sumIos -> save();
                $sumAndroid = UserLoginLogSum::where('year','=',$year)->where('way','=','Android')->where('month','=',$month)->first();
                $sumAndroid -> $a += $numAndroid;
                $sumAndroid -> save();
                $sumWeb = UserLoginLogSum::where('year','=',$year)->where('way','=','Web')->where('month','=',$month)->first();
                $sumWeb -> $a += $numWeb;
                $sumWeb -> save();
                $numIp = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('login_time','<',$todayStart+(($i+2)*3600))->groupBy('ip')->get()->count();
                $sumIp = UserLoginLogSumIp::where('year','=',$year)->where('month','=',$month)->first();
                $sumIp -> $a += $numIp;
                $sumIp -> save();
                $i = $i+2;
            }
        }
        DB::commit();
        DB::rollBack();

    }
}
