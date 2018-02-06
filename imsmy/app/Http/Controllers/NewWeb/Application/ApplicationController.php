<?php

namespace App\Http\Controllers\NewWeb\Application;

use App\Models\ChannelTweet;
use App\Models\Cloud\CloudStorageFile;
use App\Models\Cloud\CloudStorageFolder;
use App\Models\Filmfest\Application;
use App\Models\Filmfest\ApplicationContactWay;
use App\Models\Filmfest\FilmfestUniversity;
use App\Models\Filmfest\FilmTypeApplication;
use App\Models\Filmfest\JoinUniversity;
use App\Models\Filmfest\TweetProductionApplication;
use App\Models\FilmfestFilmfestType;
use App\Models\FilmfestFilmType;
use App\Models\Filmfests;
use App\Models\FilmfestsProductions;
use App\Models\ProductionFilmType;
use App\Models\Tweet;
use App\Models\TweetContent;
use App\Models\TweetProduction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use CloudStorage;

class ApplicationController extends Controller
{
    //
    private $paginate = 8;

    public function into(Request $request)
    {
        try{
            $filmfest_id = $request->get('filmfests_id',null);
            $user = \Auth::guard('api')->user()->id;
            $ok = Filmfests::where('id','=',$user)->whereHas('user',function ($q) use($user){
                $q->where('user.id',$user);
            })->first();
            if($ok){
                return response()->json(['message'=>'您是管理者，不可以参与']);
            }else{
                $is_over = Application::where('user_id',$user)->where('is_over','=',1)->where('filmfests_id',$filmfest_id)->first();
                if($is_over){
                    return response()->json(['message'=>'您已经报过名了'],200);
                }else{
                    return $filmfest_id;
                }
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function pageOne(Request $request)
    {
        try{
            $filmfest_id = $request->get('filmfests_id',null);
            if(is_null($filmfest_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $id = $request -> get('id');
            $status = $request->get('status',0);

            $filmfest = Filmfests::where('id','=',$filmfest_id)->first();
            if($filmfest){
//                $protocol_address = $filmfest->protocol;
                $filmfest_name = $filmfest->name;
                $period = $filmfest->period;
                $des = $filmfest -> des;
            }else{
                return response()->json(['message'=>'该电影节不存在'],200);
            }
            $user = \Auth::guard('api')->user()->id;
            $is_over = Application::where('user_id',$user)->where('is_over','=',1)->where('filmfests_id',$filmfest_id)->first();
            if($is_over){
                return response()->json(['message'=>'您已经报过名了'],200);
            }
            $ok = Filmfests::where('id','=',$user)->whereHas('user',function ($q) use($user){
                $q->where('user.id',$user);
            })->first();
            if($ok){
                return response()->json(['message'=>'您是管理者，不可以参与']);
            }
            if($status == 0){
                $oldData = Application::select('id','is_student','protocol','papers','number')->where('user_id','=',$user)
                    ->where('filmfests_id','=',$filmfest_id)->first();
                if($oldData){
                    $data = [
                        'filmfest_id'=>(int)$filmfest_id,
                        'period'=>'第 '.$period.' 届',
                        'filmfest_name'=>$filmfest_name,
                        'id'=>$oldData->id,
                        'des'=> $des,
                        'is_student'=>$oldData->is_student,
//                        'protocol'=>$oldData->protocol,
                        'papers'=>$oldData->papers,
//                        'protocol_address' => $protocol_address,
                        'number'=>$oldData->number,
                        'user_id'=>$user,
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
                        'filmfest_id'=>(int)$filmfest_id,
                        'user_id'=>$user,
                        'period'=>'第 '.$period.' 届',
                        'filmfest_name'=>$filmfest_name,
                        'id' => $newApplication->id,
                        'des'=> $des,
                        'is_student' => '',
//                        'protocol' => '',
                        'papers' => '',
//                        'protocol_address' => '',
                        'number'=> '',
                    ];
                }
            }elseif ($status == 1){
                $oldData = Application::where('id','=',$id)->first();
                $data = [
                    'filmfest_id'=>(int)$filmfest_id,
                    'user_id'=>$user,
                    'period'=>'第 '.$period.' 届',
                    'filmfest_name'=>$filmfest_name,
                    'id'=>$oldData->id,
                    'des'=>$des,
                    'is_student'=>$oldData->is_student,
//                    'protocol'=>$oldData->protocol,
                    'papers'=>$oldData->papers,
//                    'protocol_address' => $protocol_address,
                    'number'=>$oldData->number,
                ];
            }else{
                return response()->json(['message'=>'not_found'],404);
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
            $status = $request->get('status',0);
            $filmfests_id = $request->get('filmfests_id',null);
            if(is_null($id)||is_null($filmfests_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $is_over = Application::where('user_id',$user)->where('is_over','=',1)->where('filmfests_id',$filmfests_id)->first();
            if($is_over){
                return response()->json(['message'=>'您已经报过名了'],200);
            }
            $ok = Filmfests::where('id','=',$user)->whereHas('user',function ($q) use($user){
                $q->where('user.id',$user);
            })->first();
            if($ok){
                return response()->json(['message'=>'您是管理者，不可以参与']);
            }
            $units = FilmfestFilmType::whereHas('filmFests',function ($q) use($filmfests_id){
                $q->where('filmfests.id','=',$filmfests_id);
            })->get();
            $unit = [];
            foreach ($units as $k => $v) {
                array_push($unit,$v->name);
            }
            if($status == 0){
//                $protocol = $request -> get('protocol',null);
                $papers = $request -> get('papers',null);
                if(is_null($papers)){
                    return response()->json(['message'=>'数据不合法'],200);
                }
                $oldFile = Application::where('id',$id)->first();
                $oldFile_img = $oldFile->papers;
//                $oldFile_file = $oldFile->protocol;
//                if($oldFile_file && $oldFile_file != $protocol){
//                    CloudStorage::deleteNew('hivideo-file',$oldFile_file);
//                    $than1 = true;
//                }elseif ($oldFile_file && $oldFile_file == $protocol){
//                    $than1 = false;
//                }else{
//                    $than1 = true;
//                }
                if($oldFile_img && $oldFile_img != $papers){
                    CloudStorage::deleteNew('hivideo-img',$oldFile_img);
                    $than2 = true;
                }elseif ($oldFile_img && $oldFile_img == $papers){
                    $than2 = false;
                }else{
                    $than2 = true;
                }
                $number = '编号:'.(Application::where('id',$id)->first()->number);
                if($than2){
                    $keys1 = [];
                    array_push($keys1,$papers);
                    $keyPairs1 = array();
                    foreach($keys1 as $key)
                    {
                        $keyPairs1[$key] = $key;
                    }
                    $srcbucket1 = 'hivideo-img-ects';
                    $destbucket1 = 'hivideo-img';
                    $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
                    CloudStorage::imgWaterMark($destbucket1,$papers,$number);
                }else{
                    $message1[0]['code']=200;
                }
//                if($than1){
//                    $keys2 = [];
//                    array_push($keys2,$protocol);
//                    $keyPairs2 = array();
//                    foreach ($keys2 as $key)
//                    {
//                        $keyPairs2[$key] = $key;
//                    }
//                    $srcbucket2 = 'hivideo-file-ects';
//                    $destbucket2 = 'hivideo-file';
//                    $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket2,$destbucket2);
//                    CloudStorage::imgWaterMark($destbucket2,$protocol,$number);
//                }else{
//                    $message2[0]['code']=200;
//                }
                $is_student = $request->get('is_student',null);

                if(is_null($is_student))
                {
                    return response()->json(['message'=>'有数据为空'],200);
                }

                if($message1[0]['code']==200)
                {
                    $newData = Application::where('id','=',$id)->first();
                    $newData -> is_student = $is_student;
//                    $newData -> protocol = $protocol;
                    $newData -> papers = $papers;
                    $newData -> time_update = time();
                    $newData -> save();

                }else{
                    return response()->json(['message'=>'失败'],200);
                }

                $oldData = Application::where('id','=',$id)->where('name','!=','')->first();
                if($oldData) {
                    if ($oldData->filmType()->first()) {
                        $types = [];
                        foreach ($oldData->filmType as $k => $v) {
                            foreach($v->filmfests as $kk => $vv){
                                if ($vv->id == $filmfests_id) {
                                    array_push($types, ['name'=>$v->name,'id'=>$v->id]);
                                } else {
                                    continue;
                                }
                            }
                        }

                        $trueTypes = [];
                        foreach ($types as $kk => $vv)
                        {
                            if(in_array($vv['name'], $unit)){
                                $temp = [
                                    'id'=>$vv['id'],
                                    'name'=>$vv['name'],
                                    'status'=>1,
                                ];
                            }else{
                                $temp = [
                                    'id'=>$vv['id'],
                                    'name'=>$vv['name'],
                                    'status'=>0,
                                ];
                            }
                            array_push($trueTypes,$temp);
                        }

                    } else {
                        $trueTypes = [];
                    }
                    $hour = floor((($oldData->duration) / 3600))>=10?floor((($oldData->duration) / 3600)):('0'.floor((($oldData->duration) / 3600)));
                    $minute = floor(((($oldData->duration) % 3600) / 60))>=10?floor(((($oldData->duration) % 3600) / 60)):('0'.floor(((($oldData->duration) % 3600) / 60)));
                    $second = floor(((($oldData->duration) % 3600) % 60))>=10?floor(((($oldData->duration) % 3600) % 60)):('0'.floor(((($oldData->duration) % 3600) % 60)));
                    $data = [
                        'id' => $id,
                        'user_id' => $user,
                        'filmfests_id' => $filmfests_id,
                        'name' => $oldData->name,
                        'english_name' => $oldData->english_name,
                        'duration' => $hour . ':' . $minute . ':' . $second,
                        'is_orther_web' => $oldData->is_orther_web,
                        'copyright' => $oldData->copyright,
                        'is_collective' => $oldData->is_collective,
                        'types' => $trueTypes,
                        'create_people_num' => $oldData->create_people_num,
                        'create_collective_name' => $oldData->create_collective_name,
                        'create_start_time' => date('Y.m.d', $oldData->create_start_time),
                        'create_end_time' => date('Y.m.d', $oldData->create_end_time),
                        'production_des' => $oldData->production_des,
                        'production_english_des' => $oldData->production_english_des,
                    ];
                }else{
                    $data = [
                        'id'=>$id,
                        'user_id'=>$user,
                        'filmfests_id'=>$filmfests_id,
                        'name'=>'',
                        'english_name'=>'',
                        'duration'=>'00:00:00',
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

            }elseif ($status == 1){
                $oldData = Application::where('id','=',$id)->where('name','!=','')->first();
                if($oldData){
                    if ($oldData->filmType()->first()) {
                        $types = [];
                        foreach ($oldData->filmType as $k => $v) {
                            foreach($v->filmfests as $kk => $vv){
                                if ($vv->id == $filmfests_id) {
                                    array_push($types, ['name'=>$v->name,'id'=>$v->id]);
                                } else {
                                    continue;
                                }
                            }
                        }

                        $trueTypes = [];
                        foreach ($types as $kk => $vv)
                        {
                            if(in_array($vv['name'], $unit)){
                                $temp = [
                                    'id'=>$vv['id'],
                                    'name'=>$vv['name'],
                                    'status'=>1,
                                ];
                            }else{
                                $temp = [
                                    'id'=>$vv['id'],
                                    'name'=>$vv['name'],
                                    'status'=>0,
                                ];
                            }
                            array_push($trueTypes,$temp);
                        }

                    } else {
                        $trueTypes = [];
                    }
                    $hour = floor((($oldData->duration) / 3600))>=10? floor((($oldData->duration) / 3600)) : ('0'.floor((($oldData->duration) / 3600)));
                    $minute = floor(((($oldData->duration) % 3600) / 60))>=10?floor(((($oldData->duration) % 3600) / 60)):('0'.floor(((($oldData->duration) % 3600) / 60)));
                    $second = floor(((($oldData->duration) % 3600) % 60))>=10?floor(((($oldData->duration) % 3600) % 60)):('0'.floor(((($oldData->duration) % 3600) % 60)));
                    $data = [
                        'id' => $id,
                        'user_id' => $user,
                        'filmfests_id' => $filmfests_id,
                        'name' => $oldData->name,
                        'english_name' => $oldData->english_name,
                        'duration' => $hour . ':' . $minute . ':' . $second,
                        'is_orther_web' => $oldData->is_orther_web,
                        'copyright' => $oldData->copyright,
                        'is_collective' => $oldData->is_collective,
                        'types' => $trueTypes,
                        'create_people_num' => $oldData->create_people_num,
                        'create_collective_name' => $oldData->create_collective_name?$oldData->create_collective_name:'',
                        'create_start_time' => date('Y.m.d', $oldData->create_start_time),
                        'create_end_time' => date('Y.m.d', $oldData->create_end_time),
                        'production_des' => $oldData->production_des,
                        'production_english_des' => $oldData->production_english_des,
                    ];
                }else{
                    return response()->json(['message'=>'异常'],200);
                }


            }else{
                return response()->json(['message'=>'异常'],200);
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }


    public function pageThree(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user()->id;
            $id = $request->get('id',null);
            $status = $request->get('status',0);
            $filmfests_id = $request->get('filmfests_id',null);
            if(is_null($id)||is_null($filmfests_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $is_over = Application::where('user_id',$user)->where('is_over','=',1)->where('filmfests_id',$filmfests_id)->first();
            if($is_over){
                return response()->json(['message'=>'您已经报过名了'],200);
            }
            $ok = Filmfests::where('id','=',$user)->whereHas('user',function ($q) use($user){
                $q->where('user.id',$user);
            })->first();
            if($ok){
                return response()->json(['message'=>'您是管理者，不可以参与']);
            }
            //  从前一页过来
            if($status == 0){
                $name = $request->get('name',null);
                $english_name = $request->get('english_name',null);
                $duration = $request->get('duration',null);
                $is_orther_web = $request->get('is_orther_web',0);
                $copyright = $request->get('copyright',0);
                $is_collective = $request->get('is_collective',0);
                $units = $request->get('units',null);
                $create_people_num = $request->get('create_people_num',0);
                $create_collective_name = $request->get('create_collective_name','');
                $create_start_time = $request->get('create_start_time',null);
                $create_end_time = $request->get('create_end_time',null);
                $production_des = $request->get('production_des',null);
                $production_english_des = $request->get('production_english_des',null);
                if(is_null($name)||is_null($english_name)||is_null($duration)||is_null($create_start_time)||is_null($create_end_time)||
                    is_null($production_des)||is_null($production_english_des)
                ){
                    return response()->json(['message'=>'有数据为空'],200);
                }
                $duration = explode(':',$duration);
                $duration = ($duration[0]*3600)+($duration[1]*60)+($duration[2]);
                DB::beginTransaction();
                $newData = Application::where('id','=',$id)->first();
                $newData -> name = $name;
                $newData -> english_name = $english_name;
                $newData -> duration = $duration;
                $newData -> is_orther_web = $is_orther_web;
                $newData -> copyright = $copyright;
                $newData -> is_collective = $is_collective;
                $newData -> create_people_num = $create_people_num;
                $newData -> create_collective_name = $create_collective_name;
                $newData -> create_start_time = $create_start_time;
                $newData -> create_end_time = $create_end_time;
                $newData -> production_des = $production_des;
                $newData -> production_english_des = $production_english_des;
                $newData -> save();
                $units = rtrim($units,'|');
                $units = explode('|',$units);
                foreach ($units as $k => $v)
                {
                        $unit_id=explode(':',$v)[0];
                        $unit_status=explode(':',$v)[1];
                        FilmTypeApplication::where('application_id',$id)->delete();
                        if($unit_status==1){
                            $type1 = new FilmTypeApplication;
                            $type1 -> type_id = $unit_id;
                            $type1 -> application_id = $id;
                            $type1 -> time_add = time();
                            $type1 -> time_update = time();
                            $type1 -> save();
                        }
                }
                DB::commit();
                $oldData = Application::where('id','=',$id)->where('creater_name','!=','')->first();
                if($oldData) {
                    $contact_phone = '';
                    $contact_email = '';
                    if ($oldData->contactWay()->first()) {
                        foreach ($oldData->contactWay as $k => $v) {
                            if ($v->type == 0) {
                                $contact_phone .= $v->contact_way . ';';
                            } else {
                                $contact_email .= $v->contact_way . ';';
                            }
                        }
                        $contact_phone = rtrim($contact_phone, ';');
                        $contact_email = rtrim($contact_email, ';');
                    }
                    $data = [
                        'id'=>$id,
                        'user_id'=>$user,
                        'filmfests_id'=>$filmfests_id,
                        'creater_name' => $oldData->creater_name,
                        'director_name' => $oldData->director_name,
                        'photography_name' => $oldData->photography_name,
                        'scriptwriter_name' => $oldData->scriptwriter_name,
                        'cutting_name' => $oldData->cutting_name,
                        'hero_name' => $oldData->hero_name,
                        'heroine_name' => $oldData->heroine_name,
                        'contact_phone' => $contact_phone,
                        'contact_email' => $contact_email,
                        'school' => $oldData->university_name,
                        'major' => $oldData->major,
                        'adviser_name' => $oldData->adviser_name,
                        'adviser_phone' => $oldData->adviser_phone,
                        'enter_school_time' =>date('Y/m/d',$oldData->enter_school_time),
                        'communication_address_country' => $oldData->communication_address_country?$oldData->communication_address_country:'',
                        'communication_address_province' => $oldData->communication_address_province?$oldData->communication_address_province:'',
                        'communication_address_city' => $oldData->communication_address_city?$oldData->communication_address_city:'',
                        'communication_address_county' => $oldData->communication_county?$oldData->communication_county:'',
                        'communication_detail_address' => $oldData->communication_detail_address?$oldData->communication_detail_address:'',
                        'creater_des' => $oldData->creater_des,
                        'other_creater_des' => $oldData->other_creater_des,
                    ];
                }else{
                    $data = [
                        'id'=>$id,
                        'user_id'=>$user,
                        'filmfests_id'=>$filmfests_id,
                        'creater_name'=>'',
                        'director_name'=>'',
                        'photography_name'=>'',
                        'scriptwriter_name'=>'',
                        'cutting_name'=>'',
                        'hero_name'=>'',
                        'heroine_name'=>'',
                        'contact_phone'=>'',
                        'contact_email'=>'',
                        'school'=>'',
                        'major'=>'',
                        'adviser_name'=>'',
                        'adviser_phone'=>'',
                        'enter_school_time'=>'',
                        'communication_address_country' => '',
                        'communication_address_province' => '',
                        'communication_address_city' =>'',
                        'communication_address_county' => '',
                        'communication_detail_address'=>'',
                        'creater_des'=>'',
                        'other_creater_des'=>'',
                    ];
                }
            }elseif($status == 1){
                $oldData = Application::where('id','=',$id)->where('creater_name','!=','')->first();
                $contact_phone = '';
                $contact_email = '';
                if($oldData->contactWay()->first()){
                    foreach ($oldData->contactWay as $k => $v)
                    {
                        if($v->type === 0 ){
                            $contact_phone .=$v->contact_way.';';
                        }else{
                            $contact_email .=$v->contact_way.';';
                        }
                    }
                    $contact_phone = rtrim($contact_phone,';');
                    $contact_email = rtrim($contact_email,';');
                }
                $data = [
                    'id'=>$id,
                    'user_id'=>$user,
                    'filmfests_id'=>$filmfests_id,
                    'creater_name'=>$oldData->creater_name,
                    'director_name'=>$oldData->director_name,
                    'photography_name'=>$oldData->photography_name,
                    'scriptwriter_name'=>$oldData->scriptwriter_name,
                    'cutting_name'=>$oldData->cutting_name,
                    'hero_name'=>$oldData->hero_name,
                    'heroine_name'=>$oldData->heroine_name,
                    'contact_phone'=>$contact_phone,
                    'contact_email'=>$contact_email,
                    'school'=>$oldData->university_name,
                    'major'=>$oldData->major,
                    'adviser_name'=>$oldData->adviser_name,
                    'adviser_phone'=>$oldData->adviser_phone,
                    'enter_school_time'=>date('Y/m/d',$oldData->enter_school_time),
                    'communication_address_country' => $oldData->communication_address_country?$oldData->communication_address_country:'',
                    'communication_address_province' => $oldData->communication_address_province?$oldData->communication_address_province:'',
                    'communication_address_city' => $oldData->communication_address_city?$oldData->communication_address_city:'',
                    'communication_address_county' => $oldData->communication_county?$oldData->communication_county:'',
                    'communication_detail_address' => $oldData->communication_detail_address?$oldData->communication_detail_address:'',
                    'creater_des'=>$oldData->creater_des,
                    'other_creater_des'=>$oldData->other_creater_des,
                ];
            }else{
                return response()->json(['message'=>'异常'],200);

            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function pageFour(Request $request)
    {
        try{
            $id = $request->get('id',null);
            $user = \Auth::guard('api')->user()->id;
            $filmfests_id = $request->get('filmfests_id',null);
            $creater_name = $request->get('creater_name',null);
            $director_name = $request->get('director_name',null);
            $photography_name = $request->get('photography_name',null);
            $scriptwriter_name = $request->get('scriptwriter_name',null);
            $cutting_name = $request->get('cutting_name',null);
            $hero_name = $request->get('hero_name',null);
            $heroine_name = $request->get('heroine_name',null);
            $contact_phone = $request->get('contact_phone',null);
            $contact_email = $request->get('contact_email',null);
            $school = $request->get('school',null);
            $major = $request->get('major',null);
            $adviser_name = $request->get('adviser_name',null);
            $adviser_phone = $request->get('adviser_phone',null);
            $enter_school_time = $request->get('enter_school_time',null);
            $communication_address_country = $request->get('communication_address_country',null);
            $communication_address_province = $request->get('communication_address_province',null);
            $communication_address_city = $request->get('communication_address_city',null);
            $communication_address_county = $request->get('communication_address_county',null);
            $communication_detail_address = $request->get('communication_detail_address',null);
            $creater_des = $request->get('creater_des',null);
            $other_creater_des = $request->get('other_creater_des',null);
            if(is_null($id)||is_null($filmfests_id)||is_null($creater_name)||is_null($director_name)||
                is_null($photography_name)||is_null($scriptwriter_name)||is_null($cutting_name)||
                is_null($hero_name)||is_null($heroine_name)||is_null($contact_phone)||is_null($contact_email)||is_null($school)||
                is_null($major)||is_null($adviser_name)||is_null($adviser_phone)||
                is_null($enter_school_time)||is_null($communication_address_country)||
                is_null($communication_detail_address)||is_null($creater_des)||is_null($other_creater_des)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $is_over = Application::where('user_id',$user)->where('is_over','=',1)->where('filmfests_id',$filmfests_id)->first();
            if($is_over){
                return response()->json(['message'=>'您已经报过名了'],200);
            }
            $ok = Filmfests::where('id','=',$user)->whereHas('user',function ($q) use($user){
                $q->where('user.id',$user);
            })->first();
            if($ok){
                return response()->json(['message'=>'您是管理者，不可以参与']);
            }
            DB::beginTransaction();
            $schoolData = JoinUniversity::where('name','=',$school)->first();
            if($schoolData){
                $school_id = $schoolData->id;
            }else{
                $newSchoolData = new JoinUniversity;
                $newSchoolData -> name = $school;
                $newSchoolData -> time_add = time();
                $newSchoolData -> time_update = time();
                $newSchoolData -> save();
                $school_id = $newSchoolData->id;
            }
            $application = Application::where('id','=',$id)->first();
            $application -> creater_name = $creater_name;
            $application -> director_name = $director_name;
            $application -> photography_name = $photography_name;
            $application -> scriptwriter_name = $scriptwriter_name;
            $application -> cutting_name = $cutting_name;
            $application -> hero_name = $hero_name;
            $application -> heroine_name = $heroine_name;
            $application -> major = $major;
            $application -> university_id = $school_id;
            $application -> university_name = $school;
            $application -> adviser_name = $adviser_name;
            $application -> enter_school_time = $enter_school_time;
            $application -> adviser_phone = $adviser_phone;
            $application -> communication_address_country = $communication_address_country;
            $application -> communication_address_province = $communication_address_province;
            $application -> communication_address_city = $communication_address_city;
            $application -> communication_address_county = $communication_address_county;
            $application -> communication_detail_address = $communication_detail_address;
            $application -> creater_des = $creater_des;
            $application -> other_creater_des = $other_creater_des;
            $application -> save();
            $contact_email = explode(';',$contact_email);
            ApplicationContactWay::where('application_id',$id)->delete();
            foreach ($contact_email as $k => $v)
            {
                $contact_way = new ApplicationContactWay;
                $contact_way -> application_id = $id;
                $contact_way -> contact_way = $v;
                $contact_way -> type = 1;
                $contact_way -> time_add = time();
                $contact_way -> time_update = time();
                $contact_way -> save();
            }

            $contact_phone = explode(';',$contact_phone);
            foreach ($contact_phone as $k => $v)
            {
                $contact_phone_way = new ApplicationContactWay;
                $contact_phone_way -> application_id = $id;
                $contact_phone_way -> contact_way = $v;
                $contact_phone_way -> type = 0;
                $contact_phone_way -> time_add = time();
                $contact_phone_way -> time_update = time();
                $contact_phone_way -> save();
            }
            DB::commit();
            $data = [
                'id'=>$id,
                'user_id'=> $user,
                'filmfests_id'=>$filmfests_id,
                'university_id'=>$application->university_id,
            ];
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_fouond'],404);
        }
    }

    public function pageSubmit(Request $request)
    {
        try{
            $application_id = $request ->get('id',null);                    //  报名表id
            $filmfests_id = $request->get('filmfests_id',null);             //  电影节id
            $user = $user = \Auth::guard('api')->user()->id;          //  用户id
            $movie_clips = $request->get('movie_clips',null);               //  片花地址
            $is_original_clips = $request->get('is_original_clips',0);      //  是否有原视频作为片花
            $is_cloud_clips = $request->get('is_cloud_clips',0);            //  片花是否是云空间的文件
            $clips_id = $request->get('clips_id',null);                     //  原片花视频id
            $is_original_video = $request->get('is_original_video',0);      //  是否原有视频
            $address = $request->get('address',null);                       //  视频地址
            $production_id = $request->get('production_id',null);           //  原视频动态id
            $university_id = $request->get('university_id',null);           //  学校id
            $poster = $request->get('poster',null);                        //  海报地址
            $is_download = $request->get('is_download',1);                  //  能否下载
            $is_reply = $request->get('is_reply',1);                        //  能否评论
            $visible = $request->get('visible',0);                          //  观看权限
            $size = $request->get('size',0);                                //  视频大小
            $is_cloud = $request->get('is_cloud',0);                        //  是否是云空间的视频
            if($visible==3){
                $is_download = 0;
                $is_reply = 0;
                $visible = 2;
            }
            $is_over = Application::where('user_id',$user)->where('is_over','=',1)->where('filmfests_id',$filmfests_id)->first();
            if($is_over){
                return response()->json(['message'=>'您已经报过名了'],200);
            }
            $ok = Filmfests::where('id','=',$user)->whereHas('user',function ($q) use($user){
                $q->where('user.id',$user);
            })->first();
            if($ok){
                return response()->json(['message'=>'您是管理者，不可以参与']);
            }
            //  保存片花和海报
            /**
             * 是否用原视频做片花
             */
            if($is_original_clips == 1){
                if($is_cloud_clips==1){
                    $movie_clips = CloudStorageFile::find($clips_id)->address;
                    $message2[0]['code']=200;
                }else{
                    $movie_clips = Tweet::find($clips_id)->video;
                    $message2[0]['code']=200;
                }
            }else{
                $keys2 = [];
                array_push($keys2,$movie_clips);
                $keyPairs2 = array();
                foreach ($keys2 as $key)
                {
                    $keyPairs2[$key] = $key;
                }
                $srcbucket2 = 'hivideo-video-ects';
                $destbucket2 = 'hivideo-video';
                $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket2,$destbucket2);

            }
            $keys1 = [];
            array_push($keys1,$poster);
            $keyPairs1 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            $srcbucket1 = 'hivideo-img-ects';
            $destbucket1 = 'hivideo-img';
            $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            if($message1[0]['code']==200 && $message2[0]['code']==200){
                if($is_original_video == 1){
                    if((int)$is_cloud===0){
                        DB::beginTransaction();
                        $production = TweetProduction::where('tweet_id','=',$production_id)->first();
                        if(!$production){
                            return response()->json(['message'=>'数据不存在'],200);
                        }
                        $production -> join_university_id = $university_id;
                        $production -> poster = $poster;
                        $production -> movie_clips = $movie_clips;
                        $production -> time_add = time();
                        $production -> time_update = time();
                        $production -> save();
                        $tweet_production_id = $production->id;

                        $filmfestProductionData = new FilmfestsProductions;
                        $filmfestProductionData -> filmfests_id = $filmfests_id;
                        $filmfestProductionData -> tweet_productions_id = $tweet_production_id;
                        $filmfestProductionData -> time_add = time();
                        $filmfestProductionData -> time_update = time();
                        $filmfestProductionData -> status = 3;
                        $filmfestProductionData -> save();

                        $applicationProduction = new TweetProductionApplication;
                        $applicationProduction -> application_id = $application_id;
                        $applicationProduction -> tweet_production_id = $tweet_production_id;
                        $applicationProduction -> time_add = time();
                        $applicationProduction -> time_update = time();
                        $applicationProduction -> save();

                        $channel = 11;
                        ChannelTweet::where('tweet_id',$production_id)->where('channel_id','=',11)->delete();
                        $channelTweet = new ChannelTweet;
                        $channelTweet -> channel_id = $channel;
                        $channelTweet -> tweet_id = $production_id;
                        $channelTweet -> save();

                        if(Application::where('filmfests_id',$filmfests_id)->where('number','>',0)->orderBy('id')->first()){
                            $number = Application::where('filmfests_id',$filmfests_id)->max('number');
                            $number = 1 + $number;
                        }else{
                            $firstData = '00000001';
                            $number_title = Filmfests::where('id','=',$filmfests_id)->first()->number_title;
                            $number = (int)($number_title.$firstData);
                        }
                        $application_form = Application::where('id','=',$application_id)->first();
                        $application_form -> time_update = time();
                        $application_form -> is_over = 1;
                        $application_form -> number = $number;
                        $application_form -> save();

                        $productionFilmfestTypes = FilmTypeApplication::where('application_id','=',$application_id)->get();
                        foreach ($productionFilmfestTypes as $item => $value)
                        {
                            $newProductionFilmType = new ProductionFilmType;
                            $newProductionFilmType -> join_type_id = $value->type_id;
                            $newProductionFilmType -> production_id = $tweet_production_id;
                            $newProductionFilmType -> time_add = time();
                            $newProductionFilmType -> time_update = time();
                            $newProductionFilmType -> save();
                        }

                        DB::commit();
                        return response()->json(['message'=>'success'],200);
                    }else{
                        $cloud_file = CloudStorageFile::where('id','=',$production_id)->first();
                        if($cloud_file){
                            $address = $cloud_file->address;
                            $url = "http://video.ects.cdn.hivideo.com/".$address.'?avinfo';
                            $html = file_get_contents($url);
                            $rule1 = "/\"width\":.*?,/";
                            $rule2 = "/\"height\":.*?,/";
                            $rule3 = "/\"duration\":.*?,/";
                            preg_match($rule1,$html,$width);
                            preg_match($rule2,$html,$height);
                            preg_match($rule3,$html,$duration);
                            $width =rtrim( explode(' ',$width[0])[1],',');
                            $height = rtrim(explode(' ',$height[0])[1],',');
                            $duration = (int)trim(rtrim(explode(' ',$duration[0])[1],','),'"');
                            DB::beginTransaction();
                            $newTweet = new Tweet;
                            $newTweet -> video = $address;
                            $newTweet -> screen_shot = $cloud_file->screenshot;
                            $newTweet -> size = $cloud_file->size;
                            $newTweet -> updated_at = time();
                            $newTweet -> created_at = time();
                            $newTweet -> user_id = $user;
                            $newTweet -> type = 3;
                            $newTweet -> is_download = $is_download;
                            $newTweet -> is_reply = $is_reply;
                            $newTweet -> visible = $visible;
                            $newTweet -> duration = $duration;
                            $newTweet -> save();
                            DB::commit();
                            DB::beginTransaction();
                            $id = $newTweet->id;
                            $production = Tweet::find($id);
                            $address = $production->video;
                            $address = str_replace('v.cdn.hivdeo.com/', '', $address);
                            array_push($keys1,$address);
                            $keyPairs1 = array();
                            foreach($keys1 as $key)
                            {
                                $keyPairs1[$key] = $key;
                            }
                            $srcbucket = 'hivideo-video';
                            $destbucket = 'hivideo-video';
                            //  扩展名
                            $ex = pathinfo($address, PATHINFO_EXTENSION);
                            //  分辨率
                            $fenBianLv = CloudStorage::getWidthAndHeight($address);
                            //  移动到正式空间  还没改名字
                            $address = 'copy_'.$address;
                            $message = CloudStorage::copyfile2($keyPairs1,$srcbucket,$destbucket,$address);
                            if($message[0]['code']==200){
                                //  产生新名字
                                $newAddress = str_replace('.'.$ex, '_'.$fenBianLv.'.'.$ex, $address);
                                //  改名字
                                $move = CloudStorage::reNameFile($destbucket,$address,$destbucket,$newAddress);
                                if($move){
                                    $cover = CloudStorage::saveCover($address,$newAddress,$width,$height);
                                    if($cover){
                                        $bb = 'img.cdn.hivideo.com/'.$address.'vframe-001_'.$cover.'_.jpg';
                                        $production -> screen_shot = $bb;
                                        $production -> active = 0;
                                        $production -> video =$newAddress;
                                        $production -> save();
                                        DB::commit();


                                    }else{
                                        $production -> active =8;
                                        $production -> updated_at = time();
                                        $production -> save();
                                        $production -> error_reason = '保存图片失败';
                                        DB::commit();
                                        return response()->json(['message'=>'保存图片失败'],200);
                                    }
                                }else{
                                    $production -> active =8;
                                    $production -> updated_at = time();
                                    $production -> save();
                                    $production -> error_reason = '重命名失败';
                                    DB::commit();
                                    return response()->json(['message'=>'重命名失败'],200);
                                }
                                DB::beginTransaction();
                                $production = Tweet::find($id);
                                $address = $production->video;
                                $bucket = 'hivideo-video';
                                $key = $address;
                                if($width>=1280){
                                    $message = CloudStorage::transcoding($bucket,$key,$width,$height,$choice=1);
                                    if($message){
                                        $finallyAddress = str_replace($ex,'m3u8',$newAddress);
                                        $transcoding_video = str_replace('.'.$ex,'_'.$ex.'.m3u8',$key);
//                        $production -> transcoding_video = 'v.cdn.hivideo.com/'.$newAddress.'.m3u8';
                                        $production -> transcoding_video = 'v.cdn.hivideo.com/'.$transcoding_video;
                                        $production -> transcoding_id = $message;
                                        $production -> video ='v.cdn.hivideo.com/'.$newAddress;
                                        $production -> high_video = 'v.cdn.hivideo.com/high/'.$finallyAddress;
                                        $production -> norm_video = 'v.cdn.hivideo.com/norm/'.$finallyAddress;
                                        $production -> video_m3u8 ='v.cdn.hivideo.com/'.$finallyAddress;
                                        $production -> is_transcod = 1;
                                        $production -> updated_at = time();
                                        $production -> save();

                                        $channel = 11;
                                        ChannelTweet::where('tweet_id',$id)->where('channel_id','=',11)->delete();
                                        $channelTweet = new ChannelTweet;
                                        $channelTweet -> channel_id = $channel;
                                        $channelTweet -> tweet_id = $id;
                                        $channelTweet -> save();

                                        if(Application::where('filmfests_id',$filmfests_id)->where('number','>',0)->orderBy('id')->first()){
                                            $number = Application::where('filmfests_id',$filmfests_id)->max('number');
                                            $number = 1 + $number;
                                        }else{
                                            $firstData = '00000001';
                                            $number_title = Filmfests::where('id','=',$filmfests_id)->first()->number_title;
                                            $number = (int)($number_title.$firstData);
                                        }
                                        $application_form = Application::where('id','=',$application_id)->first();
                                        $application_form -> time_update = time();
                                        $application_form -> is_over = 1;
                                        $application_form -> number = $number;
                                        $application_form -> save();

                                        $childData = new TweetProduction;
                                        $childData -> tweet_id = $id;
                                        $childData -> is_current = 1;
                                        $childData -> time_add = time();
                                        $childData -> time_update = time();
                                        $childData -> poster = $poster;
                                        $childData -> movie_clips = $movie_clips;
                                        $childData -> join_university_id = $university_id;
                                        $childData -> save();

                                        $applicationProduction = new TweetProductionApplication;
                                        $applicationProduction -> application_id = $application_id;
                                        $applicationProduction -> tweet_production_id = $childData->id;
                                        $applicationProduction -> time_add = time();
                                        $applicationProduction -> time_update = time();
                                        $applicationProduction -> save();

                                        $productionFilmfestTypes = FilmTypeApplication::where('application_id','=',$application_id)->get();

                                        foreach ($productionFilmfestTypes as $item => $value)
                                        {
                                            $newProductionFilmType = new ProductionFilmType;
                                            $newProductionFilmType -> join_type_id = $value->type_id;
                                            $newProductionFilmType -> production_id = $childData->id;
                                            $newProductionFilmType -> time_add = time();
                                            $newProductionFilmType -> time_update = time();
                                            $newProductionFilmType -> save();
                                        }

                                        $filmfestProductionData = new FilmfestsProductions;
                                        $filmfestProductionData -> filmfests_id = $filmfests_id;
                                        $filmfestProductionData -> tweet_productions_id = $childData->id;
                                        $filmfestProductionData -> time_add = time();
                                        $filmfestProductionData -> time_update = time();
                                        $filmfestProductionData -> status = 3;
                                        $filmfestProductionData -> save();

                                        $oldFilmfestUniversity = FilmfestUniversity::where('university_id',$university_id)->where('filmfest_id',$filmfests_id)->first();
                                        if(!$oldFilmfestUniversity){
                                            $newFilmfestUniversity = new FilmfestUniversity;
                                            $newFilmfestUniversity -> university_id = $university_id;
                                            $newFilmfestUniversity -> filmfest_id = $filmfests_id;
                                            $newFilmfestUniversity -> time_add = time();
                                            $newFilmfestUniversity -> time_update = time();
                                            $newFilmfestUniversity -> save();
                                        }

                                        $content = new TweetContent;
                                        $content -> tweet_id = $id;
                                        $content -> content = $application_form->production_des;
                                        $content -> created_at = time();
                                        $content -> updated_at = time();
                                        $content ->save();

                                        DB::commit();
                                        return response()->json(['message'=>'success'],200);

                                    }else{
                                        $production -> active =8;
                                        $production -> updated_at = time();
                                        $production -> save();
                                        $production -> error_reason = '转码失败';
                                        DB::commit();
                                        return response()->json(['message'=>'转码失败'],200);
                                    }
                                }else{
                                    $message = CloudStorage::transcoding($bucket,$key,$width,$height,$choice=0);
                                    if($message){
                                        $finallyAddress = str_replace($ex,'m3u8',$newAddress);
                                        $production -> transcoding_id = $message;
                                        $transcoding_video = str_replace('.'.$ex,'_'.$ex.'.m3u8',$key);
                                        $production -> transcoding_video = 'v.cdn.hivideo.com/'.$transcoding_video;
                                        $production -> video ='v.cdn.hivideo.com/'.$newAddress;
                                        $production -> video_m3u8 ='v.cdn.hivideo.com/'.$finallyAddress;
                                        $production -> is_transcod = 1;
                                        $production -> updated_at = time();
                                        $production -> save();


                                        $channel = 11;
                                        ChannelTweet::where('tweet_id',$id)->where('channel_id','=',11)->delete();
                                        $channelTweet = new ChannelTweet;
                                        $channelTweet -> channel_id = $channel;
                                        $channelTweet -> tweet_id = $id;
                                        $channelTweet -> save();

                                        if(Application::where('filmfests_id',$filmfests_id)->where('number','>',0)->orderBy('id')->first()){
                                            $number = Application::where('filmfests_id',$filmfests_id)->max('number');
                                            $number = 1 + $number;
                                        }else{
                                            $firstData = '00000001';
                                            $number_title = Filmfests::where('id','=',$filmfests_id)->first()->number_title;
                                            $number = (int)($number_title.$firstData);
                                        }
                                        $application_form = Application::where('id','=',$application_id)->first();
                                        $application_form -> time_update = time();
                                        $application_form -> is_over = 1;
                                        $application_form -> number = $number;
                                        $application_form -> save();

                                        $childData = new TweetProduction;
                                        $childData -> tweet_id = $id;
                                        $childData -> is_current = 1;
                                        $childData -> time_add = time();
                                        $childData -> time_update = time();
                                        $childData -> poster = $poster;
                                        $childData -> movie_clips = $movie_clips;
                                        $childData -> join_university_id = $university_id;
                                        $childData -> save();

                                        $applicationProduction = new TweetProductionApplication;
                                        $applicationProduction -> application_id = $application_id;
                                        $applicationProduction -> tweet_production_id = $childData->id;
                                        $applicationProduction -> time_add = time();
                                        $applicationProduction -> time_update = time();
                                        $applicationProduction -> save();


                                        $productionFilmfestTypes = FilmTypeApplication::where('application_id','=',$application_id)->get();
                                        foreach ($productionFilmfestTypes as $item => $value)
                                        {
                                            $newProductionFilmType = new ProductionFilmType;
                                            $newProductionFilmType -> join_type_id = $value->type_id;
                                            $newProductionFilmType -> production_id = $childData->id;;
                                            $newProductionFilmType -> time_add = time();
                                            $newProductionFilmType -> time_update = time();
                                            $newProductionFilmType -> save();
                                        }

                                        $oldFilmfestUniversity = FilmfestUniversity::where('university_id',$university_id)->where('filmfest_id',$filmfests_id)->first();
                                        if(!$oldFilmfestUniversity){
                                            $newFilmfestUniversity = new FilmfestUniversity;
                                            $newFilmfestUniversity -> university_id = $university_id;
                                            $newFilmfestUniversity -> filmfest_id = $filmfests_id;
                                            $newFilmfestUniversity -> time_add = time();
                                            $newFilmfestUniversity -> time_update = time();
                                            $newFilmfestUniversity -> save();
                                        }

                                        $content = new TweetContent;
                                        $content -> tweet_id = $id;
                                        $content -> content = $application_form->production_des;
                                        $content -> created_at = time();
                                        $content -> updated_at = time();
                                        $content ->save();
                                        DB::commit();
                                        return response()->json(['message'=>'success'],200);
                                    }else{
                                        $production -> active =8;
                                        $production -> updated_at = time();
                                        $production -> save();
                                        $production -> error_reason = '转码失败';
                                        DB::commit();
                                        return response()->json(['message'=>'转码失败'],200);
                                    }
                                }


                            }else{
                                $production -> active =8;
                                $production -> updated_at = time();
                                $production -> save();
                                $production -> error_reason = '移动失败';
                                DB::commit();
                                return response()->json(['message'=>'移动失败'],200);
                            }

                        }else{
                            return response()->json(['message'=>'数据不存在'],200);
                        }
                    }

                }else{
                    if(is_null($address)||is_null($size)){
                        return response()->json(['error'=>'数据不合法'],200);
                    }
                    $url = "http://video.ects.cdn.hivideo.com/".$address.'?avinfo';
                    $html = file_get_contents($url);
                    $rule1 = "/\"width\":.*?,/";
                    $rule2 = "/\"height\":.*?,/";
                    $rule3 = "/\"duration\":.*?,/";
                    preg_match($rule1,$html,$width);
                    preg_match($rule2,$html,$height);
                    preg_match($rule3,$html,$duration);
                    $width =rtrim( explode(' ',$width[0])[1],',');
                    $height = rtrim(explode(' ',$height[0])[1],',');
                    $duration = (int)trim(rtrim(explode(' ',$duration[0])[1],','),'"');
                    DB::beginTransaction();
                    $tweet = new Tweet;
                    $tweet -> created_at = time();
                    $tweet -> updated_at = time();
                    $tweet -> user_id = $user;
                    $tweet -> type = 3;
                    $tweet -> active = 7;
                    $tweet -> is_download = $is_download;
                    $tweet -> is_reply = $is_reply;
                    $tweet -> size = $size;
                    $tweet -> video = $address;
                    $tweet -> duration = $duration;
                    $tweet -> visible = $visible;
                    $tweet -> save();
                    DB::commit();
                    $id = $tweet->id;
                    $keys1 = [];
                    DB::beginTransaction();
                    $production = Tweet::find($id);
                    $address = $production->video;
                    array_push($keys1,$address);
                    $keyPairs1 = array();
                    foreach($keys1 as $key)
                    {
                        $keyPairs1[$key] = $key;
                    }
                    $srcbucket = 'hivideo-video-ects';
                    $destbucket = 'hivideo-video';
                    //  扩展名
                    $ex = pathinfo($address, PATHINFO_EXTENSION);
                    //  分辨率
                    $fenBianLv = CloudStorage::getWidthAndHeight($address);
                    //  移动到正式空间  还没改名字
                    $message = CloudStorage::copyfile($keyPairs1,$srcbucket,$destbucket);
                    if($message[0]['code']==200){
                        //  产生新名字
                        $newAddress = str_replace('.'.$ex, '_'.$fenBianLv.'.'.$ex, $address);
                        //  改名字
                        $move = CloudStorage::reNameFile($destbucket,$address,$destbucket,$newAddress);
                        if($move){
                            $production = Tweet::find($id);
                            $production -> updated_at = time();
                            $production -> save();
                            DB::commit();
                            $address = $production->video;
                            $cover = CloudStorage::saveCover($address,$newAddress,$width,$height);
                            if($cover){
                                $bb = 'img.cdn.hivideo.com/'.$address.'vframe-001_'.$cover.'_.jpg';
                                $production = Tweet::find($id);
                                $production -> screen_shot = $bb;
                                $production -> active = 0;
                                $production -> video =$newAddress;
                                $production -> save();
                                DB::commit();


                            }else{
                                $production -> active =8;
                                $production -> updated_at = time();
                                $production -> save();
                                $production -> error_reason = '保存图片失败';
                                DB::commit();
                                return response()->json(['message'=>'保存图片失败'],200);
                            }
                        }else{
                            $production -> active =8;
                            $production -> updated_at = time();
                            $production -> save();
                            $production -> error_reason = '重命名失败';
                            DB::commit();
                            return response()->json(['message'=>'重命名失败'],200);
                        }
                        DB::beginTransaction();
                        $production = Tweet::find($id);
                        $address = $production->video;
                        $bucket = 'hivideo-video';
                        $key = $address;
                        if($width>=1280){
                            $message = CloudStorage::transcoding($bucket,$key,$width,$height,$choice=1);
                            if($message){
                                $finallyAddress = str_replace($ex,'m3u8',$newAddress);
                                $transcoding_video = str_replace('.'.$ex,'_'.$ex.'.m3u8',$key);
//                        $production -> transcoding_video = 'v.cdn.hivideo.com/'.$newAddress.'.m3u8';
                                $production -> transcoding_video = 'v.cdn.hivideo.com/'.$transcoding_video;
                                $production -> transcoding_id = $message;
                                $production -> video ='v.cdn.hivideo.com/'.$newAddress;
                                $production -> high_video = 'v.cdn.hivideo.com/high/'.$finallyAddress;
                                $production -> norm_video = 'v.cdn.hivideo.com/norm/'.$finallyAddress;
                                $production -> video_m3u8 ='v.cdn.hivideo.com/'.$finallyAddress;
                                $production -> is_transcod = 1;
                                $production -> updated_at = time();
                                $production -> save();

                                $channel = 11;
                                ChannelTweet::where('tweet_id',$id)->where('channel_id','=',11)->delete();
                                $channelTweet = new ChannelTweet;
                                $channelTweet -> channel_id = $channel;
                                $channelTweet -> tweet_id = $id;
                                $channelTweet -> save();

                                if(Application::where('filmfests_id',$filmfests_id)->where('number','>',0)->orderBy('id')->first()){
                                    $number = Application::where('filmfests_id',$filmfests_id)->max('number');
                                    $number = 1 + $number;
                                }else{
                                    $firstData = '00000001';
                                    $number_title = Filmfests::where('id','=',$filmfests_id)->first()->number_title;
                                    $number = (int)($number_title.$firstData);
                                }
                                $application_form = Application::where('id','=',$application_id)->first();
                                $application_form -> time_update = time();
                                $application_form -> is_over = 1;
                                $application_form -> number = $number;
                                $application_form -> save();

                                $childData = new TweetProduction;
                                $childData -> tweet_id = $id;
                                $childData -> is_current = 1;
                                $childData -> time_add = time();
                                $childData -> time_update = time();
                                $childData -> poster = $poster;
                                $childData -> movie_clips = $movie_clips;
                                $childData -> join_university_id = $university_id;
                                $childData -> save();

                                $applicationProduction = new TweetProductionApplication;
                                $applicationProduction -> application_id = $application_id;
                                $applicationProduction -> tweet_production_id = $childData->id;
                                $applicationProduction -> time_add = time();
                                $applicationProduction -> time_update = time();
                                $applicationProduction -> save();


                                $productionFilmfestTypes = FilmTypeApplication::where('application_id','=',$application_id)->get();
                                foreach ($productionFilmfestTypes as $item => $value)
                                {
                                    $newProductionFilmType = new ProductionFilmType;
                                    $newProductionFilmType -> join_type_id = $value->type_id;
                                    $newProductionFilmType -> production_id = $childData->id;;
                                    $newProductionFilmType -> time_add = time();
                                    $newProductionFilmType -> time_update = time();
                                    $newProductionFilmType -> save();
                                }

                                $filmfestProductionData = new FilmfestsProductions;
                                $filmfestProductionData -> filmfests_id = $filmfests_id;
                                $filmfestProductionData -> tweet_productions_id = $childData->id;;
                                $filmfestProductionData -> time_add = time();
                                $filmfestProductionData -> time_update = time();
                                $filmfestProductionData -> status = 3;
                                $filmfestProductionData -> save();

                                $oldFilmfestUniversity = FilmfestUniversity::where('university_id',$university_id)->where('filmfest_id',$filmfests_id)->first();
                                if(!$oldFilmfestUniversity){
                                    $newFilmfestUniversity = new FilmfestUniversity;
                                    $newFilmfestUniversity -> university_id = $university_id;
                                    $newFilmfestUniversity -> filmfest_id = $filmfests_id;
                                    $newFilmfestUniversity -> time_add = time();
                                    $newFilmfestUniversity -> time_update = time();
                                    $newFilmfestUniversity -> save();
                                }

                                $content = new TweetContent;
                                $content -> tweet_id = $id;
                                $content -> content = $application_form->production_des;
                                $content -> created_at = time();
                                $content -> updated_at = time();
                                $content ->save();

                                DB::commit();
                                return response()->json(['message'=>'success'],200);

                            }else{
                                $production -> active =8;
                                $production -> updated_at = time();
                                $production -> save();
                                $production -> error_reason = '转码失败';
                                DB::commit();
                                return response()->json(['message'=>'转码失败'],200);
                            }
                        }else{
                            $message = CloudStorage::transcoding($bucket,$key,$width,$height,$choice=0);
                            if($message){
                                $finallyAddress = str_replace($ex,'m3u8',$newAddress);
                                $production -> transcoding_id = $message;
                                $transcoding_video = str_replace('.'.$ex,'_'.$ex.'.m3u8',$key);
                                $production -> transcoding_video = 'v.cdn.hivideo.com/'.$transcoding_video;
                                $production -> video ='v.cdn.hivideo.com/'.$newAddress;
                                $production -> video_m3u8 ='v.cdn.hivideo.com/'.$finallyAddress;
                                $production -> is_transcod = 1;
                                $production -> updated_at = time();
                                $production -> save();


                                $channel = 11;
                                ChannelTweet::where('tweet_id',$id)->where('channel_id','=',11)->delete();
                                $channelTweet = new ChannelTweet;
                                $channelTweet -> channel_id = $channel;
                                $channelTweet -> tweet_id = $id;
                                $channelTweet -> save();

                                if(Application::where('filmfests_id',$filmfests_id)->where('number','>',0)->orderBy('id')->first()){
                                    $number = Application::where('filmfests_id',$filmfests_id)->max('number');
                                    $number = 1 + $number;
                                }else{
                                    $firstData = '00000001';
                                    $number_title = Filmfests::where('id','=',$filmfests_id)->first()->number_title;
                                    $number = (int)($number_title.$firstData);
                                }
                                $application_form = Application::where('id','=',$application_id)->first();
                                $application_form -> time_update = time();
                                $application_form -> is_over = 1;
                                $application_form -> number = $number;
                                $application_form -> save();

                                $childData = new TweetProduction;
                                $childData -> tweet_id = $id;
                                $childData -> is_current = 1;
                                $childData -> time_add = time();
                                $childData -> time_update = time();
                                $childData -> poster = $poster;
                                $childData -> movie_clips = $movie_clips;
                                $childData -> join_university_id = $university_id;
                                $childData -> save();

                                $applicationProduction = new TweetProductionApplication;
                                $applicationProduction -> application_id = $application_id;
                                $applicationProduction -> tweet_production_id = $childData->id;
                                $applicationProduction -> time_add = time();
                                $applicationProduction -> time_update = time();
                                $applicationProduction -> save();

                                $productionFilmfestTypes = FilmTypeApplication::where('application_id','=',$application_id)->get();
                                foreach ($productionFilmfestTypes as $item => $value)
                                {
                                    $newProductionFilmType = new ProductionFilmType;
                                    $newProductionFilmType -> join_type_id = $value->type_id;
                                    $newProductionFilmType -> production_id = $childData->id;;
                                    $newProductionFilmType -> time_add = time();
                                    $newProductionFilmType -> time_update = time();
                                    $newProductionFilmType -> save();
                                }
                                $oldFilmfestUniversity = FilmfestUniversity::where('university_id',$university_id)->where('filmfest_id',$filmfests_id)->first();
                                if(!$oldFilmfestUniversity){
                                    $newFilmfestUniversity = new FilmfestUniversity;
                                    $newFilmfestUniversity -> university_id = $university_id;
                                    $newFilmfestUniversity -> filmfest_id = $filmfests_id;
                                    $newFilmfestUniversity -> time_add = time();
                                    $newFilmfestUniversity -> time_update = time();
                                    $newFilmfestUniversity -> save();
                                }

                                $filmfestProductionData = new FilmfestsProductions;
                                $filmfestProductionData -> filmfests_id = $filmfests_id;
                                $filmfestProductionData -> tweet_productions_id = $childData->id;;
                                $filmfestProductionData -> time_add = time();
                                $filmfestProductionData -> time_update = time();
                                $filmfestProductionData -> status = 3;
                                $filmfestProductionData -> save();

                                $content = new TweetContent;
                                $content -> tweet_id = $id;
                                $content -> content = $application_form->production_des;
                                $content -> created_at = time();
                                $content -> updated_at = time();
                                $content ->save();
                                DB::commit();
                                return response()->json(['message'=>'success'],200);
                            }else{
                                $production -> active =8;
                                $production -> updated_at = time();
                                $production -> save();
                                $production -> error_reason = '转码失败';
                                DB::commit();
                                return response()->json(['message'=>'转码失败'],200);
                            }
                        }


                    }else{
                        $production -> active =8;
                        $production -> updated_at = time();
                        $production -> save();
                        $production -> error_reason = '移动失败';
                        DB::commit();
                        return response()->json(['message'=>'移动失败'],200);
                    }
                }
            }else{
                return response()->json(['message'=>'异常'],200);
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function unit(Request $request)
    {
        try{
            $filmfests_id = $request->get('filmfests_id',null);
            if(is_null($filmfests_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $units = FilmfestFilmType::whereHas('filmFests',function ($q) use($filmfests_id){
                $q->where('filmfests.id','=',$filmfests_id);
            })->get();
            $showUnit = [];
            foreach ($units as $k => $v) {
                array_push($showUnit,['id'=>$v->id,'name'=>$v->name]);
            }
            return response()->json(['data'=>$showUnit],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function visible()
    {
        $data = [
            [
                'label'=>0,
                'des'=>'全部人可见'
            ],
            [
                'label'=>1,
                'des'=>'朋友圈可见'
            ],
            [
                'label'=>2,
                'des'=>'仅自己可见'
            ],
            [
                'label'=>3,
                'des'=>'仅评审团可见'
            ],
        ];

        return response()->json(['data'=>$data],200);
    }

    public function oldTweet(Request $request)
    {
        try{
            $type = $request->get('type',0);
            $page = $request->get('page',1);
            $user = $user = \Auth::guard('api')->user()->id;
            if($type == 0){
                $mainData = Tweet::where('active','<',2)->where('type','=',3)->where('user_id','=',$user)
                    ->limit($page*($this->paginate))->get();
                $data = [];
                if($mainData->count()>0){
                    foreach ($mainData as $k => $v)
                    {
                        $tempData = [
                            'id'=>$v->id,
                            'name'=>$v->name,
                            'des'=>$v->hasOneContent()->first()?$v->hasOneContent->content:'',
                            'cover'=>$v->screen_shot,
                            'size'=>$v->size,
                            'duration'=>floor(($v->duration/60)).':'.(($v->duration)%60),
                            'time'=>$v->created_at,
                            'is_cloud'=>0,
                        ];
                        array_push($data,$tempData);
                    }
                }else{
                    return response()->json(['message'=>'空空如也哎，快去存几个吧！']);
                }
            }else{
                $mainData = CloudStorageFile::where('folder_id','=',$type)->where('active','=',1)
                    ->where('type','=',2)->limit($page*($this->paginate))->get();
                if($mainData->count()>0){
                    foreach ($mainData as $k => $v)
                    {
                        $tempData = [
                            'id'=>$v->id,
                            'name'=>$v->name,
                            'des'=>'',
                            'cover'=>$v->screenshot,
                            'size'=>$v->size,
                            'duration'=>floor(($v->duration/60)).':'.(($v->duration)%60),
                            'time'=>date('Y-m-d H:i:s',$v->time_add),
                            'is_cloud'=>1,
                        ];
                        array_push($data,$tempData);
                    }
                }else{
                    return response()->json(['message'=>'空空如也哎，快去存几个吧！']);
                }
            }
            return response()->json(['data'=>$data],200);

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function old_tweet_type()
    {
        try{
            $user = $user = \Auth::guard('api')->user()->id;
            $type = [
                [
                    'label'=>0,
                    'des'=>'作品'
                ]
            ];
            $mainData = CloudStorageFolder::where('user_id','=',$user)->get();
            if($mainData->count()>0){
                foreach ($mainData as $k => $v)
                {
                    $tempData = [
                        'label'=>$v->id,
                        'des'=>'云空间-'.$v->name,
                    ];
                    array_push($type,$tempData);
                }
            }
            return response()->json(['data'=>$type],200);
        }catch (ModelNotFoundException $q) {
            return response()->json(['error'=>'not_found'],200);
        }
    }

}
