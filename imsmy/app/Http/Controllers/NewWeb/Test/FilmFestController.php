<?php

namespace App\Http\Controllers\NewWeb\Test;

use App\Models\Activity;
use App\Models\Filmfests;
use App\Models\TweetActivity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FilmFestController extends Controller
{
    //
    private $protocol = 'http://';

    private $paginate = 5;

    public function index(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user();
            $active = $request->get('active',1);
            $page = $request->get('page',1);
            $id = $user->id;
            if($active==1){
                $mainData = Activity::where('active','=',1)->orderBy('work_count','desc')->limit($page*$this->paginate)->get();
            }elseif ($active==2){
                $mainData = Activity::where('active','=',1)->orderBy('time_start','desc')->limit($page*$this->paginate)->get();
            }elseif($active==3){
                $mainData = Activity::where('active','=',1)->orderBy('time_end','desc')->limit($page*$this->paginate)->get();
            }elseif($active == 4){
                $mainData = Activity::where('active','=',1)->whereHas('hasManyTweets',function($q) use ($id){
                    $q->where('tweet_activity.user_id',$id);
                })->forPage($page,$this->paginate)->get();
            }elseif($active == 5){
                $mainData = Activity::where('active','=',1)->limit($page*$this->paginate)->get();
            }else{
                $mainData = Activity::where('active','=',1)->orderBy('work_count','desc')->limit($page*$this->paginate)->get();
            }
            $more = '';
            if($mainData->count() < $page*$this->paginate){
                $more = false;
            }else{
                $more = true;
            }
            $data = [];
            foreach ($mainData as $k => $v)
            {
                $submit_end_time = date('Y年m月d日',$v->submit_end_time);
                $festTime = date('Y年m月d日',$v->time_start).' - '.date('Y年m月d日',$v->time_end);
                $type = '';
                if($v->hasManyChannel){
                   foreach ($v->hasManyChannel as $kk => $vv)
                   {
                       $type .= '、'.$vv->name;
                   }
                }

                $address = $v->address;
                $cost = $v->cost;
                $id = $v->id;
                if($v->time_end > time()){
                    $tempData = [
                        'name' => $v->name,
                        'id' => $id,
                        'submit_end_time'=>$submit_end_time,
                        'festTime'=>$festTime,
                        'type'=>$type,
                        'address'=>$address,
                        'cost'=>$cost,
                    ];
                }else{
                    $tempData = [
                        'name' => $v->name,
                        'id' => $id,
                        'submit_end_time'=>$submit_end_time,
                        'festTime'=>$festTime,
                        'type'=>$type,
                        'address'=>$address,
                        'cost'=>$cost,
                        'end' => 'yes',
                    ];
                }

                array_push($data,$tempData);


            }
            return response()->json(['data'=>$data,'more'=>$more],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }
}
