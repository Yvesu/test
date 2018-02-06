<?php

namespace App\Http\Controllers\NewWeb\User;

use App\Http\Middleware\Filmfest;
use App\Models\Filmfest\JoinUniversity;
use App\Models\FilmfestFilmType;
use App\Models\Filmfests;
use App\Models\FilmfestsProductions;
use App\Models\FilmfestUser\FilmfestUserFilmfestUser;
use App\Models\FilmfestUser\FilmfestUserReviewChildLog;
use App\Models\FilmfestUser\FilmfestUserReviewLog;
use App\Models\FilmfestUser\FilmfestUserRole;
use App\Models\FilmfestUser\FilmfestUserRoleGroup;
use App\Models\FilmfestUser\FilmfestUserRoleRoleGroup;
use App\Models\FilmfestUser\FilmfestUserUserGroup;
use App\Models\FilmfestUser\FilmfestUserUserGroupRoleGroup;
use App\Models\FilmfestUser\FilmfestUserUserRoleGroup;
use App\Models\FilmfestUser\FilmfestUserUserUserGroup;
use App\Models\FilmfestUser\UserFilmfestUserRole;
use App\Models\PrivateLetter;
use App\Models\Subscription;
use App\Models\Tweet;
use App\Models\TweetProduction;
use App\Models\User;
use function foo\func;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;

class FilmfestController extends Controller
{
    //
    private $paginate = 12;

    public function index(Request $request)
    {
        try{
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据异常'],200);
            }
            //  应届作品
            $currentProductionNum = TweetProduction::select('id')
                ->whereHas('filmfest',function ($q) use($id){
                    $q->where('filmfests.id','=',$id);
                })->get()->count();
//            //  历届作品
//            $noCurrentProductionNum = TweetProduction::select('id')->where('is_current','0')
//                ->whereHas('filmfest',function ($q) use($id){
//                    $q->where('filmfests.id','=',$id);
//                })->get()->count();
            //  参与院校
            $joinUniversityNum = TweetProduction::select('id')->where('is_current','1')
                ->whereHas('filmfest',function ($q) use($id){
                    $q->where('filmfests.id','=',$id);
                })->count('join_university_id');
            //  历届参与院校
//            $historyUniversityNum = JoinUniversity::select('id')
//                ->whereHas('filmfests',function ($q) use($id){
//                    $q->where('filmfests.id','=',$id);
//                })->get()->count();

            //  已查看
            $alreadyWatch = FilmfestUserReviewLog::where('filmfest_id',$id)->where('watch_num','>',0)->groupBy('production_id')->get()->count();

            /**
             * 分类占比
             */
            //  总片数
            $sumNum = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q){
                $q->where('status','<>',2)->where('status','<>',4);
            })
                ->whereHas('filmfest',function ($q) use($id){
                    $q->where('filmfests.id','=',$id);
                })->get()->count();
            //  私有数量
            $privateNum = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q){
                $q->where('status','>',2)->where('status','<>',4);
            })->whereHas('filmfest',function ($q) use($id){
                $q->where('filmfests.id','=',$id);
            })->whereHas('tweet',function ($q) {
                $q->where('visible', '=', 2);
            })->get()->count();
            //  私有占比
            $privateProportion = (round($privateNum/$sumNum,2)*100).'%';
            //  公有数量
            $publicNum = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q){
                $q->where('status','>',2)->where('status','<>',4);
            })->whereHas('filmfest',function ($q) use($id){
                $q->where('filmfests.id','=',$id);
            })->whereHas('tweet',function ($q) {
                $q->where('visible','<>',2);
            })->get()->count();
            //  公有占比
            $publicProportion = (round($publicNum/$sumNum,2)*100).'%';
            //  已查看占比
            $alreadyWatchProportion = (round($alreadyWatch/$sumNum,2)*100).'%';
            //  倒计时
            $filmfest = Filmfests::find($id);
            $nowTime = time();
            $stopSubmitTime = $filmfest->submit_end_time;
            $stopSelectTime = $filmfest->check_time;
            $stopCheckTime = $filmfest->check_again_time;
            $stopCheckAgainTime = $filmfest->enter_time;
            $endTime = $filmfest->time_end;
            if ($nowTime<$stopSubmitTime){
                $countDownTime = $stopSubmitTime-$nowTime;
                $des = '距离投片戒指倒计时';
            }else{
                if($nowTime<$stopSelectTime){
                    $countDownTime = $stopSelectTime-$nowTime;
                    $des = '距离海选截止倒计时';
                }else{
                    if($nowTime<$stopCheckTime){
                        $countDownTime = $stopCheckTime-$nowTime;
                        $des = '距离复选截止倒计时';
                    }else{
                        if($nowTime<$stopCheckAgainTime){
                            $countDownTime = $stopCheckAgainTime-$nowTime;
                            $des = '距离入围名单公示倒计时';
                        }else{
                            if($nowTime<$endTime){
                                $countDownTime = $endTime-$nowTime;
                                $des = '距离颁奖倒计时';
                            }else{
                                $countDownTime = null;
                                $des = '已结束';
                            }
                        }
                    }
                }
            }
            if(is_null($countDownTime)){
                $countDownTime = '--:--:--';
            }else{
                $days = floor($countDownTime/86400);
                $hours = floor(($countDownTime-86400*$days)/3600);
                $minutes = floor((($countDownTime-86400*$days)-3600*$hours)/60);
                $seconds = floor(((($countDownTime-86400*$days)-3600*$hours)-60*$minutes)/60);
                $countDownTime = $days.'天  '.$hours.':'.$minutes.':'.$seconds;
            }
            $countDown = [
                'countDownTime'=>$countDownTime,
                'des'=>$des,
            ];


            //  该电影节的所有单元
            $units = FilmfestFilmType::whereHas('filmFests',function ($q)use($id){
                $q->where('filmfests.id','=',$id);
            })->get();
            $finallyData = [];
            foreach ($units as $k => $v)
            {
                $content = $v->name;
                $data1 = $this->baseData($id,$content,$visible = 1);
                $data2 = $this->baseData($id,$content,$visible = 2);
                $data3 = $this->baseData($id,$content,$visible = 3);
                if($sumNum == 0){
                    $data4 = '0%';
                }else{
                    $data4 = (round($data3/$sumNum,2)*100);
                }
                $tempData = [
                    'name'=>$content,
                    'num'=>$data3,
                    'privateNum'=>$data1,
                    'publicNum'=>$data2,
                    'proportion'=>$data4,
                ];
                array_push($finallyData,$tempData);
            }


            $data = [
                'currentProductionNum'=>$currentProductionNum,
//                'noCurrentProductionNum'=>$noCurrentProductionNum,
                'joinUniversityNum'=>$joinUniversityNum,
                'publicProportion'=>$publicProportion,
                'privateProportion'=>$privateProportion,
                'countDownTime'=>$countDown,
//                'historyUniversityNum'=>$historyUniversityNum,
                'alredyWatchProportion' => $alreadyWatchProportion,
            ];

            return response()->json(['data'=>$data,'data2'=>$finallyData],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function deadTime(Request $request)
    {
        try{
            $id = $request->get('id');
            //  倒计时
            $filmfest = Filmfests::find($id);
            $nowTime = time();
            $stopSubmitTime = $filmfest->submit_end_time;
            $stopSelectTime = $filmfest->check_time;
            $stopCheckTime = $filmfest->check_again_time;
            $stopCheckAgainTime = $filmfest->enter_time;
            $endTime = $filmfest->time_end;
            if ($nowTime<$stopSubmitTime){
                $countDownTime = $stopSubmitTime-$nowTime;
                $des = '距离投片戒指倒计时';
            }else{
                if($nowTime<$stopSelectTime){
                    $countDownTime = $stopSelectTime-$nowTime;
                    $des = '距离海选截止倒计时';
                }else{
                    if($nowTime<$stopCheckTime){
                        $countDownTime = $stopCheckTime-$nowTime;
                        $des = '距离复选截止倒计时';
                    }else{
                        if($nowTime<$stopCheckAgainTime){
                            $countDownTime = $stopCheckAgainTime-$nowTime;
                            $des = '距离入围名单公示倒计时';
                        }else{
                            if($nowTime<$endTime){
                                $countDownTime = $endTime-$nowTime;
                                $des = '距离颁奖倒计时';
                            }else{
                                $countDownTime = null;
                                $des = '已结束';
                            }
                        }
                    }
                }
            }
            if(is_null($countDownTime)){
                $countDownTime = '--:--:--';
            }else{
                $days = floor($countDownTime/86400);
                $hours = floor(($countDownTime-86400*$days)/3600);
                $minutes = floor((($countDownTime-86400*$days)-3600*$hours)/60);
                $seconds = floor((($countDownTime-86400*$days)-3600*$hours)-60*$minutes);
                $countDownTime = $days.'天  '.$hours.':'.$minutes.':'.$seconds;
            }
            $countDown = [
                'countDownTime'=>$countDownTime,
                'des'=>$des,
            ];
            return response()->json(['data'=>$countDown],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }

    public function baseData($filmfest_id,$content,$visible)
    {
        if($visible==1){
            $data = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q){
                $q->where('status','<>',2)->where('status','<>',4);
            })
                ->whereHas('filmfest',function ($q) use($filmfest_id){
                    $q->where('filmfests.id','=',$filmfest_id);
                })
                ->whereHas('filmfestFilmType',function ($q) use($content){
                    $q->where('name',$content);
                })->whereHas('tweet',function ($q){
                    $q->where('visible','=',2);
                })->get()->count();
        }elseif($visible == 2){
            $data = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q){
                $q->where('status','<>',2)->where('status','<>',4);
            })
                ->whereHas('filmfest',function ($q) use($filmfest_id){
                    $q->where('filmfests.id','=',$filmfest_id);
                })
                ->whereHas('filmfestFilmType',function ($q) use($content){
                    $q->where('name',$content);
                })->whereHas('tweet',function ($q){
                    $q->where('visible','<>',2);
                })->get()->count();
        }else{
            $data = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q){
                $q->where('status','<>',2)->where('status','<>',4);
            })
                ->whereHas('filmfest',function ($q) use($filmfest_id){
                    $q->where('filmfests.id','=',$filmfest_id);
                })
                ->whereHas('filmfestFilmType',function ($q) use($content){
                    $q->where('name',$content);
                })->get()->count();
        }
        return $data;
    }


    public function table(Request $request)
    {
        try{
            $type = $request -> get('type',1);
            $range = $request -> get('range',null);
            $filmfest_id = $request -> get('id',1);
            $time = $request -> get('time','1');
            if($type == 1){
                if($range){
                    //  开始时间
                    $startDay = trim(explode(' - ',$range)[0]);
                    //  结束时间
                    $endDay = trim(explode(' - ',$range)[1]);
                    //  开始时间时间戳
                    $startTime = strtotime($startDay.' 00:00:00');
                    //  结束时间时间戳
                    $endTime = strtotime($endDay.'24:00:00');
                    //  总时间秒数
                    $time = $endTime - $startTime;
                    //  总天数
                    $sumDays = (int)floor($time/86400);
                    //  每份天数
                    $everyDays = ((int)floor($sumDays/12))==0?1:(int)floor($sumDays/12);
                    //  开始时间数组
                    $startDayArray = explode('-',$startDay);
                    //  结束时间数组
                    $endDayArray = explode('-',$endDay);
                    //  下标数组
                    $date = [];
                    //  长月数组
                    $longMonth =  [1,3,5,7,8,10,12];
                    //  短月数组
                    $shortMonth = [4,6,9,11];
                    for($i=0;$i<12;$i++)
                    {
                        //  临时天数
                        $tempDay = (int)($startDayArray[2])+$everyDays;
                        if($tempDay>31 && in_array((int)$startDayArray[1],$longMonth)){          //  临时天数大于31天，且开始为长月
                            $endDayArray[2] = $tempDay-31;
                            if((int)$startDayArray[1]==12){                                      //  当前月是否为12月
                                $endDayArray[1] = 1;
                                $endDayArray[0] = (int)$startDayArray[0] +1;
                            }else{
                                $endDayArray[1] = (int)$startDayArray[1] + 1;
                            }
                        }elseif ($tempDay<=31 && in_array((int)$startDayArray[1],$longMonth)){   //  临时天数小于31天，且当前月为长月
                            $endDayArray[2] = $tempDay;
                            $endDayArray[1] = (int)$startDayArray[1];
                        }elseif ((int)$startDayArray[1]==2){                                     //  如果是2月
                            //  如果是闰年
                            if((((int)$startDayArray[0]%4==0 && (int)$startDayArray[0]%100 != 0) || (int)$startDayArray[0]%400 == 0)){
                                if($tempDay>29){
                                    $endDayArray[2] = $tempDay-29;
                                    $endDayArray[1] = 3;
                                }else{
                                    $endDayArray[2] = $tempDay;
                                    $endDayArray[1] = 2;
                                }
                            }else{                                                          //  不是闰年
                                if($tempDay>28){
                                    $endDayArray[2] = $tempDay-28;
                                    $endDayArray[1] = 3;
                                }else{
                                    $endDayArray[2] = $tempDay;
                                    $endDayArray[1] = 2;
                                }
                            }
                        }elseif ($tempDay>30 && in_array((int)$startDayArray[1],$shortMonth)){   //  如果是小月
                            $endDayArray[2] = $tempDay-30;
                            $endDayArray[1] = (int)$startDayArray[1] + 1;
                        }elseif ($tempDay<=30 && in_array((int)$startDayArray[1],$shortMonth)){
                            $endDayArray[2] = $tempDay;
                            $endDayArray[1] = (int)$startDayArray[1];
                        }
                        $date[$i] = (int)$startDayArray[0].'.'.(int)$startDayArray[1].'.'.(int)$startDayArray[2].'.'.' - '.(int)$endDayArray[0].'.'.(int)$endDayArray[1].'.'.(int)$endDayArray[2];
                        $startDayArray[0] = $endDayArray[0];
                        $startDayArray[1] = $endDayArray[1];
                        $startDayArray[2] = $endDayArray[2];
                    }

                    $data = [];
                    foreach ($date as $k => $v){
                        $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                            $q->where('filmfests.id','=',$filmfest_id);
                        })->where('time_add','>',$startTime+($time/12)*$k)
                            ->where('time_add','<',$startTime+($time/12)*($k+1))
                            ->whereHas('filmfestProduction',function ($q){
                                $q->where('status','<>',2);
                            })
                            ->get()->count();

                        array_push($data,['XX'=>$v,'YY'=>$tempData]);
                    }

                }else{
                    $data = [];
                    if($time == 1){
                        $startTime = strtotime('today');
                        for ($i = 0;$i < 12 ;$i++){
                            $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                $q->where('filmfests.id','=',$filmfest_id);
                            })->where('time_add','>',$startTime+(7200)*$i)
                                ->where('time_add','<',$startTime+(7200)*($i+1))
                                ->whereHas('filmfestProduction',function ($q){
                                    $q->where('status','<>',2);
                                })
                                ->get()->count();
                            array_push($data,['XX'=>$i.':00','YY'=>$tempData]);
                        }
                    }elseif ($time == 2){
                        $timestamp = time();
                        $startTime = strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp)));
                        for ($i = 0;$i < 7 ;$i++){
                            $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                $q->where('filmfests.id','=',$filmfest_id);
                            })->where('time_add','>',$startTime+(86400)*$i)
                                ->where('time_add','<',$startTime+(86400)*($i+1))
                                ->whereHas('filmfestProduction',function ($q){
                                    $q->where('status','<>',2);
                                })
                                ->get()->count();
                            switch ($i){
                                case 0:
                                    $xx = '周一';
                                    break;
                                case 1:
                                    $xx = '周二';
                                    break;
                                case 2:
                                    $xx = '周三';
                                    break;
                                case 3:
                                    $xx = '周四';
                                    break;
                                case 4:
                                    $xx = '周五';
                                    break;
                                case 5:
                                    $xx = '周六';
                                    break;
                                case 6:
                                    $xx = '周日';
                                    break;
                                default:
                                    break;
                            }
                            array_push($data,['XX'=>$xx,'YY'=>$tempData]);
                        }
                    }elseif ($time == 3){
                        $month = date('m',time());
                        $year = date('Y',time());
                        if(($year%4==0 && $year%100 != 0)||$year%400 == 0){
                            if($month==2){
                                $days = 29;
                            }elseif(in_array($month,[1,3,5,7,8,10,12])){
                                $days = 31;
                            }else{
                                $days = 30;
                                }
                        }else{
                            if($month==2){
                                $days = 28;
                            }elseif(in_array($month,[1,3,5,7,8,10,12])){
                                $days = 31;
                            }else{
                                $days = 30;
                                }
                        }
                        $startTime = mktime(0, 0, 0, date('m') , 1, date('Y'));
                        for ($i = 1;$i <= $days ;$i++){
                            $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                $q->where('filmfests.id','=',$filmfest_id);
                            })->where('time_add','>',$startTime+(86400)*($i-1))
                                ->where('time_add','<',$startTime+(86400)*($i))
                                ->whereHas('filmfestProduction',function ($q){
                                    $q->where('status','<>',2);
                                })
                                ->get()->count();
                            array_push($data,['XX'=>$i.'日','YY'=>$tempData]);
                        }
                    }elseif ($time == 4){
                        $startTime = mktime(0, 0, 0, 1, 1, date('Y'));
                        for ($i = 1;$i<=12;$i++)
                        {
                            if($i==12){
                                $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                    $q->where('filmfests.id','=',$filmfest_id);
                                })->where('time_add','>',mktime(0, 0, 0, $i, 1, date('Y')))
                                    ->where('time_add','<',mktime(0, 0, 0, 1, 1, date('Y')+1))
                                    ->whereHas('filmfestProduction',function ($q){
                                        $q->where('status','<>',2);
                                    })
                                    ->get()->count();
                            }else{
                                $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                    $q->where('filmfests.id','=',$filmfest_id);
                                })->where('time_add','>',mktime(0, 0, 0, $i, 1, date('Y')))
                                    ->where('time_add','<',mktime(0, 0, 0, $i+1, 1, date('Y')))
                                    ->whereHas('filmfestProduction',function ($q){
                                        $q->where('status','<>',2);
                                    })
                                    ->get()->count();
                            }
                            array_push($data,['XX'=>$i.'月','YY'=>$tempData]);
                        }
                    }
                }

            }else{
                if($range){
                    //  开始时间
                    $startDay = explode(' - ',$range)[0];
                    //  结束时间
                    $endDay = explode(' - ',$range)[1];
                    //  开始时间时间戳
                    $startTime = strtotime($startDay.' 00:00:00');
                    //  结束时间时间戳
                    $endTime = strtotime($endDay.'24:00:00');
                    //  总时间秒数
                    $time = $endTime - $startTime;
                    //  总天数
                    $sumDays = (int)floor($time/86400);
                    //  每份天数
                    $everyDays = ((int)floor($sumDays/12))==0?1:(int)floor($sumDays/12);
                    //  开始时间数组
                    $startDayArray = explode('-',$startDay);
                    //  结束时间数组
                    $endDayArray = explode('-',$endDay);
                    //  下标数组
                    $date = [];
                    //  长月数组
                    $longMonth =  [1,3,5,7,8,10,12];
                    //  短月数组
                    $shortMonth = [4,6,9,11];
                    for($i=0;$i<12;$i++)
                    {
                        //  临时天数
                        $endDayArray[0] = (int)$startDayArray[0];
                        $tempDay = (int)($startDayArray[2])+$everyDays;
                        if($tempDay>31 && in_array((int)$startDayArray[1],$longMonth)){          //  临时天数大于31天，且开始为长月
                            $endDayArray[2] = $tempDay-31;
                            if((int)$startDayArray[1]==12){                                      //  当前月是否为12月
                                $endDayArray[1] = 1;
                                $endDayArray[0] = (int)$startDayArray[0] +1;
                            }else{
                                $endDayArray[1] = (int)$startDayArray[1] + 1;
                            }
                        }elseif ($tempDay<=31 && in_array((int)$startDayArray[1],$longMonth)){   //  临时天数小于31天，且当前月为长月
                            $endDayArray[2] = $tempDay;
                            $endDayArray[1] = (int)$startDayArray[1];
                        }elseif ((int)$startDayArray[1]==2){                                     //  如果是2月
                            //  如果是闰年
                            if((((int)$startDayArray[0]%4==0 && (int)$startDayArray[0]%100 != 0) || (int)$startDayArray[0]%400 == 0)){
                                if($tempDay>29){
                                    $endDayArray[2] = $tempDay-29;
                                    $endDayArray[1] = 3;
                                }else{
                                    $endDayArray[2] = $tempDay;
                                    $endDayArray[1] = 2;
                                }
                            }else{                                                          //  不是闰年
                                if($tempDay>28){
                                    $endDayArray[2] = $tempDay-28;
                                    $endDayArray[1] = 3;
                                }else{
                                    $endDayArray[2] = $tempDay;
                                    $endDayArray[1] = 2;
                                }
                            }
                        }elseif ($tempDay>30 && in_array((int)$startDayArray[1],$shortMonth)){   //  如果是小月
                            $endDayArray[2] = $tempDay-30;
                            $endDayArray[1] = (int)$startDayArray[1] + 1;
                        }elseif ($tempDay<=30 && in_array((int)$startDayArray[1],$shortMonth)){
                            $endDayArray[2] = $tempDay;
                            $endDayArray[1] = (int)$startDayArray[1] + 1;
                        }
                        $date[$i] = (int)$startDayArray[0].'.'.(int)$startDayArray[1].'.'.(int)$startDayArray[2].' - '.(int)$endDayArray[0].'.'.(int)$endDayArray[1].'.'.(int)$endDayArray[2];
                        $startDayArray[0] = $endDayArray[0];
                        $startDayArray[1] = $endDayArray[1];
                        $startDayArray[2] = $endDayArray[2];
                    }

                    $data = [];

                    $allProduction = TweetProduction::whereHas('filmfestProduction',function ($q){
                        $q->where('status','<>',2);
                    })->get();
                    foreach ($date as $k => $v)
                    {
                        $tempData = 0;
                        foreach ($allProduction as $item => $value)
                        {
                            if($value->filmfestProduction()->first()){
                                $id = $value->id;
                                $date1 = $startTime+($time/12)*$k;
                                $tempData1 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date1)->count('like_count');
                                $date2 = $startTime+($time/12)*$k;
                                $tempData2 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date2)->count('like_count');
                                $tempData = ($tempData2 - $tempData1);
                            }else{
                                $tempData += 0;
                            }

                        }
                        array_push($data,['XX'=>$v,'YY'=>$tempData]);
                    }
                }else{
                    $data = [];
                    if($time == 1){
                        $allProduction = TweetProduction::whereHas('filmfestProduction',function ($q){
                            $q->where('status','<>',2);
                        })->get();
                        $startTime = strtotime('today');
                        for ($i = 0;$i < 12 ;$i++){
                            $tempData = 0;
                            foreach ($allProduction as $item => $value)
                            {
                                if($value->filmfestProduction()->first()){
                                    $id = $value->id;
                                    $date1 = $startTime+(7200)*$i;
                                    $tempData1 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date1)->count('like_count');
                                    $date2 = $startTime+(7200)*($i+1);
                                    $tempData2 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date2)->count('like_count');
                                    $tempData = ($tempData2 - $tempData1);
                                }else{
                                    $tempData += 0;
                                }
                            }
                            array_push($data,['XX'=>$i.':00','YY'=>$tempData]);
                        }
                    }elseif ($time == 2){
                        $allProduction = TweetProduction::whereHas('filmfestProduction',function ($q){
                            $q->where('status','<>',2);
                        })->get();

                        $timestamp = time();
                        $startTime = strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp)));
                        for ($i = 0;$i < 7 ;$i++){
                            $tempData = 0;
                            foreach ($allProduction as $item => $value)
                            {
                                if($value->filmfestProduction()->first()){
                                    $id = $value->id;
                                    $date1 = $startTime+(86400)*$i;
                                    $tempData1 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date1)->count('like_count');
                                    $date2 = $startTime+(86400)*($i+1);
                                    $tempData2 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date2)->count('like_count');
                                    $tempData = ($tempData2 - $tempData1);
                                }else{
                                    $tempData += 0;
                                }
                            }
                            switch ($i){
                                case 0:
                                    $xx = '周一';
                                    break;
                                case 1:
                                    $xx = '周二';
                                    break;
                                case 2:
                                    $xx = '周三';
                                    break;
                                case 3:
                                    $xx = '周四';
                                    break;
                                case 4:
                                    $xx = '周五';
                                    break;
                                case 5:
                                    $xx = '周六';
                                    break;
                                case 6:
                                    $xx = '周日';
                                    break;
                                default:
                                    break;
                            }
                            array_push($data,['XX'=>$xx,'YY'=>$tempData]);
                        }
                    }elseif ($time == 3){
                        $month = date('m',time());
                        $year = date('Y',time());
                        if(($year%4==0 && $year%100 != 0)||$year%400 == 0){
                            if($month==2){
                                $days = 29;
                            }elseif(in_array($month,[1,3,5,7,8,10,12])){
                                $days = 31;
                            }else{
                                $days = 30;
                            }
                        }else{
                            if($month==2){
                                $days = 28;
                            }elseif(in_array($month,[1,3,5,7,8,10,12])){
                                $days = 31;
                            }else{
                                $days = 30;
                            }
                        }
                        $startTime = mktime(0, 0, 0, date('m'), 1, date('Y'));
                        $allProduction = TweetProduction::whereHas('filmfestProduction',function ($q){
                            $q->where('status','<>',2);
                        })->get();
                        for ($i = 1;$i <= $days ;$i++){
                            $tempData = 0;
                            foreach ($allProduction as $item => $value)
                            {
                                if($value->filmfestProduction()->first()){
                                    $date1 = $startTime+(86400)*($i-1);
                                    $tempData1 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date1)->count('like_count');
                                    $date2 = $startTime+(86400)*($i);
                                    $tempData2 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date2)->count('like_count');
                                    $tempData = ($tempData2 - $tempData1);
                                }else{
                                    $tempData += 0;
                                }
                            }
                            array_push($data,['XX'=>$i.'日','YY'=>$tempData]);
                        }
                    }elseif ($time == 4){
                        $allProduction = TweetProduction::whereHas('filmfestProduction',function ($q){
                            $q->where('status','<>',2);
                        })->get();
                        for ($i = 1;$i<=12;$i++)
                        {
                            if($i==12){
                                $date1 = mktime(0, 0, 0, $i, 1, date('Y'));
                                $date2 = mktime(0, 0, 0, 1, 1, date('Y')+1);
                                $tempData = 0;
                                foreach ($allProduction as $item => $value)
                                {
                                    if($value->filmfestProduction()->first()){
                                        $tempData1 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date1)->count('like_count');
                                        $tempData2 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date2)->count('like_count');
                                        $tempData = ($tempData2 - $tempData1);
                                    }else{
                                        $tempData += 0;
                                    }

                                }
                            }else{
                                $tempData = 0;
                                $date1 = mktime(0, 0, 0, $i, 1, date('Y'));
                                $date2 = mktime(0, 0, 0, $i+1, 1, date('Y'));
                                foreach ($allProduction as $item => $value)
                                {
                                    if($value->filmfestProduction()->first()){
                                        $tempData1 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date1)->count('like_count');
                                        $tempData2 = FilmfestsProductions::where('filmfests_id',$filmfest_id)->where('time_update','<',$date2)->count('like_count');
                                        $tempData = ($tempData2 - $tempData1);
                                    }else{
                                        $tempData += 0;
                                    }
                                }
                            }
                            array_push($data,['XX'=>$i.'月','YY'=>$tempData]);
                        }
                    }
                }
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function universityTop(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $school = JoinUniversity::whereHas('filmfests',function ($q) use($filmfest_id){
                $q->where('filmfests.id',$filmfest_id);
            })->get();
            $tempData = [];
            if($school->count()>0){
                foreach($school as $k => $v)
                {
                    $count = 0;
                    if($v->filmfests()->first()){
                        if($v->filmfests()->first()->tweetProduction()->first()){
                            foreach($v->filmfests()->first()->tweetProduction()->get() as $kk => $vv)
                            {
                                if($vv){
                                    $count += $vv->tweet->like_count;
                                }else{
                                    $count += 0;
                                }

                            }
                            $tempData = [
                                $v->id => $count
                            ];
                        }else{
                            continue;
                        }
                    }else {
                        continue;
                    }
                }
            }
            arsort($tempData);
            $tempData = array_slice($tempData,0,9,true);
            $data = [];
            foreach ($tempData as $k => $v)
            {
                $tempData2 = [
                    'name'=>JoinUniversity::where('id',$k)->pluck('name')[0],
                    'count'=>$v
                ];
                array_push($data,$tempData2);
            }

            return response()->json(['data'=>$data]);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function university(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $school = JoinUniversity::whereHas('filmfests',function ($q) use($filmfest_id){
                $q->where('filmfests.id',$filmfest_id);
            })->get();
            $tempData = [];
            if($school->count()>0){
                foreach($school as $k => $v)
                {
                    $count = 0;
                    if($v->filmfests()->first()){
                        if($v->filmfests()->first()->tweetProduction()->first()){
                            foreach($v->filmfests()->first()->tweetProduction()->get() as $kk => $vv)
                            {
                                if($vv){
                                    $count += $vv->tweet->like_count;
                                }else{
                                    $count += 0;
                                }

                            }
                            $tempData = [
                                $v->id => $count
                            ];
                        }else{
                            continue;
                        }
                    }else {
                        continue;
                    }
                }
            }

            arsort($tempData);
            $data = [];
            foreach ($tempData as $k => $v)
            {
                $tempData2 = [
                    'name'=>JoinUniversity::where('id',$k)->pluck('name')[0],
                    'count'=>$v
                ];
                array_push($data,$tempData2);
            }

            return response()->json(['data'=>$data]);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function workIndex(Request $request)
    {
        try{
            //  电影节id
            $filmfest_id = $request->get('id');
            //  用户id
            $user = \Auth::guard('api')->user()->id;
            //  用户组id
            $userGroupId = $request->get('user_group_id',null);
            if(is_null($userGroupId)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $userGroupName = FilmfestUserUserGroup::find($userGroupId)->name;
            //  该用户组下所有角色组
            $roleGroup = FilmfestUserRoleGroup::where('filmfest_id',$filmfest_id)->where('status',1)
                ->whereHas('userGroup',function ($q) use($userGroupId){
                    $q->where('filmfest_user_user_group.id',$userGroupId);
                })->get();
            //  用来放置顶部数据
            $topData = [
                'group'=>[],
            ];
            $sumPeopleNum = 0;
            //  判断角色组是否为空，不为空则取出所有角色组下人的数量
            if($roleGroup->count()>0){
                foreach ($roleGroup as $k => $v){
                    if((int)($v->status) === 0){
                        continue;
                    }
                    $roles = $v->role()->first()?$v->role:'';
                    $tempAuditions = 0;
                    if($roles){
                        foreach($roles as $kk => $vv)
                        {
                            if($vv->filmfest_id == $filmfest_id)
                            {
                                $tempAuditions += $vv->user()->get()->count();
                            }else{
                                continue;
                            }
                        }
                        array_push($topData['group'],['des'=>$v->name,'num'=>$tempAuditions,]);
                        if(strstr($v->name,'冻结')){
                           continue;
                        }else{
                            $sumPeopleNum += $tempAuditions;
                        }
                    }else{
                        continue;
                    }
                }

            }else{
                $topData = [];
            }
            //  头像
            $avatar = User::find($user)->avatar;
            $topData['avatar']=$avatar;
            $topData['userGroupName']=$userGroupName;
            array_push($topData,['des'=>'成员','sumPeopleNum'=>$sumPeopleNum]);

            //  主体数据
            $type = (int)($request->get('type',0));
            $page = (int)($request->get('page',1));
            $mainData = [];
            if($type===0){
                $adminUser = User::whereHas('filmfestUserRoleGroup',function ($q)use($filmfest_id){
                    $q->where('name','not like','发起者')->where('filmfest_id',$filmfest_id)->where('status',1);
                })->whereHas('filmfestUserGroup',function ($q) use($userGroupId){
                    $q->where('filmfest_user_user_group.id',$userGroupId);
                })
                    ->limit($page*($this->paginate))
                    ->orderBy('id')
                    ->get();
                if($adminUser->count()>0){
                    foreach ($adminUser as $k => $v)
                    {
                        $tempUserId = $v->id;
                        $tempAvatar= $v->avatar;
                        $tempNickName = $v->nickname;
                        $tempPhone = $v->hasOneLocalAuth()->first()?$v->hasOneLocalAuth->username:'';
                        $tempRole = FilmfestUserRole::where('filmfest_id',$filmfest_id)
                            ->where('status',1)
                            ->whereHas('user',function ($q)use($tempUserId){
                                $q->where('user.id',$tempUserId);
                            })->get();
                        $role = [];
                        if($tempRole->count()>0){
                            foreach($tempRole as $kk => $vv)
                            {
                                array_push($role,['role'=>$vv->role_name]);
                            }
                        }

                        $tempUser = $v->id;

                        $num = FilmfestUserReviewLog::where('filmfest_id',$filmfest_id)
                            ->where('user_id',$tempUser)->count('production_id');

                        $undeterminedNum = FilmfestUserReviewLog::where('status','=',2)
                            ->where('filmfest_id',$filmfest_id)->where('user_id',$tempUser)->count('production_id');

                        $designateNum = FilmfestUserReviewLog::where('status','=',1)
                            ->where('filmfest_id',$filmfest_id)->where('user_id',$tempUser)->count('production_id');

                        $complete_watch_num = FilmfestUserReviewLog::where('is_complete_watch','=',1)
                            ->where('filmfest_id',$filmfest_id)->where('user_id',$tempUser)->count('production_id');

                        $again_watch_num = FilmfestUserReviewLog::where('watch_num','>',1)
                            ->where('filmfest_id',$filmfest_id)->where('user_id',$tempUser)->count('production_id');

                        if($num == 0){
                            $complete_watch_num_proportion = '0%';
                            $again_watch_num_proportion = '0%';
                        }else{

                            $complete_watch_num_proportion = ((round($complete_watch_num/$num,2))*100).'%';

                            $again_watch_num_proportion = ((round($again_watch_num/$num,2))*100).'%';
                        }

                        $tempMainData = [
                            'id'=>$tempUserId,
                            'avatar'=>$tempAvatar,
                            'nickname'=>$tempNickName,
                            'phone'=>$tempPhone,
                            'role'=>$role,
                            'num'=>$num,
                            'undeterminedNum'=>$undeterminedNum,
                            'designateNum'=>$designateNum,
                            'complete_watch_num_proportion'=>$complete_watch_num_proportion,
                            'again_watch_num_proportion'=>$again_watch_num_proportion,
                        ];

                        array_push($mainData,$tempMainData);
                    }
                    return response()->json(['topData'=>$topData,'mainData'=>$mainData]);
                }else{
                    return response()->json(['message'=>'还没有人是这个分组的管理员，请您块去添加'],200);
                }
            }else{
                if($roleGroup->count()>0){
                    $adminUser = User::whereHas('filmfestUserRoleGroup',function ($q)use($type){
                        $q->where('filmfest_user_role_group.id',$type)->where('status',1);
                    })->whereHas('filmfestUserGroup',function ($q)use($userGroupId){
                        $q->where('filmfest_user_user_group.id',$userGroupId);
                    })->limit($page*($this->paginate))
                        ->orderBy('id')
                        ->get();
                    if($adminUser->count()>0){
                        foreach ($adminUser as $k => $v)
                        {
                            $tempUserId = $v->id;
                            $tempAvatar= $v->avatar;
                            $tempNickName = $v->nickname;
                            $tempPhone = $v->hasOneLocalAuth()->first()?$v->hasOneLocalAuth->usesrname:'';
                            $tempRole = FilmfestUserRole::where('filmfest_id',$filmfest_id)
                                ->where('status',1)
                                ->whereHas('user',function ($q)use($tempUserId){
                                    $q->where('user.id',$tempUserId);
                                })->whereHas('group',function ($q)use($type){
                                    $q->where('filmfest_user_role_group.id',$type);
                                })->get();
                            $role = [];
                            if($tempRole->count()>0){
                                foreach($tempRole as $kk => $vv)
                                {
                                    array_push($role,['role'=>$vv->role_name]);
                                }
                            }

                            $tempUser = $v->id;

                            $num = FilmfestUserReviewLog::where('filmfest_id',$filmfest_id)
                                ->where('user_id',$tempUser)->count('production_id');

                            $undeterminedNum = FilmfestUserReviewLog::where('status','=',2)
                                ->where('filmfest_id',$filmfest_id)->where('user_id',$tempUser)->count('production_id');

                            $designateNum = FilmfestUserReviewLog::where('status','=',1)
                                ->where('filmfest_id',$filmfest_id)->where('user_id',$tempUser)->count('production_id');

                            $complete_watch_num = FilmfestUserReviewLog::where('is_complete_watch','=',1)
                                ->where('filmfest_id',$filmfest_id)->where('user_id',$tempUser)->count('production_id');

                            $again_watch_num = FilmfestUserReviewLog::where('watch_num','>',1)
                                ->where('filmfest_id',$filmfest_id)->where('user_id',$tempUser)->count('production_id');

                            if($num == 0){
                                $complete_watch_num_proportion = '0%';
                                $again_watch_num_proportion = '0%';
                            }else{

                                $complete_watch_num_proportion = ((round($complete_watch_num/$num,2))*100).'%';

                                $again_watch_num_proportion = ((round($again_watch_num/$num,2))*100).'%';
                            }

                            $tempMainData = [
                                'id'=>$tempUserId,
                                'avatar'=>$tempAvatar,
                                'nickname'=>$tempNickName,
                                'phone'=>$tempPhone,
                                'role'=>$role,
                                'num'=>$num,
                                'undeterminedNum'=>$undeterminedNum,
                                'designateNum'=>$designateNum,
                                'complete_watch_num_proportion'=>$complete_watch_num_proportion,
                                'again_watch_num_proportion'=>$again_watch_num_proportion,
                            ];

                            array_push($mainData,$tempMainData);
                        }
                        return response()->json(['topData'=>$topData,'mainData'=>$mainData]);

                    }else{
                        return response()->json(['message'=>'还没有人是这个分组的管理员，请您块去添加'],200);
                    }
                }else{
                    return response()->json(['message'=>'还没有分组']);
                }
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function menu(Request $request)
    {
        $filmfest_id = $request->get('id');
        $user = \Auth::guard('api')->user()->id;
        $is_issue = User::where('id',$user)->whereHas('filmfest_role',function ($q)use($filmfest_id){
            $q->where('role_name','like','%发起%')->where('filmfest_id',$filmfest_id);
        })->first();
        if($is_issue){
            $menu = [
                [
                    'icon'=>'bar-chart',
                    'key'=>'analyse',
                    'des'=>'分析页',
                    'uri'=>'/manage/analyse'
                ],
                [
                    'icon'=>'user',
                    'key'=>'sub1',
                    'des'=>'审片室',
                    'uri'=>'',
                    'children'=>[],
                ],
            ];
            $role = FilmfestUserRole::where('role_name','not like','%冻结%')
                ->where('role_name','not like','%发起%')
                ->where('filmfest_id',$filmfest_id)
                ->where('is_correlation_watch_clips',1)
                ->get();
            if($role->count()>0){
                foreach ($role as $k => $v)
                {
                    $tempData = [
                        'id'=>$v->id,
                        'name'=>$v->des,
                        'icon'=>$v->pass()->first()?$v->pass()->first()->icon:'',
                        'key'=>$v->pass()->first()?$v->pass()->first()->key:'',
                        'uri'=>$v->pass()->first()?$v->pass()->first()->pass:'',
                    ];
                    array_push($menu[1]['children'],$tempData);
                }
            }
            $userGroup = FilmfestUserUserGroup::where('filmfest_id',$filmfest_id)->get();
            if($userGroup->count()>0){
                foreach ($userGroup as $k => $v)
                {
                    $tempData = [
                        'id'=>$v->id,
                        'des'=>$v->name,
                        'uri'=>'/manage/working-team',
                        'key'=>'working-team',
                        'icon'=>'team',
                    ];
                    array_push($menu,$tempData);
                }
            }
            array_push($menu,['des'=>'设置','uri'=>'/manage/set','key'=>'set','icon'=>'setting']);
        }else{
            $menu = [
                [
                    'icon'=>'bar-chart',
                    'key'=>'analyse',
                    'des'=>'分析页',
                    'uri'=>'/manage/analyse'
                ],
                [
                    'icon'=>'user',
                    'key'=>'sub1',
                    'des'=>'审片室',
                    'uri'=>'',
                    'children'=>[],
                ],
            ];
            $role = FilmfestUserRole::where('filmfest_id',$filmfest_id)
                ->where('role_name','not like','%发起%')
                ->where('role_name','not like','%冻结%')
                ->where('status',1)
                ->where('is_correlation_watch_clips',1)
                ->whereHas('user',function ($q)use($user){
                    $q->where('user.id',$user);
                })->get();
            if($role->count()>0){
                foreach ($role as $k => $v)
                {
                    $tempData = [
                        'id'=>$v->id,
                        'name'=>$v->des,
                        'icon'=>$v->pass()->first()?$v->pass()->first()->icon:'',
                        'key'=>$v->pass()->first()?$v->pass()->first()->key:'',
                        'uri'=>$v->pass()->first()?$v->pass()->first()->pass:'',
                    ];
                    array_push($menu[1]['children'],$tempData);
                }
            }
        }

        return response()->json(['data'=>$menu],200);
    }


    public function workType(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $type = $request->get('type',1);
            $userGroupId = $request->get('user_group_id',null);
            if(is_null($userGroupId)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            if($type==1){
                $groups = FilmfestUserRoleGroup::where('name','not like','%发起%')
                    ->where('status',1)
                    ->where('filmfest_id',$filmfest_id)
                    ->whereHas('userGroup',function ($q) use($userGroupId){
                        $q->where('filmfest_user_user_group.id',$userGroupId);
                    })->get();
                $data = [
                    [
                        'id' => 0,
                        'des'=>'全部成员'
                    ]
                ];
                if($groups->count()>0){
                    foreach($groups as $k => $v)
                    {
                        $tempData = [
                            'id'=>$v->id,
                            'des'=>$v->name,
                        ];
                        array_push($data,$tempData);
                    }
                }
            }else {
                $groups = FilmfestUserRoleGroup::where('name', 'not like', '%发起%')
                    ->where('status', 1)
                    ->where('name', 'not like', '%冻结%')
                    ->where('filmfest_id', $filmfest_id)
                    ->whereHas('userGroup', function ($q) use ($userGroupId) {
                        $q->where('filmfest_user_user_group.id', $userGroupId);
                    })->get();
                $data = [];
                if ($groups->count() > 0) {
                    {
                        foreach ($groups as $k => $v) {
                            $tempData = [
                                'id' => $v->id,
                                'des' => $v->name,
                            ];
                            array_push($data, $tempData);
                        }
                    }
                }

            }

            return response()->json(['data'=>$data,'user_group_id'=>$userGroupId],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function addAdmin(Request $request)
    {
        try{
            $filmfest_id =  $request->get('id');
            $type = $request->get('type');
            $group = FilmfestUserRoleGroup::find($type);
            $roles = $group->role()->first()?$group->role:'';
            $tempAuditions = 0;
            if($roles){
                foreach($roles as $kk => $vv)
                {
                    if($vv->filmfest_id == $filmfest_id)
                    {
                        $tempAuditions += $vv->user()->get()->count();
                    }else{
                        continue;
                    }
                }
            }
            $limitNum = $group->num;
            $vacancyNum = $limitNum-$tempAuditions;
            $user = \Auth::guard('api')->user()->id;
            $users = User::find($user)->belongsFromManyUser()->get();
            $data = [];
            $usabelNum = 0;
            if($users->count()>0){
                foreach ($users as $k => $v)
                {
                    if(($v->verify!=0) && ($v->hasOneLocalAuth()->first())){
                        if($v->filmfest_role()->first() && ($v->filmfest_role()->first()->filmfest_id == $filmfest_id)){
                            continue;
                        }else{
                            $user_id = $v->id;
                            $avatar = $v->avatar;
                            $nickName = $v->nickname;
                            $fansNum = $v->hasManySubscriptions()->get()->count();
                            $tempData = [
                                'user_id'=>$user_id,
                                'avatar'=>$avatar,
                                'nickname'=>$nickName,
                                'fansNum'=>$fansNum,
                            ];
                            array_push($data,$tempData);
                            $usabelNum = $usabelNum+1;
                        }

                    }else{
                        continue;
                    }
                }
            }else{
                return response()->json(['message'=>'您还没有关注认证用户，请先关注'],200);
            }

            return response()->json(['limitNum'=>$limitNum,'vacancyNum'=>$vacancyNum,'data'=>$data,'usabelNum'=>$usabelNum,'filmfest_id'=>$filmfest_id],200);

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }


    public function doAdd(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_group_id = $request->get('group_id',null);
            $user_id = \Auth::guard('api')->user()->id;
            $userGroupId = $request->get('user_group_id',null);
            $ids = $request->get('user_id',null);
            $vacancyNum = $request->get('vacancyNum');
            if(is_null($role_group_id) || is_null($userGroupId) || is_null($ids) || is_null($vacancyNum)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = explode('|',$ids);
            if(count($id)>$vacancyNum){
                return response()->json(['message'=>'您添加的用户数量已经超出限制'],200);
            }
            $role = FilmfestUserRole::where('filmfest_id',$filmfest_id)
                ->where('status',1)
                ->whereHas('group',function ($q) use($role_group_id){
                    $q->where('filmfest_user_role_group.id',$role_group_id);
                })->get();
            DB::beginTransaction();
            foreach ($id as $k => $v)
            {
                $is_subscription = Subscription::where('from',$user_id)->where('to',$v)->first();
                if($is_subscription){

                    if($role->count()>0){
                        foreach($role as $kk => $vv)
                        {
                            $newRoleUser = new UserFilmfestUserRole;
                            $newRoleUser -> user_id = $v;
                            $newRoleUser -> role_id = $vv->id;
                            $newRoleUser -> time_add = time();
                            $newRoleUser -> time_update = time();
                            $newRoleUser -> save();
                        }

                        $newUserFilmfest = new FilmfestUserFilmfestUser;
                        $newUserFilmfest -> user_id = $v;
                        $newUserFilmfest -> filmfest_id = $filmfest_id;
                        $newUserFilmfest -> time_add = time();
                        $newUserFilmfest -> time_update = time();
                        $newUserFilmfest -> save();

                        $newUserUserRoleGroup = new FilmfestUserUserRoleGroup;
                        $newUserUserRoleGroup -> user_id = $v;
                        $newUserUserRoleGroup -> role_group_id = $role_group_id;
                        $newUserUserRoleGroup -> time_add = time();
                        $newUserUserRoleGroup -> time_update = time();
                        $newUserUserRoleGroup -> save();


                        $newUserUserGroup = new FilmfestUserUserUserGroup;
                        $newUserUserGroup -> group_id = $userGroupId;
                        $newUserUserGroup -> user_id = $v;
                        $newUserUserGroup -> time_add = time();
                        $newUserUserGroup -> time_update = time();
                        $newUserUserGroup -> save();
                    }else{
                        return response()->json(['message'=>'您目前无可用的角色可以添加'],200);
                    }
                }else{
                    return response()->json(['message'=>'数据不合法']);
                }



            }

            DB::commit();
            return response()->json(['message'=>'success'],200);



        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function adminDetail(Request $request)
    {
        try{
            $id = $request->get('user_id');
            $page = $request->get('page',1);
            $filmfest_id = $request->get('id');
            $user_id = \Auth::guard('api')->user()->id;
            $is_subscription = Subscription::where('from',$user_id)->where('to',$id)->first();
            $is_filmfest = User::select('id')->where('id',$id)->whereHas('filmfest',function ($q) use($filmfest_id){
                $q->where('filmfests.id',$filmfest_id);
            })->first();
            if($is_subscription && $is_filmfest){
                $user = User::find($id);
                $avatar = $user->avatar;
                $nickName = $user->nickname;
                $phone = $user->hasOneLocalAuth()->first()?$user->hasOneLocalAuth:'';
                $verify_info = $user->verify_info;
                $roles = FilmfestUserRole::where('filmfest_id',$filmfest_id)
                    ->where('status',1)
                    ->whereHas('user',function ($q)use($id){
                        $q->where('user.id',$id);
                    })->get();
                $role = [];
                if($roles->count()>0){
                    foreach($roles as $kk => $vv)
                    {
                        array_push($role,['role'=>$vv->role_name]);
                    }
                }
                $num = FilmfestUserReviewLog::where('filmfest_id',$filmfest_id)->where('user_id',$id)
                    ->count('production_id');

                $undeterminedNum = FilmfestUserReviewLog::where('status','=',2)
                    ->where('filmfest_id',$filmfest_id)->where('user_id',$id)->count('production_id');

                $designateNum = FilmfestUserReviewLog::where('status','=',1)
                    ->where('filmfest_id',$filmfest_id)->where('user_id',$id)->count('production_id');

                $complete_watch_num = FilmfestUserReviewLog::where('is_complete_watch','=',1)
                    ->where('filmfest_id',$filmfest_id)->where('user_id',$id)->count('production_id');

                $again_watch_num = FilmfestUserReviewLog::where('watch_num','>',1)
                    ->where('filmfest_id',$filmfest_id)->where('user_id',$id)->count('production_id');

                if($num == 0){
                    $complete_watch_num_proportion = '0%';

                    $again_watch_num_proportion = '0%';
                }else{
                    $complete_watch_num_proportion = ((round($complete_watch_num/$num,2))*100).'%';

                    $again_watch_num_proportion = ((round($again_watch_num/$num,2))*100).'%';
                }


                $logs = FilmfestUserReviewChildLog::where('user_id',$id)->where('filmfest_id',$filmfest_id)
                    ->orderBy('time_add','desc')->get();

                $logNum = $logs ->count();

                $logs = FilmfestUserReviewChildLog::where('user_id',$id)->where('filmfest_id',$filmfest_id)
                    ->orderBy('time_add','desc')->limit($page*(25))->get();

                $log = [];

                foreach ($logs as $k => $v)
                {
                    $time = date('Y年m月d日. H:i',$v->time_add);
                    $content = ($v->doint).'  '.($v->cause);
                    $tempLogData = [
                        'time'=>$time,
                        'content'=>$content,
                    ];
                    array_push($log,$tempLogData);
                }

                $data = [
                    'filmfest_id'=>$filmfest_id,
                    'avatar'=>$avatar,
                    'nickname'=>$nickName,
                    'phone'=>$phone,
                    'role'=>$role,
                    'verify_info'=>$verify_info,
                    'num'=>$num,
                    'undeterminedNum'=>$undeterminedNum,
                    'designateNum'=>$designateNum,
                    'complete_watch_num_proportion'=>$complete_watch_num_proportion,
                    'again_watch_num_proportion'=>$again_watch_num_proportion,
                    'logNum'=>$logNum,
                    'log'=>$log,
                ];

                return response()->json(['data'=>$data],200);
            }else{
                return response()->json(['message'=>'数据不合法'],200);
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }

    public function handelAdmin(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $data = [
                [
                    'label'=>'1',
                    'des'=>'发送私信',
                ],
                [
                    'label'=>'2',
                    'des'=>'更改角色',
                ],
                [
                    'label'=>'3',
                    'des'=>'到处日志',
                ],
                [
                    'label'=>'4',
                    'des'=>'冻结成员',
                ]
            ];
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['message'=>'not_found'],200);
        }
    }

    public function handleAdminUserGroup(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $data = FilmfestUserUserGroup::where('filmfest_id',$filmfest_id)->where('status',1)->get();
            if($data->count()>0){
                $group = [];
                foreach ($data as $k => $v)
                {
                    $tempData = [
                        'id'=>$v->id,
                        'name'=>$v->name,
                    ];
                    array_push($group,$tempData);
                }
            }else{
                return response()->json(['message'=>'您还没有添加用户组'],200);
            }
            return response()->json(['data'=>$group],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function handleAdminRoleGroup(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $user_group = $request->get('user_group',null);
            if(is_null($user_group)){
                return response()->json(['message'=>'数据不合法']);
            }
            $role_group = FilmfestUserRoleGroup::where('filmfest_id',$filmfest_id)
                ->where('status',1)
                ->where('name','not like','%冻结%')
                ->whereHas('userGroup',function ($q) use($user_group){
                    $q->where('filmfest_user_user_group.id',$user_group)->where('status',1);
                })->get();
            if($role_group->count()>0){
                $group = [];
                foreach ($role_group as $k => $v)
                {
                    $tempData = [
                        'id'=>$v->id,
                        'name'=>$v->name,
                    ];
                    array_push($group,$tempData);
                }
            }else{
                return response()->json(['message'=>'您还没有添加角色组'],200);
            }
            return response()->json(['data'=>$group],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function handleAdminRole(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_group = $request->get('role_group',null);
            if(is_null($role_group)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $role = FilmfestUserRole::where('filmfest_id',$filmfest_id)
                ->where('status',1)
                ->whereHas('group',function ($q) use($role_group){
                    $q->where('filmfest_user_role_group.id',$role_group)->where('status',1);
                })->get();
            if($role->count()>0){
                $group = [];
                foreach ($role as $k => $v)
                {
                    $tempData = [
                        'id'=>$v->id,
                        'name'=>$v->role_name,
                    ];
                    array_push($group,$tempData);
                }
            }else{
                return response()->json(['message'=>'您还没有添加角色'],200);
            }
            return response()->json(['data'=>$group],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }

    public function doHandleAdminRole(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $user_id = $request->get('user_id',null);
            $role_id = $request->get('role_id',null);
            $role_group_id = $request->get('role_group_id',null);
            $user_group_id = $request->get('user_group_id',null);
            if(is_null($user_id) || is_null($role_id) || is_null($role_group_id) || is_null($user_group_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $userLog = FilmfestUserReviewLog::where('filmfest_id',$filmfest_id)->where('user_id',$user_id)->first();
            if($userLog){
                return response()->json(['该用户已不可变更角色，只可冻结，冻结后无法恢复！！'],200);
            }
            $oldUserRole = FilmfestUserRole::where('filmfest_id',$filmfest_id)
                ->whereHas('user',function ($q) use($user_id){
                    $q->where('user.id',$user_id);
                })->get();
            $oldUserGroup = FilmfestUserUserGroup::where('filmfest_id',$filmfest_id)
                ->whereHas('user',function ($q) use($user_id){
                    $q->where('user.id',$user_id);
                })->get();
            $oldRoleGroup = FilmfestUserRoleGroup::where('filmfest_id',$filmfest_id)
                ->whereHas('user',function ($q) use($user_id){
                    $q->where('user.id',$user_id);
                })->get();
            DB::beginTransaction();
            if($oldUserRole->count()>0){
                foreach ($oldUserRole as $k => $v)
                {
                    $oldUserRoleId = $v->id;
                    UserFilmfestUserRole::where('user_id',$user_id)->where('role_id',$oldUserRoleId)->delete();
                }
            }
            if($oldUserGroup->count()>0){
                foreach ($oldUserGroup as $k => $v)
                {
                    $oldUserGroupId = $v->id;
                    FilmfestUserUserUserGroup::where('user_id',$user_id)->where('group_id',$oldUserGroupId)->delete();
                }
            }
            if($oldRoleGroup->count()>0){
                foreach ($oldUserGroup as $k => $v)
                {
                    $oldRoleGroupId = $v->id;
                    FilmfestUserUserUserGroup::where('user_id',$user_id)->where('group_id',$oldRoleGroupId)->delete();
                }
            }
            $role_id = explode('|',$role_id);
            $user_group_id = explode('|',$user_group_id);
            $role_group_id = explode('|',$role_group_id);
            foreach($user_group_id as $k => $v)
            {
                $group = FilmfestUserUserGroup::find($v);
                if(($group->filmfest_id)!=$filmfest_id || (int)($group->status) !==1){
                    return response()->json(['message'=>'数据不合法'],200);
                }
                $newUserGroupUser = new FilmfestUserUserUserGroup;
                $newUserGroupUser -> user_id = $user_id;
                $newUserGroupUser -> group_id = $v;
                $newUserGroupUser -> time_add = time();
                $newUserGroupUser -> time_update = time();
                $newUserGroupUser -> save();
                foreach ($role_group_id as $kk => $vv)
                {
                    $roleGroup = FilmfestUserRoleGroup::find($vv);
                    $is_relevance = FilmfestUserUserGroupRoleGroup::where('user_group_id',$v)->where('role_group_id',$vv)->first();
                    if(($roleGroup->filmfest_id)!=$filmfest_id || (int)($roleGroup->status) !==1 || !$is_relevance){
                        return response()->json(['message'=>'数据不合法'],200);
                    }
                    $newUserRoleGroup = new FilmfestUserUserRoleGroup;
                    $newUserRoleGroup -> user_id = $user_id;
                    $newUserRoleGroup -> role_group_id = $vv;
                    $newUserRoleGroup -> time_update = time();
                    $newUserRoleGroup -> save();

                    foreach ($role_id as $kkk => $vvv)
                    {
                        $role = FilmfestUserRole::find($vvv);
                        $is_relevance2 = FilmfestUserRoleRoleGroup::where('role_id',$vvv)->where('group_id',$vv)->first();
                        if(($role->filmfest_id)!=$filmfest_id || (int)($role->status) !==1 || !$is_relevance2){
                            return response()->json(['message'=>'数据不合法'],200);
                        }
                        $newUserRole = new UserFilmfestUserRole;
                        $newUserRole -> user_id = $user_id;
                        $newUserRole -> role_id = $vvv;
                        $newUserRole -> time_add = time();
                        $newUserRole -> time_update = time();
                        $newUserRole -> save();
                    }
                }
            }
            DB::commit();
            return response()->json(['message'=>'success'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }

    public function stopAdmin(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $user_id = $request->get('user_id',null);
            if(is_null($user_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $user = User::find($user_id);
            $role = $user->filmfest_role()->get();
            DB::beginTransaction();
            if($role->count()>0){
                foreach ($role as $k => $v)
                {
                    if($v->filmfest_id == $filmfest_id){
                        $role_id = $v->id;
                        if(strstr($v->role_name,'发起')){
                            return response()->json(['message'=>'这个用户不可以冻结'],200);
                        }
                        UserFilmfestUserRole::where('user_id',$user_id)->where('role_id',$role_id)->delete();
                    }else{
                        continue;
                    }
                }
            }else{
                return response()->json(['message'=>'该用户角色不能被冻结'],200);
            }
            $roleGroup = $user->filmfestUserRoleGroup()->get();
            if($roleGroup->count()>0){
                foreach ($roleGroup as $k =>  $v)
                {
                    if($v->filmfest_id == $filmfest_id){
                        $group_id = $v->id;
                        FilmfestUserUserRoleGroup::where('user_id',$user_id)->where('role_group_id',$group_id)->delete();
                    }else{
                        continue;
                    }
                }
            }else{
                return response()->json(['message'=>'该用户角色不能被冻结'],200);
            }
            $newRoleGroupId = FilmfestUserRoleGroup::where('name','like','%冻结%')
                ->where('filmfest_id',$filmfest_id)->first()->id;
            $newRoleId = FilmfestUserRole::where('role_name','like','%冻结%')
                ->where('filmfest_id',$filmfest_id)->first()->id;
            $newUserRoleGroup = new FilmfestUserUserRoleGroup;
            $newUserRoleGroup -> user_id = $user_id;
            $newUserRoleGroup -> role_group_id = $newRoleGroupId;
            $newUserRoleGroup -> time_add = time();
            $newUserRoleGroup -> time_update = time();
            $newUserRoleGroup -> save();

            $newUserRole = new UserFilmfestUserRole;
            $newUserRole -> user_id = $user_id;
            $newUserRole -> role_id = $newRoleId;
            $newUserRole -> time_add = time();
            $newUserRole -> time_update = time();
            $newUserRole -> save();

            DB::commit();
            return response()->json(['message'=>'success'],200);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function logExport(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $user_id = $request->get('user_id');
            $nickName = User::find($user_id)->nickname;
            $role = $request->get('role');
            if(is_null($user_id)||is_null($role)){
                return response()->json(['message'=>'缺少数据'],200);
            }
            $data = FilmfestUserReviewChildLog::where('user_id',$user_id)->where('filmfest_id',$filmfest_id)
                ->orderBy('time_add','desc')->get();
            $title = [
                0=>'时间',
                1=>'行为',
            ];
            $export = null;
            foreach($data as $k => $v)
            {
                $time = date('Y年m月d日. H:i',$v->time_add);
                $content = ($v->doint).'  '.($v->cause);
                $export[$k][0] = $time;
                $export[$k][1] = $content;
            }
            $data = array_merge($title,$export);
            $head = $role.$nickName.'操作日志';
            Excel::create($head,function ($excel) use($data,$head){
                $excel->sheet($head,function ($sheet) use($data){
                    $sheet->setWidth(
                        array(
                            'A'=>10,
                            'B'=>30,
                        )
                    );
                    $sheet->rows($data);
                });
            })->export('xls');

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function sendPrivaterLetter(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $type = $request->get('type',1);
            $user = \Auth::guard('api')->user()->id;
            $user_id = $request->get('user_id',null);
            $content = $request->get('content',null);
            if(is_null($content)){
                return response()->json(['message'=>'私信不能为空'],200);
            }
            DB::beginTransaction();
            if($type == 1){             //  给一个或指定用户发私信，知道用户id
                if(is_null($user_id)){
                    return response()->json(['message'=>'数据不合法'],200);
                }
                $user_id = rtrim($user_id,'|');
                $user_id = explode('|',$user_id);
                foreach ($user_id as $k => $v)
                {
                    $newPrivaterLetter = new PrivateLetter;
                    $newPrivaterLetter -> from = $user;
                    $newPrivaterLetter -> to = $v;
                    $newPrivaterLetter -> content = $content;
                    $newPrivaterLetter -> created_at = time();
                    $newPrivaterLetter -> updated_at = time();
                    $newPrivaterLetter -> save();
                }

            }elseif($type==2){
                //  给角色组统一群发消息
                $role_group_id = $request->get('role_group_id',null);
                if(is_null($role_group_id)){
                    return response()->json(['message'=>'数据不合法'],200);
                }
                $role_group_id = rtrim($role_group_id,'|');
                $role_group_id = explode('|',$role_group_id);
                foreach ($role_group_id as $k => $v)
                {
                    $user_id = User::whereHas('filmfestUserRoleGroup',function ($q) use($v,$filmfest_id){
                        $q->where('filmfest_user_role_group.id',$v)->where('filmfest_user_role_group.filmfest_id',$filmfest_id);
                    })->get();
                    if($user_id->count()>0){
                        foreach ($user_id as $kk => $vv)
                        {
                            $newPrivaterLetter = new PrivateLetter;
                            $newPrivaterLetter -> from = $user;
                            $newPrivaterLetter -> to = $vv->id;
                            $newPrivaterLetter -> content = $content;
                            $newPrivaterLetter -> created_at = time();
                            $newPrivaterLetter -> updated_at = time();
                            $newPrivaterLetter -> save();
                        }
                    }
                }

            }elseif($type == 3){
                //  给用户组统一发消息
                $user_group_id = $request->get('user_group_id',null);
                if(is_null($user_group_id)){
                    return response()->json(['message'=>'数据不合法'],200);
                }
                $user_group_id = rtrim($user_group_id,'|');
                $user_group_id = explode('|',$user_group_id);
                foreach ($user_group_id as $k => $v)
                {
                    $user_id = User::whereHas('filmfestUserGroup',function ($q)use($v,$filmfest_id){
                        $q->where('filmfest_user_user_group.id',$v)->where('filmfest_user_user_group.filmfest_id',$filmfest_id);
                    })->get();
                    if($user_id->count()>0){
                        foreach ($user_id as $kk => $vv)
                        {
                            $newPrivaterLetter = new PrivateLetter;
                            $newPrivaterLetter -> from = $user;
                            $newPrivaterLetter -> to = $vv->id;
                            $newPrivaterLetter -> content = $content;
                            $newPrivaterLetter -> created_at = time();
                            $newPrivaterLetter -> updated_at = time();
                            $newPrivaterLetter -> save();
                        }
                    }
                }
            }else{
                return response()->json(['message'=>"Hey! Brother, don't be funny!"]);
            }
            DB::commit();

            return response()->json(['message'=>'发送成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }




}
