<?php

namespace App\Http\Controllers\NewWeb\Application;

use App\Models\ChannelTweet;
use App\Models\Filmfest\Application;
use App\Models\Filmfest\ApplicationContactWay;
use App\Models\Filmfest\FilmTypeApplication;
use App\Models\Filmfest\JoinUniversity;
use App\Models\Filmfest\TweetProductionApplication;
use App\Models\FilmfestFilmfestType;
use App\Models\FilmfestFilmType;
use App\Models\Filmfests;
use App\Models\FilmfestsProductions;
use App\Models\ProductionFilmType;
use App\Models\Tweet;
use App\Models\TweetProduction;
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
            $id = $request -> get('id');
            if($filmfest_id){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $status = $request->get('status',0);

            $protocol_address = Filmfests::where('id','=',$filmfest_id)->first()->protocol;
            $user = \Auth::guard('api')->user()->id;
            if($status == 0){
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
                    $newApplication -> festfilms_id = $filmfest_id;
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
            }elseif ($status == 1){
                $oldData = ApplicationContactWay::where('id','=',$id)->first();
                $data = [
                    'id'=>$oldData->id,
                    'is_student'=>$oldData->is_student,
                    'protocol'=>$oldData->protocol,
                    'papers'=>$oldData->papers,
                    'protocol_address' => $protocol_address,
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
            if($status == 0){
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

                $oldData = Application::where('id','=',$id)->where('name','!=','')->get();
                if($oldData) {
                    if ($oldData->filmType()->first()) {
                        $types = [];
                        foreach ($oldData->filmType as $k => $v) {
                            if ($v->filmfests->id == $id) {
                                array_push($types, $v->name);
                            } else {
                                continue;
                            }
                        }
                        $trueTypes = [];
                        if (in_array('剧情片', $types)) {
                            array_push($trueTypes, ['story' => '1']);
                        } else {
                            array_push($trueTypes, ['story' => '0']);
                        }
                        if (in_array('纪录片', $types)) {
                            array_push($trueTypes, ['documentary' => '1']);
                        } else {
                            array_push($trueTypes, ['documentary' => '0']);
                        }
                        if (in_array('动画短片', $types)) {
                            array_push($trueTypes, ['cartoon' => '1']);
                        } else {
                            array_push($trueTypes, ['cartoon' => '0']);
                        }
                        if (in_array('实验短片', $types)) {
                            array_push($trueTypes, ['short_film' => '1']);
                        } else {
                            array_push($trueTypes, ['short_film' => '0']);
                        }
                        if (in_array('联合特别单元', $types)) {
                            array_push($trueTypes, ['particularly_union' => '1']);
                        } else {
                            array_push($trueTypes, ['particularly_union' => '0']);
                        }
                    } else {
                        $trueTypes = [];
                    }
                    $data = [
                        'id' => $id,
                        'user_id' => $user,
                        'filmfests_id' => $filmfests_id,
                        'name' => $oldData->name,
                        'english_name' => $oldData->english_name,
                        'duration' => (($oldData->duration) / 3600) . ':' . ((($oldData->duration) % 3600) / 60) . ':' . ((($oldData->duration) % 3600) % 60),
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

            }elseif ($status == 1){
                $oldData = Application::where('id','=',$id)->where('name','!=','')->get();
                if ($oldData->filmType()->first()) {
                    $types = [];
                    foreach ($oldData->filmType as $k => $v) {
                        if ($v->filmfests->id == $id) {
                            array_push($types, $v->name);
                        } else {
                            continue;
                        }
                    }
                    $trueTypes = [];
                    if (in_array('剧情片', $types)) {
                        array_push($trueTypes, ['story' => '1']);
                    } else {
                        array_push($trueTypes, ['story' => '0']);
                    }
                    if (in_array('纪录片', $types)) {
                        array_push($trueTypes, ['documentary' => '1']);
                    } else {
                        array_push($trueTypes, ['documentary' => '0']);
                    }
                    if (in_array('动画短片', $types)) {
                        array_push($trueTypes, ['cartoon' => '1']);
                    } else {
                        array_push($trueTypes, ['cartoon' => '0']);
                    }
                    if (in_array('实验短片', $types)) {
                        array_push($trueTypes, ['short_film' => '1']);
                    } else {
                        array_push($trueTypes, ['short_film' => '0']);
                    }
                    if (in_array('联合特别单元', $types)) {
                        array_push($trueTypes, ['particularly_union' => '1']);
                    } else {
                        array_push($trueTypes, ['particularly_union' => '0']);
                    }
                } else {
                    $trueTypes = [];
                }
                $data = [
                    'id' => $id,
                    'user_id' => $user,
                    'filmfests_id' => $filmfests_id,
                    'name' => $oldData->name,
                    'english_name' => $oldData->english_name,
                    'duration' => (($oldData->duration) / 3600) . ':' . ((($oldData->duration) % 3600) / 60) . ':' . ((($oldData->duration) % 3600) % 60),
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
                return response()->json(['message'=>'异常'],200);
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
            $status = $request->get('status',0);
            $filmfests_id = $request->get('filmfests_id',null);
            if(is_null($id)||is_null($filmfests_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            //  从前一页过来
            if($status == 0){
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
                DB::beginTransaction();
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
                    $type2_id = FilmfestFilmType::where('name','=','纪录片')->first()->id;
                    $type2 = new FilmTypeApplication;
                    $type2 -> type_id = $type2_id;
                    $type2 -> application_id = $id;
                    $type2 -> time_add = time();
                    $type2 -> time_update = time();
                    $type2 -> save();
                }
                if($is_cartoon == 1){
                    $type3_id = FilmfestFilmType::where('name','=','动画片')->first()->id;
                    $type3 = new FilmTypeApplication;
                    $type3 -> type_id = $type3_id;
                    $type3 -> application_id = $id;
                    $type3 -> time_add = time();
                    $type3 -> time_update = time();
                    $type3 -> save();
                }
                if($is_short_film == 1){
                    $type4_id = FilmfestFilmType::where('name','=','实验短片')->first()->id;
                    $type4 = new FilmTypeApplication;
                    $type4 -> type_id = $type4_id;
                    $type4 -> application_id = $id;
                    $type4 -> time_add = time();
                    $type4 -> time_update = time();
                    $type4 -> save();
                }
                if($is_particularly_union == 1){
                    $type5_id = FilmfestFilmType::where('name','=','联合特别单元')->first()->id;
                    $type5 = new FilmTypeApplication;
                    $type5 -> type_id = $type5_id;
                    $type5 -> application_id = $id;
                    $type5 -> time_add = time();
                    $type5 -> time_update = time();
                    $type5 -> save();
                }
                DB::commit();
                $oldData = Application::where('id','=',$id)->where('creater_name','!=','')->first();
                if($oldData) {
                    $contact_phone = '';
                    $contact_email = '';
                    if ($oldData->contactWay()->first()) {
                        foreach ($oldData->contactWay as $k => $v) {
                            if ($v->type == 0) {
                                $contact_phone .= $v->contact_way . '_';
                            } else {
                                $contact_email .= $v->contact_way . '_';
                            }
                        }
                        $contact_phone = rtrim($contact_phone, '_');
                        $contact_email = rtrim($contact_email, '_');
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
                        'enter_school_time' => $oldData->enter_school_time,
                        'communication_address' => $oldData->communication_address,
                        'communication_detail_address' => $oldData->communication_detail_address,
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
                        'communication_address'=>'',
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
                        if($v->type == 0 ){
                            $contact_phone .=$v->contact_way.'_';
                        }else{
                            $contact_email .=$v->contact_way.'_';
                        }
                    }
                    $contact_phone = rtrim($contact_phone,'_');
                    $contact_email = rtrim($contact_email,'_');
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
                    'enter_school_time'=>$oldData->enter_school_time,
                    'communication_address'=>$oldData->communication_address,
                    'communication_detail_address'=>$oldData->communication_detail_address,
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
            $communication_address = $request->get('communication_address',null);
            $communication_detail_address = $request->get('communication_detail_address',null);
            $creater_des = $request->get('creater_des',null);
            $other_creater_des = $request->get('other_creater_des',null);
            if(is_null($id)||is_null($filmfests_id)||is_null($creater_name)||is_null($director_name)||
                is_null($photography_name)||is_null($scriptwriter_name)||is_null($cutting_name)||
                is_null($hero_name)||is_null($heroine_name)||is_null($contact_phone)||is_null($contact_email)||is_null($school)||
                is_null($major)||is_null($adviser_name)||is_null($adviser_phone)||
                is_null($enter_school_time)||is_null($communication_address)||is_null($communication_detail_address)||is_null($creater_des)||is_null($other_creater_des)){
                return response()->json(['message'=>'数据不合法'],200);
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
                $schoolData = $newSchoolData->id;
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
            $application -> communication_address = $communication_address;
            $application -> communication_detail_address = $communication_detail_address;
            $application -> creater_des = $creater_name;
            $application -> other_creater_des = $other_creater_des;
            $application -> save();
            $contact_email = explode('_',$contact_email);
            foreach ($contact_email as $k => $v)
            {
                $contact_way = new ApplicationContactWay;
                $contact_way -> application_id = $id;
                $contact_way -> contact_way = $contact_email;
                $contact_way -> type = 1;
                $contact_way -> time_add = time();
                $contact_way -> time_update = time();
                $contact_way -> save();
            }

            $contact_phone = explode('_',$contact_phone);
            foreach ($contact_phone as $k => $v)
            {
                $contact_phone_way = new ApplicationContactWay;
                $contact_phone_way -> application_id = $id;
                $contact_phone_way -> contact_way = $contact_phone;
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
            $application_id = $request ->get('id',null);
            $filmfests_id = $request->get('filmfests_id',null);
            $user = $user = \Auth::guard('api')->user()->id;
            $movie_clips = $request->get('movie_clips',null);
            $is_original_video = $request->get('is_original_video',0);
            $address = $request->get('address',null);
            $production_id = $request->get('production_id',null);
            $university_id = $request->get('university_id',null);
            $poster = $request->get('$poster',null);
            $keys1 = [];
            $keys2 = [];
            array_push($keys2,$movie_clips);
            array_push($keys1,$poster);
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
            $srcbucket2 = 'hivideo-video-ects';
            $destbucket1 = 'hivideo-img';
            $destbucket2 = 'hivideo-video';
            $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket2,$destbucket2);
            if($message1[0]['code']==200 && $message2[0]['code']==200){
                if($is_original_video == 1){
                    DB::beginTransaction();
                    $filmfestProductionData = new FilmfestsProductions;
                    $filmfestProductionData -> filmfests_id = $filmfests_id;
                    $filmfestProductionData -> tweet_productions_id = $production_id;
                    $filmfestProductionData -> time_add = time();
                    $filmfestProductionData -> time_update = time();
                    $filmfestProductionData -> save();

                    $applicationProduction = new TweetProductionApplication;
                    $applicationProduction -> application_id = $application_id;
                    $applicationProduction -> tweet_production_id = $production_id;
                    $applicationProduction -> time_add = time();
                    $applicationProduction -> time_update = time();
                    $applicationProduction -> save();

                    $production = TweetProduction::where('id','=',$production_id)->first();
                    $production -> join_university_id = $university_id;
                    $production -> poster = $poster;
                    $production -> movie_clips = $movie_clips;
                    $production -> time_add = time();
                    $production -> time_update = time();
                    $production -> save();
                    DB::commit();
                    return response()->json(['message'=>'success'],200);
                }else{
                    $id = $production_id;
                    $is_priviate = $request->get('is_priviate',0);
                    $is_download = $request->get('is_download',1);
                    $is_reply = $request->get('is_reply',1);
                    $visible = $request->get('visible',0);
                    $size = $request->get('size',0);
                    if(is_null($id)||is_null($address)||is_null($size)){
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
                    $newProduction = Tweet::find($id);
                    $newProduction -> name = $name;
                    $newProduction -> active = 7;
                    $newProduction -> is_priviate = $is_priviate;
                    if($is_priviate==1){
                        $is_download = 0;
                        $is_reply = 0;
                    }
                    $newProduction -> is_download = $is_download;
                    $newProduction -> is_reply = $is_reply;
                    $newProduction -> size = $size;
                    $newProduction -> video = $address;
                    $newProduction -> created_at = time();
                    $newProduction -> updated_at = time();
                    $newProduction -> duration = $duration;
                    $newProduction -> visible = $visible;
                    $newProduction -> save();
                    DB::commit();
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
                                DB::beginTransaction();
                                $bb = 'img.cdn.hivideo.com/'.$address.'vframe-001_'.$cover.'_.jpg';
                                $production = Tweet::find($id);
                                $production -> screen_shot = $bb;
                                $production -> active = 0;
                                $production -> video =$newAddress;
                                $production -> save();

                            }else{
                                $production -> active =8;
                                $production -> updated_at = time();
                                $production -> save();
                                $production -> error_reason = '保存图片失败';
                                DB::commit();
                                return response()->json(['message'=>'保存图片失败'],200);
                            }
                            DB::commit();
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
                                $tweet_id = $production->id;
                                ChannelTweet::where('tweet_id',$tweet_id)->where('channel_id','=',11)->delete();
                                $channelTweet = new ChannelTweet;
                                $channelTweet -> channel_id = $channel;
                                $channelTweet -> tweet_id = $tweet_id;
                                $channelTweet -> save();

                                $application_form = Application::where('id','=',$application_id)->first();
                                $application_form -> time_update = time();
                                $application_form -> is_over = 1;
                                $application_form -> save();

                                $childData = new TweetProduction;
                                $childData -> tweet_id = $id;
                                $childData -> is_current = 1;
                                $childData -> status = 3;
                                $childData -> time_add = time();
                                $childData -> time_update = time();
                                $childData -> poster = $poster;
                                $childData -> movie_clips = $movie_clips;
                                $childData -> university_id = $university_id;
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
                                    $newProductionFilmType -> production_id = $production_id;
                                    $newProductionFilmType -> time_add = time();
                                    $newProductionFilmType -> time_update = time();
                                    $newProductionFilmType -> save();
                                }
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
                                $tweet_id = $production->id;
                                ChannelTweet::where('tweet_id',$tweet_id)->where('channel_id','=',11)->delete();
                                $channelTweet = new ChannelTweet;
                                $channelTweet -> channel_id = $channel;
                                $channelTweet -> tweet_id = $tweet_id;
                                $channelTweet -> save();

                                $application_form = Application::where('id','=',$application_id)->first();
                                $application_form -> time_update = time();
                                $application_form -> is_over = 1;
                                $application_form -> save();

                                $childData = new TweetProduction;
                                $childData -> tweet_id = $id;
                                $childData -> is_current = 1;
                                $childData -> status = 3;
                                $childData -> time_add = time();
                                $childData -> time_update = time();
                                $childData -> poster = $poster;
                                $childData -> movie_clips = $movie_clips;
                                $childData -> university_id = $university_id;
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
                                    $newProductionFilmType -> production_id = $production_id;
                                    $newProductionFilmType -> time_add = time();
                                    $newProductionFilmType -> time_update = time();
                                    $newProductionFilmType -> save();
                                }


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

}
