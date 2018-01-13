<?php

namespace App\Http\Controllers\NewWeb\User;

use App\Models\Filmfest\JoinUniversity;
use App\Models\FilmfestFilmType;
use App\Models\Tweet;
use App\Models\TweetProduction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class FilmfestController extends Controller
{
    //
    public function index(Request $request)
    {
        try{
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据异常'],200);
            }

            //  应届作品
            $currentProductionNum = TweetProduction::select('id')->where('is_current','1')
                ->whereHas('filmfests',function ($q) use($id){
                    $q->where('id','=',$id);
                })->get()->count();
            //  历届作品
            $noCurrentProductionNum = TweetProduction::select('id')->where('is_current','0')
                ->whereHas('filmfests',function ($q) use($id){
                    $q->where('id','=',$id);
                })->get()->count();
            //  参与院校
            $joinUniversityNum = TweetProduction::where('is_current',1)
                ->whereHas('filmfests',function ($q) use($id){
                    $q->where('id','=',$id);
                })->count('join_university_id');
            //  历届参与院校
            $historyUniversityNum = JoinUniversity::select('id')
                ->whereHas('filmfests',function ($q) use($id){
                    $q->where('id','=',$id);
                })->get()->count();
            /**
             * 分类占比
             */
            //  总片数
            $sumNum = TweetProduction::select('id')->whereHas('filmefestProduction',function ($q){
                $q->where('status','<>',2)->where('status','<>',4);
            })
                ->whereHas('filmfests',function ($q) use($id){
                    $q->where('id','=',$id);
                })->get()->count();
            //  该电影节的所有单元
            $units = FilmfestFilmType::whereHas('filmFests',function ($q)use($id){
                $q->where('id','=',$id);
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
                    $data4 = (round($data3/$sumNum,2)*100).'%';
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
                'noCurrentProductionNum'=>$noCurrentProductionNum,
                'joinUniversityNum'=>$joinUniversityNum,
                'historyUniversityNum'=>$historyUniversityNum,
            ];

            return response()->json(['data'=>$data,'data2'=>$finallyData],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function baseData($filmfest_id,$content,$visible)
    {
        if($visible==1){
            $data = TweetProduction::select('id')->whereHas('filmefestProduction',function ($q){
                $q->where('status','<>',2)->where('status','<>',4);
            })
                ->whereHas('filmfests',function ($q) use($filmfest_id){
                    $q->where('id','=',$filmfest_id);
                })
                ->whereHas('filmfestFilmType',function ($q) use($content){
                    $q->where('name',$content);
                })->whereHas('tweet',function ($q){
                    $q->where('visible','=',2);
                })->get()->count();
        }elseif($visible == 2){
            $data = TweetProduction::select('id')->whereHas('filmefestProduction',function ($q){
                $q->where('status','<>',2)->where('status','<>',4);
            })
                ->whereHas('filmfests',function ($q) use($filmfest_id){
                    $q->where('id','=',$filmfest_id);
                })
                ->whereHas('filmfestFilmType',function ($q) use($content){
                    $q->where('name',$content);
                })->whereHas('tweet',function ($q){
                    $q->where('visible','<>',2);
                })->get()->count();
        }else{
            $data = TweetProduction::select('id')->whereHas('filmefestProduction',function ($q){
                $q->where('status','<>',2)->where('status','<>',4);
            })
                ->whereHas('filmfests',function ($q) use($filmfest_id){
                    $q->where('id','=',$filmfest_id);
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
            $filmfest_id = $request -> get('filmfest_id',1);
            $time = $request -> get('time','1');
            if($type == 1){
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
                    $sumDays = floor($time/86400);
                    //  每份天数
                    $everyDays = floor($sumDays/12);
                    //  开始时间数组
                    $startDayArray = explode('.',$startDay);
                    //  结束时间数组
                    $endDayArray = explode('.',$endDay);
                    //  下标数组
                    $date = [];
                    //  长月数组
                    $longMonth =  [1,3,5,7,8,10,12];
                    //  短月数组
                    $shortMonth = [4,6,8,11];
                    for($i=0;$i<12;$i++)
                    {
                        //  临时天数
                        $tempDay = $startDayArray[2]+$everyDays;
                        if($tempDay>31 && in_array($startDayArray[1],$longMonth)){          //  临时天数大于31天，且开始为长月
                            $endDayArray[2] = $tempDay-31;
                            if($startDayArray[1]==12){                                      //  当前月是否为12月
                                $endDayArray[1] = 1;
                            }else{
                                $endDayArray[1] = $startDayArray[1] + 1;
                            }
                        }elseif ($tempDay<=31 && in_array($startDayArray[1],$longMonth)){   //  临时天数小于31天，且当前月为长月
                            $endDayArray[2] = $tempDay;
                            if($startDayArray[1]==12){                                      //  当前月是否为12月
                                $endDayArray[1] = 1;
                            }else{
                                $endDayArray[1] = $startDayArray[1] + 1;
                            }
                        }elseif ($startDayArray[1]==2){                                     //  如果是2月
                            //  如果是闰年
                            if((($startDayArray[0]%4==0 && $startDayArray[0]%100 != 0) || $startDayArray[0]%400 == 0)){
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
                        }elseif ($tempDay>30 && in_array($startDayArray[1],$shortMonth)){   //  如果是小月
                            $endDayArray[2] = $tempDay-30;
                            if($startDayArray[1]==12){
                                $endDayArray[1] = 1;
                            }else{
                                $endDayArray[1] = $startDayArray[1] + 1;
                            }
                        }elseif ($tempDay<=30 && in_array($startDayArray[1],$shortMonth)){
                            $endDayArray[2] = $tempDay;
                            if($startDayArray[1]==12){
                                $endDayArray[1] = 1;
                            }else{
                                $endDayArray[1] = $startDayArray[1] + 1;
                            }
                        }
                        $date[$i] = $startDayArray[0].'.'.$startDayArray[1].'.'.$startDayArray[2].'.'.' - '.$endDayArray[0].'.'.$endDayArray[1].'.'.$endDayArray[2];
                    }

                    $data = [];
                    foreach ($date as $k => $v){
                        $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                            $q->where('id','=',$filmfest_id);
                        })->where('time_add','>',$startTime+($time/12)*$k)
                            ->where('time_add','<',$startTime+($time/12)*($k+1))
                            ->where('status','<>',2)
                            ->get()->count();

                        array_push($data,['XX'=>$v,'YY'=>$tempData]);
                    }

                }else{
                    $data = [];
                    if($time == 1){
                        $startTime = strtotime('today');
                        for ($i = 0;$i < 12 ;$i++){
                            $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                $q->where('id','=',$filmfest_id);
                            })->where('time_add','>',$startTime+(7200)*$i)
                                ->where('time_add','<',$startTime+(7200)*($i+1))
                                ->where('status','<>',2)
                                ->get()->count();
                            array_push($data,['XX'=>$i.':00','YY'=>$tempData]);
                        }
                    }elseif ($time == 2){
                        $timestamp = time();
                        $startTime = strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp)));
                        for ($i = 0;$i < 7 ;$i++){
                            $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                $q->where('id','=',$filmfest_id);
                            })->where('time_add','>',$startTime+(86400)*$i)
                                ->where('time_add','<',$startTime+(86400)*($i+1))
                                ->where('status','<>',2)
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
                        $startTime = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
                        for ($i = 1;$i <= $days ;$i++){
                            $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                $q->where('id','=',$filmfest_id);
                            })->where('time_add','>',$startTime+(86400)*$i)
                                ->where('time_add','<',$startTime+(86400)*($i+1))
                                ->where('status','<>',2)
                                ->get()->count();
                            array_push($data,['XX'=>$i.'日','YY'=>$tempData]);
                        }
                    }elseif ($time == 4){
                        $startTime = mktime(0, 0, 0, 1, 1, date('Y'));
                        for ($i = 1;$i<=12;$i++)
                        {
                            if($i=12){
                                $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                    $q->where('id','=',$filmfest_id);
                                })->where('time_add','>',mktime(0, 0, 0, $i, 1, date('Y')))
                                    ->where('time_add','<',mktime(0, 0, 0, 1, 1, date('Y+1')))
                                    ->where('status','<>',2)
                                    ->get()->count();
                            }else{
                                $tempData = TweetProduction::select('id')->whereHas('filmfest',function ($q) use($filmfest_id){
                                    $q->where('id','=',$filmfest_id);
                                })->where('time_add','>',mktime(0, 0, 0, $i, 1, date('Y')))
                                    ->where('time_add','<',mktime(0, 0, 0, $i+1, 1, date('Y')))
                                    ->where('status','<>',2)
                                    ->get()->count();
                            }
                            array_push($data,['XX'=>$i.'日','YY'=>$tempData]);
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
                    $sumDays = floor($time/86400);
                    //  每份天数
                    $everyDays = floor($sumDays/12);
                    //  开始时间数组
                    $startDayArray = explode('.',$startDay);
                    //  结束时间数组
                    $endDayArray = explode('.',$endDay);
                    //  下标数组
                    $date = [];
                    //  长月数组
                    $longMonth =  [1,3,5,7,8,10,12];
                    //  短月数组
                    $shortMonth = [4,6,8,11];
                    for($i=0;$i<12;$i++)
                    {
                        //  临时天数
                        $tempDay = $startDayArray[2]+$everyDays;
                        if($tempDay>31 && in_array($startDayArray[1],$longMonth)){          //  临时天数大于31天，且开始为长月
                            $endDayArray[2] = $tempDay-31;
                            if($startDayArray[1]==12){                                      //  当前月是否为12月
                                $endDayArray[1] = 1;
                            }else{
                                $endDayArray[1] = $startDayArray[1] + 1;
                            }
                        }elseif ($tempDay<=31 && in_array($startDayArray[1],$longMonth)){   //  临时天数小于31天，且当前月为长月
                            $endDayArray[2] = $tempDay;
                            if($startDayArray[1]==12){                                      //  当前月是否为12月
                                $endDayArray[1] = 1;
                            }else{
                                $endDayArray[1] = $startDayArray[1] + 1;
                            }
                        }elseif ($startDayArray[1]==2){                                     //  如果是2月
                            //  如果是闰年
                            if((($startDayArray[0]%4==0 && $startDayArray[0]%100 != 0) || $startDayArray[0]%400 == 0)){
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
                        }elseif ($tempDay>30 && in_array($startDayArray[1],$shortMonth)){   //  如果是小月
                            $endDayArray[2] = $tempDay-30;
                            if($startDayArray[1]==12){
                                $endDayArray[1] = 1;
                            }else{
                                $endDayArray[1] = $startDayArray[1] + 1;
                            }
                        }elseif ($tempDay<=30 && in_array($startDayArray[1],$shortMonth)){
                            $endDayArray[2] = $tempDay;
                            if($startDayArray[1]==12){
                                $endDayArray[1] = 1;
                            }else{
                                $endDayArray[1] = $startDayArray[1] + 1;
                            }
                        }
                        $date[$i] = $startDayArray[0].'.'.$startDayArray[1].'.'.$startDayArray[2].'.'.' - '.$endDayArray[0].'.'.$endDayArray[1].'.'.$endDayArray[2];
                    }

                    $data = [];

                    $allProduction = TweetProduction::where('status','<>',2)->get();
                    foreach ($date as $k => $v)
                    {
                        $tempData = 0;
                        foreach ($allProduction as $item => $value)
                        {
                            if($value->tweet()->first()){
                                $id = $value->id;
                                $date1 = $startTime+($time/12)*$k;
                                $tempData1 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date1]);
                                $date2 = $startTime+($time/12)*($k+1);
                                $tempData2 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date2]);
                                $tempData += $tempData2 - $tempData1;
                            }else{
                                $tempData += 0;
                            }
                        }
                        array_push($data,['XX'=>$v,'YY'=>$tempData]);
                    }
                }else{
                    $data = [];
                    if($time == 1){
                        $allProduction = TweetProduction::where('status','<>',2)->get();
                        $startTime = strtotime('today');
                        for ($i = 0;$i < 12 ;$i++){
                            $tempData = 0;
                            foreach ($allProduction as $item => $value)
                            {
                                if($value->tweet()->first()){
                                    $id = $value->id;
                                    $date1 = $startTime+(7200)*$i;
                                    $tempData1 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date1]);
                                    $date2 = $startTime+(7200)*($i+1);
                                    $tempData2 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date2]);
                                    $tempData += $tempData2 - $tempData1;
                                }else{
                                    $tempData += 0;
                                }
                            }
                            array_push($data,['XX'=>$i.':00','YY'=>$tempData]);
                        }
                    }elseif ($time == 2){
                        $allProduction = TweetProduction::where('status','<>',2)->get();
                        $timestamp = time();
                        $startTime = strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp)));
                        for ($i = 0;$i < 7 ;$i++){
                            $tempData = 0;
                            foreach ($allProduction as $item => $value)
                            {
                                if($value->tweet()->first()){
                                    $id = $value->id;
                                    $date1 = $startTime+(86400)*$i;
                                    $tempData1 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date1]);
                                    $date2 = $startTime+(86400)*($i+1);
                                    $tempData2 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date2]);
                                    $tempData += $tempData2 - $tempData1;
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
                        $startTime = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
                        $allProduction = TweetProduction::where('status','<>',2)->get();
                        for ($i = 1;$i <= $days ;$i++){
                            $tempData = 0;
                            foreach ($allProduction as $item => $value)
                            {
                                if($value->tweet()->first()){
                                    $id = $value->id;
                                    $date1 = $startTime+(86400)*$i;
                                    $tempData1 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date1]);
                                    $date2 = $startTime+(86400)*($i+1);
                                    $tempData2 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date2]);
                                    $tempData += $tempData2 - $tempData1;
                                }else{
                                    $tempData += 0;
                                }
                            }
                            array_push($data,['XX'=>$i.'日','YY'=>$tempData]);
                        }
                    }elseif ($time == 4){
                        $allProduction = TweetProduction::where('status','<>',2)->get();
                        $startTime = mktime(0, 0, 0, 1, 1, date('Y'));
                        for ($i = 1;$i<=12;$i++)
                        {
                            if($i=12){
                                $date1 = mktime(0, 0, 0, $i, 1, date('Y'));
                                $date2 = mktime(0, 0, 0, 1, 1, date('Y+1'));
                                $tempData = 0;
                                foreach ($allProduction as $item => $value)
                                {
                                    if($value->tweet()->first()){
                                        $id = $value->id;
                                        $tempData1 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date1]);
                                        $tempData2 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date2]);
                                        $tempData += $tempData2 - $tempData1;
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
                                    if($value->tweet()->first()){
                                        $id = $value->id;
                                        $tempData1 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date1]);
                                        $tempData2 = DB::select("select like_count from tweet where id = ? AND UNIX_TIMESTAMP(updated_at)>? ",[$id,$date2]);
                                        $tempData += $tempData2 - $tempData1;
                                    }else{
                                        $tempData += 0;
                                    }
                                }
                            }
                            array_push($data,['XX'=>$i.'日','YY'=>$tempData]);
                        }
                    }
                }
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function universityTop()
    {
        try{
            $school = JoinUniversity::get();
            $tempData = [];
            foreach($school as $k => $v)
            {
                $count = 0;
                if($v->tweetProduction()->first()){
                    foreach($v->tweetProduction as $kk => $vv)
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
            }

            arsort($tempData);
            $tempData = array_slice($tempData,0,9,true);
            $data = [];
            foreach ($tempData as $k => $v)
            {
                $tempData2 = [
                    'name'=>JoinUniversity::where('id',$k)->pluck('name'),
                    'count'=>$v
                ];
                array_push($data,$tempData2);
            }

            return response()->json(['data'=>$data]);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function university()
    {
        try{
            $school = JoinUniversity::get();
            $tempData = [];
            foreach($school as $k => $v)
            {
                $count = 0;
                if($v->tweetProduction()->first()){
                    foreach($v->tweetProduction as $kk => $vv)
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
            }

            arsort($tempData);
            $data = [];
            foreach ($tempData as $k => $v)
            {
                $tempData2 = [
                    'name'=>JoinUniversity::where('id',$k)->pluck('name'),
                    'count'=>$v
                ];
                array_push($data,$tempData2);
            }

            return response()->json(['data'=>$data]);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }




}
