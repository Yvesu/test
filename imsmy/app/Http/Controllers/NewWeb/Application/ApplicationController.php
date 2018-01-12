<?php

namespace App\Http\Controllers\NewWeb\Application;

use App\Models\Filmfest\Application;
use App\Models\Filmfest\ApplicationContactWay;
use App\Models\Filmfest\FilmTypeApplication;
use App\Models\FilmfestFilmfestType;
use App\Models\FilmfestFilmType;
use App\Models\Filmfests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use CloudStorage;

class ApplicationController extends Controller
{
    //
    public function pageOne(Request $request)
    {
        try{
            $filmfest_id = $request->get('filmfest_id',null);
            if($filmfest_id){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $protocol_address = Filmfests::where('id','=',$filmfest_id)->first()->protocol;
            $user = \Auth::guard('api')->user()->id;
            $oldData = ApplicationContactWay::select('id','is_student','protocol','papers','number')->where('user_id','=',$user)
                ->where('filmfests_id','=',$filmfest_id)->first();
            if($oldData){
                $data = [
                    'id'=>$oldData->id,
                    'is_student'=>$oldData->is_student,
                    'protocol'=>$oldData->protocol,
                    'papers'=>$oldData->papers,
                    'protocol_address' => $protocol_address,
                    'number'=>$oldData->number,
                ];
            }else{
                DB::beginTransaction();
                $number = (Application::where('filmfests_id','=',$filmfest_id)->get()->max('number'))+1;
                $newApplication = new Application;
                $newApplication -> user_id = $user;
                $newApplication -> filmfests_id = $filmfest_id;
                $newApplication -> time_add = time();
                $newApplication -> time_update = time();
                $newApplication -> number = $number;
                $newApplication -> save();
                DB::commit();
                $data = [
                    'id' => $newApplication->id,
                    'is_student' => '',
                    'protocol' => '',
                    'papers' => '',
                    'protocol_address' => '',
                    'number'=> '',
                ];
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function pageTwo(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            $id = $request->get('id',null);
            $filmfests_id = $request->get('filmfests_id',null);
            if(is_null($id)||is_null($filmfests_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $oldData = Application::where('id','=',$id)->where('name','!=',null)->get();
            if($oldData){
                if($oldData->filmType()->first()){
                    $types = [];
                    foreach ($oldData->filmType as $k => $v)
                    {
                        if($v->filmfests->id == $id){
                            array_push($types,$v->name);
                        }else{
                            continue;
                        }
                    }
                    $trueTypes = [];
                    if(in_array('剧情片',$types)){
                        array_push($trueTypes,['story'=>'1']);
                    }else{
                        array_push($trueTypes,['story'=>'0']);
                    }
                    if(in_array('纪录片',$types)){
                        array_push($trueTypes,['documentary'=>'1']);
                    }else{
                        array_push($trueTypes,['documentary'=>'0']);
                    }
                    if(in_array('动画短片',$types)){
                        array_push($trueTypes,['cartoon'=>'1']);
                    }else{
                        array_push($trueTypes,['cartoon'=>'0']);
                    }
                    if(in_array('实验短片',$types)){
                        array_push($trueTypes,['short_film'=>'1']);
                    }else{
                        array_push($trueTypes,['short_film'=>'0']);
                    }
                    if(in_array('联合特别单元',$types)){
                        array_push($trueTypes,['particularly_union'=>'1']);
                    }else{
                        array_push($trueTypes,['particularly_union'=>'0']);
                    }
                }else{
                    $trueTypes = [];
                }
                $data = [
                    'id'=>$id,
                    'user_id'=>$user,
                    'filmfests_id'=>$filmfests_id,
                    'name'=>$oldData->name,
                    'english_name'=>$oldData->english_name,
                    'duration'=>(($oldData->duration)/3600).':'.((($oldData->duration)%3600)/60).':'.((($oldData->duration)%3600)%60),
                    'is_orther_web'=>$oldData->is_orther_web,
                    'copyright'=>$oldData->copyright,
                    'is_collective'=>$oldData->is_collective,
                    'types' => $trueTypes,
                    'create_people_num'=>$oldData->create_people_num,
                    'create_collective_name'=>$oldData->create_collective_name,
                    'create_start_time'=>date('Y.m.d',$oldData->create_start_time),
                    'create_end_time'=>date('Y.m.d',$oldData->create_end_time),
                    'production_des'=>$oldData->production_des,
                    'production_english_des'=>$oldData->production_english_des,
                ];
            }else{
                $keys1 = [];
                $keys2 = [];
                $protocol = $request -> get('protocol',null);
                $papers = $request -> get('papers',null);
                if(is_null($protocol)||is_null($papers)){
                    return response()->json(['message'=>'数据不合法'],200);
                }
                array_push($keys2,$protocol);
                array_push($keys1,$papers);
                $keyPairs1 = array();
                $keyPairs2 = array();
                foreach($keys1 as $key)
                {
                    $keyPairs1[$key] = $key;
                }
                foreach ($keys2 as $key)
                {
                    $keyPairs2[$key] = $key;
                }
                $srcbucket1 = 'hivideo-img-ects';
                $srcbucket2 = 'hivideo-file-ects';
                $destbucket1 = 'hivideo-img';
                $destbucket2 = 'hivideo-file';
                $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
                $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket2,$destbucket2);
                if($message1[0]['code']==200 && $message2[0]['code']==200)
                {
                    $is_student = $request->get('is_student',null);
                    $protocol = $request->get('protocol',null);
                    $papers = $request->get('papers',null);
                    $protocol_address = $request->get('protocol_address',null);
                    $number = $request->get('number',null);
                    if(is_null($is_student)||is_null($protocol)||is_null($papers)||is_null($protocol_address)||is_null($number))
                    {
                        return response()->json(['message'=>'有数据为空'],200);
                    }
                    $newData = Application::where('id','=',$id)->first();
                    $newData -> is_student = $is_student;
                    $newData -> protocol = $protocol;
                    $newData -> papers = $papers;
                    $newData -> protocol_address = $protocol_address;
                    $newData -> number = $number;
                    $newData -> time_update = time();
                    $newData -> save();

                }else{
                    return response()->json(['message'=>'失败'],200);
                }
                $data = [
                    'id'=>$id,
                    'user_id'=>$user,
                    'filmfests_id'=>$filmfests_id,
                    'name'=>'',
                    'english_name'=>'',
                    'duration'=>'',
                    'is_orther_web'=>'',
                    'copyright'=>'',
                    'is_collective'=>'',
                    'types' => '',
                    'create_people_num'=>'',
                    'create_collective_name'=>'',
                    'create_start_time'=>'',
                    'create_end_time'=>'',
                    'production_des'=>'',
                    'production_english_des'=>'',
                ];
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }


    public function pageThree()
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            $id = $request->get('id',null);
            $filmfests_id = $request->get('filmfests_id',null);


            $name = $request->get('name',null);
            $english_name = $request->get('english_name',null);
            $duration = $request->get('duration',null);
            $is_orther_web = $request->get('is_orther_web',null);
            $copyright = $request->get('copyright',null);
            $is_collective = $request->get('is_collective',null);
            $is_story = $request->get('is_story',0);
            $is_documentary = $request->get('is_documentary',0);
            $is_cartoon = $request->get('is_cartoon',0);
            $is_short_film = $request->get('is_short_film',0);
            $is_particularly_union = $request->get('is_particularly_union',0);
            $create_people_num = $request->get('create_people_num',null);
            $create_collective_name = $request->get('create_collective_name',null);
            $create_start_time = $request->get('create_start_time',null);
            $create_end_time = $request->get('create_end_time',null);
            $production_des = $request->get('production_des',null);
            $production_english_des = $request->get('production_english_des',null);
            if(is_null($name)||is_null($english_name)||is_null($duration)||is_null($is_orther_web)||is_null($copyright)||
                is_null($is_collective)||($is_story==0 && $is_documentary==0 && $is_cartoon==0 && $is_short_film == 0
                    && $is_particularly_union == 0)||is_null($create_people_num)||is_null($create_collective_name)
                || is_null($create_start_time)||is_null($create_end_time)||is_null($production_des)||is_null($production_english_des)
            ){
                return response()->json(['message'=>'有数据为空'],200);
            }
            $duration = explode(':',$duration);
            $duration = ($duration[0]*3600)+($duration[1]*60)+($duration[1]);
            $newData = Application::where('id','=',$id)->first();
            $newData -> name = $name;
            $newData -> duration = $duration;
            $newData -> is_orther_web = $is_orther_web;
            $newData -> copyright = $copyright;
            $newData -> is_collective = $is_collective;
            $newData -> create_people_num = $create_people_num;
            $newData -> create_collective_name = $create_collective_name;
            $newData -> create_start_time = $create_start_time;
            $newData -> create_end_time = $create_end_time;
            $newData -> production_des = $production_des;
            $newData -> save();
            if($is_story == 1){
                $type1_id = FilmfestFilmType::where('name','=','剧情片')->first()->id;
                $type1 = new FilmTypeApplication;
                $type1 -> type_id = $type1_id;
                $type1 -> application_id = $id;
                $type1 -> time_add = time();
                $type1 -> time_update = time();
                $type1 -> save();
            }
            if($is_documentary == 1){
                $type1_id = FilmfestFilmType::where('name','=','纪录片')->first()->id;
                $type1 = new FilmTypeApplication;
                $type1 -> type_id = $type1_id;
                $type1 -> application_id = $id;
                $type1 -> time_add = time();
                $type1 -> time_update = time();
                $type1 -> save();
            }
            if($is_cartoon == 1){
                $type1_id = FilmfestFilmType::where('name','=','动画片')->first()->id;
                $type1 = new FilmTypeApplication;
                $type1 -> type_id = $type1_id;
                $type1 -> application_id = $id;
                $type1 -> time_add = time();
                $type1 -> time_update = time();
                $type1 -> save();
            }
            if($is_short_film == 1){
                $type1_id = FilmfestFilmType::where('name','=','实验短片')->first()->id;
                $type1 = new FilmTypeApplication;
                $type1 -> type_id = $type1_id;
                $type1 -> application_id = $id;
                $type1 -> time_add = time();
                $type1 -> time_update = time();
                $type1 -> save();
            }
            if($is_particularly_union == 1){
                $type1_id = FilmfestFilmType::where('name','=','联合特别单元')->first()->id;
                $type1 = new FilmTypeApplication;
                $type1 -> type_id = $type1_id;
                $type1 -> application_id = $id;
                $type1 -> time_add = time();
                $type1 -> time_update = time();
                $type1 -> save();
            }
        }catch (ModelNotFoundException $q){

        }
    }
}
