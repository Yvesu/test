<?php

namespace App\Http\Controllers\NewAdmin\User;

use App\Models\LocalAuth;
use App\Models\User\UserLoginLog;
use App\Models\User\UserLoginLogSum;
use App\Models\User\UserLoginLogSumIp;
use App\Models\User;
use function foo\func;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\DB;

class SupervisoryController extends Controller
{
    //
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 监控页面
     */
    public function index(Request $request)
    {
        try{

            //  今日新增的用户以及男女各占的比例
            $todayNewUser = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->get()->count();
            if($todayNewUser == 0)
            {
                $todayNewUserWomen ='0'.'%';
                $todayNewUserMen ='0'.'%';
            }else{
                $todayNewUserWomen = (round((User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('sex','=',0)->get()->count())/$todayNewUser,2)*100).'%';
                $todayNewUserMen = (round((User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('sex','=',1)->get()->count())/$todayNewUser,2)*100).'%';
            }
            //  绑定手机用户以及比例
            $phoneUserNum = LocalAuth::all()->count();
            if($phoneUserNum == 0){
                $phoneUserNumProportion = '';
            }else{
                $phoneUserNumProportion = (round($phoneUserNum/$userNum,2)*100).'%';
            }

            //  注册用户及男女比例
            $userNum = User::all()->count();
            if($userNum == 0)
            {
                $womenUserNum = '0'.'%';
                $menUserNum = '0'.'%';
            }else{
                $womenUserNum = LocalAuth::WhereHas('hasOneUser',function ($q){
                    $q->select('sex')->where('sex','=',0);
                })->get()->count();
                $menUserNum = LocalAuth::WhereHas('hasOneUser',function ($q){
                    $q->select('sex')->where('sex','=',1);
                })->get()->count();
                $womenUserNum = (round($womenUserNum/$userNum,2)*100).'%';
                $menUserNum = (round($menUserNum/$userNum,2)*100).'%';
            }

            //  活跃度
            //  今日时间戳
            $todayStart = strtotime(date('Y-m-d 0:0:0',time()));
            $todayEnd = strtotime(date('Y-m-d 23:59:59',time()));
            $year = date('Y',time());
            $month = date('m',time());
            $range = $request->get('range',null);
            $time = $request->get('time',0);
            $type = $request->get('type',0);
            if($type == 1){
                if(is_null($range)){
                    if($time == 0){
                        //  今日
                        $todayNum = [];
                        for ($i = 0;$i <24;)
                        {
                            $num = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('login_time','<',$todayStart+(($i+2)*3600))->groupBy('ip')->get()->count();
                            array_push($todayNum,[$i.':00'=>$num]);
                            $i = $i+2;
                        }
                        $activeNum = $todayNum;
                        $activeSum = '';
                        $activeIosSum = '';
                        $activeAndroidSum = '';
                        $activeWebSum = '';
                    }elseif ($time == 1){
                        //  本周
                        $weekStart = mktime(24,0,0,date('m'),date('d')-date('w')+7-7,date('Y'));
                        $weekNum = [];
                        for ($i = 0;$i < 24;)
                        {
                            $num = 0;
                            for($j = 0;$j<7;$j++){
                                $num += UserLoginLog::where('login_time','>',$weekStart+($j*86400)+($i*3600))->where('login_time','>',$weekStart+($j*86400)+(($i+2)*3600))->groupBy('ip')->get()->count();
                            }
                            array_push($weekNum,[$i.':00'=>$num]);
                            $i = $i+2;
                        }
                        $activeNum = $weekNum;
                        $activeSum = '';
                        $activeIosSum = '';
                        $activeAndroidSum = '';
                        $activeWebSum = '';
                    }elseif ($time == 2){
                        //  本月
                        $monthNum = [];


                        for ($i = 0;$i <24; )
                        {
                            $a = $i.':00';
                            $numIp = UserLoginLogSumIp::where('year','=',$year)->where('month','=',$month)->first();
                            if($numIp){
                                $monthNumIp = $numIp->$a;
                                array_push($monthNum,[$i.':00'=>$monthNumIp]);
                            }

                            $i = $i+2;

                        }
                        $activeNum = $monthNum;
                        $activeSum = '';
                        $activeIosSum = '';
                        $activeAndroidSum = '';
                        $activeWebSum = '';
                    }elseif ($time == 3){
                        //  全年
                        $yearNum = [];
                        for ($i = 0;$i <24; )
                        {
                            $num = UserLoginLogSumIp::where('year','=',$year)->get();
                            if($num){
                                $num = $num->sum($i.':00');
                                array_push($yearNum,[$i.':00'=>$num]);
                            }
                            $i = $i+2;

                        }
                        if($yearSum == 0){
                            $yearIosNum = '0%';
                            $yearAndroidNum = '0%';
                            $yearWebNum = '0%';
                        }else{
                            $yearIosNum = (round($yearIosSum/$yearSum)*100).'%';
                            $yearAndroidNum = (round($yearAndroidSum/$yearSum)*100).'%';
                            $yearWebNum = (round($yearWebSum/$yearSum)*100).'%';
                        }
                        $activeNum = $yearNum;
                        $activeSum = '';
                        $activeIosSum = '';
                        $activeAndroidSum = '';
                        $activeWebSum = '';
                    }else{
                        return response()->json(['error'=>'数据不合法'],200);
                    }
                }else{
                    $range = explode('-',$range);
                    $startTime = str_replace(".","-",$range[0]);
                    $endTime = str_replace(".","-",$range[1]);
                    $endTime = $endTime.' 24:00:00';
                    $startTime = strtotime($startTime);
                    $endTime = strtotime($endTime);
                    $dayNum = ceil(($endTime-$startTime)/(60*60*24));
                    $daysNum = [];
                    for ($i = 0;$i < 24;)
                    {
                        $num = 0;
                        for($j = 0;$j<$dayNum;$j++){
                            $num += UserLoginLog::where('login_time','>',$startTime+($j*86400)+($i*3600))->where('login_time','>',$startTime+($j*86400)+(($i+2)*3600))->groupBy('ip')->get()->count();
                        }
                        array_push($daysNum,[$i.':00'=>$num]);
                        $i = $i+2;
                    }

                    $activeNum = $daysNum;
                    $activeSum = '';
                    $activeIosSum = '';
                    $activeAndroidSum = '';
                    $activeWebSum = '';

                }
            }elseif($type == 0){
                if(is_null($range)){
                    if($time == 0){
                        //  今日
                        $todayNum = [];
                        for ($i = 0;$i <24;)
                        {
                            $num = UserLoginLog::where('login_time','>',$todayStart+($i*3600))->where('login_time','<',$todayStart+(($i+2)*3600))->get()->count();
                            array_push($todayNum,[$i.':00'=>$num]);
                            $i = $i+2;
                        }
                        $todayIosNum = UserLoginLog::where('login_time','>',$todayStart)->where('login_time','<',$todayEnd)->where('way','=','ios')->get()->count();
                        $todayAndroidNum = UserLoginLog::where('login_time','>',$todayStart)->where('login_time','<',$todayEnd)->where('way','=','Android')->get()->count();
                        $todayWebNum = UserLoginLog::where('login_time','>',$todayStart)->where('login_time','<',$todayEnd)->where('way','=','Web')->get()->count();
                        $todaysum = array_sum($todayNum);
                        if($todaysum == 0)
                        {
                            $todayIosNum = '0%';
                            $todayAndroidNum = '0%';
                            $todayWebNum = '0%';
                        }else{
                            $todayIosNum = (round($todayIosNum/$todaysum,2)*100).'%';
                            $todayAndroidNum = (round($todayAndroidNum/$todaysum,2)*100).'%';
                            $todayWebNum = (round($todayWebNum/$todaysum,2)*100).'%';
                        }
                        $activeNum = $todayNum;
                        $activeSum = $todaysum;
                        $activeIosSum = $todayIosNum;
                        $activeAndroidSum = $todayAndroidNum;
                        $activeWebSum = $todayWebNum;
                    }elseif ($time == 1){
                        //  本周
                        $weekStart = mktime(24,0,0,date('m'),date('d')-date('w')+7-7,date('Y'));
                        $weekNum = [];
                        for ($i = 0;$i < 24;)
                        {
                            $num = 0;
                            for($j = 0;$j<7;$j++){
                                $num += UserLoginLog::where('login_time','>',$weekStart+($j*86400)+($i*3600))->where('login_time','>',$weekStart+($j*86400)+(($i+2)*3600))->get()->count();
                            }
                            array_push($weekNum,[$i.':00'=>$num]);
                            $i = $i+2;
                        }
                        $weekSum = array_sum($weekNum);
                        $weekIosNum = UserLoginLog::where('login_time','>',$weekStart)->where('login_time','=',time())->where('way','=','ios')->get()->count();
                        $weekAndroidNum = UserLoginLog::where('login_time','>',$weekStart)->where('login_time','=',time())->where('way','=','Android')->get()->count();
                        $weekWebNum = UserLoginLog::where('login_time','>',$weekStart)->where('login_time','=',time())->where('way','=','Web')->get()->count();
                        if($weekSum == 0){
                            $weekIosNum = '0%';
                            $weekAndroidNum = '0%';
                            $weekWebNum = '0%';
                        }else{
                            $weekIosNum = (round($weekIosNum/$weekSum,2)*100).'%';
                            $weekAndroidNum = (round($weekAndroidNum/$weekSum,2)*100).'%';
                            $weekWebNum = (round($weekWebNum/$weekSum,2)*100).'%';
                        }
                        $activeNum = $weekNum;
                        $activeSum = $weeksum;
                        $activeIosSum = $weekIosNum;
                        $activeAndroidSum = $weekAndroidNum;
                        $activeWebSum = $weekWebNum;
                    }elseif ($time == 2){
                        //  本月
                        $monthNum = [];
                        $monthIosNum = [];
                        $monthAndroidNum = [];
                        $monthWebNum = [];

                        for ($i = 0;$i <24; )
                        {
                            $a = $i.':00';
                            $iosNum = UserLoginLogSum::where('year','=',$year)->where('month','=',$month)->where('way','=','ios')->first();
                            $AndroidNum = UserLoginLogSum::where('year','=',$year)->where('month','=',$month)->where('way','=','Android')->first();
                            $WebNum = UserLoginLogSum::where('year','=',$year)->where('month','=',$month)->where('way','=','Web')->first();
                            if($iosNum){
                                $iosNum = $iosNum->$a;
                                array_push($monthIosNum,$iosNum);
                            }
                            if($AndroidNum){
                                $AndroidNum = $AndroidNum->$a;
                                array_push($monthAndroidNum,$AndroidNum);
                            }
                            if($WebNum){
                                $WebNum = $WebNum->$a;
                                array_push($monthWebNum,$WebNum);
                            }
                            $num = $iosNum + $AndroidNum + $WebNum;
                            array_push($monthNum,[$i.':00'=>$num]);
                            $i = $i+2;

                        }
                        $monthSum = array_sum($monthNum);
                        $monthIosNum = array_sum($monthIosNum);
                        $monthAndroidNum = array_sum($monthAndroidNum);
                        $monthWebNum = array_sum($monthWebNum);
                        if($monthSum == 0)
                        {
                            $monthIosNum = '0%';
                            $monthAndroidNum = '0%';
                            $monthWebNum = '0%';
                        }else{
                            $monthIosNum = (round($monthIosNum/$monthSum,2)*100).'%';
                            $monthAndroidNum = (round($monthAndroidNum/$monthSum,2)*100).'%';
                            $monthWebNum = (round($monthWebNum/$monthSum,2)*100).'%';
                        }
                        $activeNum = $monthNum;
                        $activeSum = $monthsum;
                        $activeIosSum = $monthIosNum;
                        $activeAndroidSum = $monthAndroidNum;
                        $activeWebSum = $monthWebNum;
                    }elseif ($time == 3){
                        //  全年
                        $yearNum = [];
                        $yearIosSum = [];
                        $yearAndroidSum = [];
                        $yearWebSum = [];
                        for ($i = 0;$i <24; )
                        {
                            $num = UserLoginLogSum::where('year','=',$year)->get();
                            if($num){
                                $num = $num->sum($i.':00');
                                array_push($yearNum,[$i.':00'=>$num]);
                            }
                            $yearIosNum = UserLoginLogSum::where('year','=',$year)->where('wey','=','ios')->get();
                            if($yearIosNum){
                                $yearIosNum = $yearIosNum->sum($i.':00');
                                array_push($yearIosSum,$yearIosNum);
                            }
                            $yearAndroidNum = UserLoginLogSum::where('year','=',$year)->where('wey','=','Android')->get();
                            if($yearAndroidNum){
                                $yearAndroidNum = $yearIosNum->sum($i.':00');
                                array_push($yearAndroidSum,$yearAndroidNum);
                            }
                            $yearWebNum = UserLoginLogSum::where('year','=',$year)->where('wey','=','Web')->get();
                            if($yearWebNum){
                                $yearWebNum = $yearWebNum->sum($i.':00');
                                array_push($yearWebSum,$yearWebNum);
                            }
                            $i = $i+2;

                        }
                        $yearSum = array_sum($yearNum);
                        $yearIosSum = array_sum($yearIosSum);
                        $yearAndroidSum = array_sum($yearAndroidSum);
                        $yearWebSum = array_sum($yearWebSum);
                        if($yearSum == 0){
                            $yearIosNum = '0%';
                            $yearAndroidNum = '0%';
                            $yearWebNum = '0%';
                        }else{
                            $yearIosNum = (round($yearIosSum/$yearSum)*100).'%';
                            $yearAndroidNum = (round($yearAndroidSum/$yearSum)*100).'%';
                            $yearWebNum = (round($yearWebSum/$yearSum)*100).'%';
                        }
                        $activeNum = $yearNum;
                        $activeSum = $yearsum;
                        $activeIosSum = $yearIosNum;
                        $activeAndroidSum = $yearAndroidNum;
                        $activeWebSum = $yearWebNum;
                    }else{
                        return response()->json(['error'=>'数据不合法'],200);
                    }
                }else{
                    $range = explode('-',$range);
                    $startTime = str_replace(".","-",$range[0]);
                    $endTime = str_replace(".","-",$range[1]);
                    $endTime = $endTime.' 24:00:00';
                    $startTime = strtotime($startTime);
                    $endTime = strtotime($endTime);
                    $dayNum = ceil(($endTime-$startTime)/(60*60*24));
                    $daysNum = [];
                    for ($i = 0;$i < 24;)
                    {
                        $num = 0;
                        for($j = 0;$j<$dayNum;$j++){
                            $num += UserLoginLog::where('login_time','>',$startTime+($j*86400)+($i*3600))->where('login_time','>',$weekStart+($j*86400)+(($i+2)*3600))->get()->count();
                        }
                        array_push($daysNum,[$i.':00'=>$num]);
                        $i = $i+2;
                    }
                    $daysSum = array_sum($daysNum);
                    $daysIosNum = UserLoginLog::where('login_time','>',$startTime)->where('login_time','=',time())->where('way','=','ios')->get()->count();
                    $daysAndroidNum = UserLoginLog::where('login_time','>',$startTime)->where('login_time','=',time())->where('way','=','Android')->get()->count();
                    $daysWebNum = UserLoginLog::where('login_time','>',$startTime)->where('login_time','=',time())->where('way','=','Web')->get()->count();
                    if($daysSum == 0){
                        $daysIosNum = '0%';
                        $daysAndroidNum = '0%';
                        $daysWebNum = '0%';
                    }else{
                        $daysIosNum = (round($daysIosNum/$daysSum,2)*100).'%';
                        $daysAndroidNum = (round($daysAndroidNum/$daysSum,2)*100).'%';
                        $daysWebNum = (round($daysWebNum/$daysSum,2)*100).'%';
                    }
                    $activeNum = $daysNum;
                    $activeSum = $daysum;
                    $activeIosSum = $daysIosNum;
                    $activeAndroidSum = $daysAndroidNum;
                    $activeWebSum = $daysWebNum;

                }
            }else{
                return response()->json(['error'=>'数据不合法'],200);
            }



            //  用户占比
            $userSum = User::all()->count();
            //  有作品的用户 以及占比
            $createUserNum = User::where('work_count','>',0)->get()->count();
            $createUserNumProportion = (round($createUserNum/$userSum,2)*100).'%';
            //  机构数量及占比
            $organizationNum = User::where('verify','=',2)->get()->count();
            $organizationNumProportion = (round($organizationNum/$userSum,2)*100).'%';
            //  vip数量及占比
            $vipNum = User::where('is_vip','>',0)->get()->count();
            $vipNumProportion = (round($vipNum/$userSum,2)*100).'%';
            //  认证用户及占比
            $verifyNum = User::where('verify','=',1)->get()->count();
            $verifyNumProportion = (round($verifyNum/$userSum,2)*100).'%';
            //  普通用户及占比
            $generalUserNum = User::where('is_vip','=',0)->get()->count();
            $generalUserNumProportion = (round($generalUserNum/$userSum,2)*100).'%';

            return response()->json(['phoneUserNum'=>$phoneUserNum,'phoneUserNumProportion'=>$phoneUserNumProportion,'todayNewUser'=>$todayNewUser,'todayNewUserWomen'=>$todayNewUserWomen,'todayNewUserMen'=>$todayNewUserMen,'userNum'=>$userNum,'womenUserNum'=>$womenUserNum,'menUserNum'=>$menUserNum,'activeNum'=>$activeNum,'activeSum'=>$activeSum,'activeIosSum'=>$activeIosSum,'activeAndroidSum'=>$activeAndroidSum,'activeWebSum'=>$activeWebSum,'createUserNum'=>$createUserNum,'createUserNumProportion'=>$createUserNumProportion,'organizationNum'=>$organizationNum,'organizationNumProportion'=>$organizationNumProportion,'vipNum'=>$vipNum,'vipNumProportion'=>$vipNumProportion,'verifyNum'=>$verifyNum,'verifyNumProportion'=>$verifyNumProportion,'generalUserNum'=>$generalUserNum,'generalUserNumProportion'=>$generalUserNumProportion],200);



        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }
}
