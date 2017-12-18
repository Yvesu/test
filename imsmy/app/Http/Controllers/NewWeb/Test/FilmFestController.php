<?php

namespace App\Http\Controllers\NewWeb\Test;

use App\Models\Filmfests;
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
                $mainData = Filmfests::orderBy('count','asc')->forPage($page,$this->paginate)->get();
            }elseif ($active==2){
                $mainData = Filmfests::orderBy('time_start','asc')->forPage($page,$this->paginate)->get();
            }elseif($active==3){
                $mainData = Filmfests::orderBy('time_end','asc')->forPage($page,$this->paginate)->get();
            }elseif($active == 4){
                $mainData = Filmfests::whereHas('productions',function($q) use ($id){
                    $q->where('user_id',$id);
                })->forPage($page,$this->paginate)->get();
            }else{
                $mainData = Filmfests::forPage($page,$this->paginate)->get();
            }
            $data = [];
            foreach ($mainData as $k => $v)
            {
                $period = $v->period;
                $submit_end_time = date('Y年m月d日',$v->submit_end_time);
                $festTime = date('Y年m月d日',$v->time_start).' - '.date('Y年m月d日',$v->time_end);
                $type = '';
                if($v->filmFestType){
                   foreach ($v->filmFestType as $kk => $vv)
                   {
                       $type .= '、'.$vv->name;
                   }
                }
                $address = $v->address;
                $cost = $v->cost;
                $id = $v->id;
                $tempData = [
                    'id' => $id,
                    'submit_end_time'=>$submit_end_time,
                    'festTime'=>$festTime,
                    'type'=>$type,
                    'address'=>$address,
                    'cost'=>$cost,
                ];
                array_push($data,$tempData);


            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }
}
