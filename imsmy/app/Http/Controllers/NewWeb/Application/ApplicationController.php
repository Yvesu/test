<?php

namespace App\Http\Controllers\NewWeb\Application;

use App\Http\Middleware\Filmfest;
use App\Models\Activity;
use App\Models\ActivityUser;
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
use App\Models\JoinVideo;
use App\Models\JoinVideoTweet;
use App\Models\ProductionFilmType;
use App\Models\Tweet;
use App\Models\TweetActivity;
use App\Models\TweetContent;
use App\Models\TweetProduction;
use App\Models\TweetQiniuCheck;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use CloudStorage;
use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

class ApplicationController extends Controller
{
    //
    private $paginate = 8;

    const UseVideoBacket = 'test-video';

    const UseImgBacket = 'test-img';

    const UseVideoCdn = 'test.v.cdn.hivideo.com/';

    const UseImgCdn = 'test.img.cdn..com/';

    public function into(Request $request)
    {
        try{
            $filmfest_id = $request->get('filmfests_id',null);
//            $filmfest_name = $request->get('filmfest_name');
            $filmfest = Filmfests::find($filmfest_id);
            $logo = 'http://'.$filmfest->logo;
            $filmfest_name = '第'.$filmfest->period.'届'.$filmfest->name;
            $user = \Auth::guard('api')->user()->id;
            $ok = Filmfests::where('id','=',$filmfest_id)->whereHas('user',function ($q) use($user){
                $q->where('user.id',$user);
            })->first();
            $open = Filmfests::find($filmfest_id)->is_open;
            if((int)$open===0){
                return response()->json(['name'=>$filmfest_name,'message'=>'竞赛未开启,不能报名','logo'=>$logo],200);
            }
            if($ok){
                return response()->json(['name'=>$filmfest_name,'message'=>'您是管理者，不可以参与','logo'=>$logo]);
            }else{
                $is_over = Application::where('user_id',$user)->where('is_over','=',1)->where('filmfests_id',$filmfest_id)->first();
                if($is_over){
                    return response()->json(['message'=>'您已经报过名了','name'=>$filmfest_name,'logo'=>$logo],200);
                }else{
                    return response()->json(['data'=>['name'=>$filmfest_name,'id'=>$filmfest_id,'logo'=>$logo]],200);
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
                $nameRule = $filmfest->name_rule_data;
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
                        'nameRuleData'=>$nameRule,
                    ];
                }else{
                    DB::beginTransaction();
                    $num = Application::where('filmfests_id','=',$filmfest_id)->get();
                    if($num->count()<=0){
                        $number = (int)((Filmfests::find($filmfest_id)->number_title).'0001');
                    }else{
                        $number = (Application::where('filmfests_id','=',$filmfest_id)->get()->max('number'))+1;
                    }
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
                        'number'=> $number,
                        'nameRuleData'=>$nameRule,
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
                    'nameRuleData'=>$nameRule,
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
//                    CloudStorage::deleteNew('hivideo-img',$oldFile_img);
                    CloudStorage::deleteNew(config('constants.image_bucket'),$oldFile_img);
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
                    $destbucket1 = config('constants.image_bucket');
//                    $destbucket1 = 'hivideo-img';
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
                    return response()->json(['message'=>''],200);
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
                FilmTypeApplication::where('application_id',$id)->delete();
                foreach ($units as $k => $v)
                {
                    $unit_id=explode(':',$v)[0];
                    $unit_status=explode(':',$v)[1];
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
                return response()->json(['message'=>''],200);

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
            if(is_null($id)||is_null($filmfests_id)||is_null($creater_name)||is_null($director_name)||is_null($scriptwriter_name)||
                is_null($contact_phone)||is_null($contact_email)||is_null($school)||
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
            $filmfest = Filmfests::find($filmfests_id);
            $data = [
                'id'=>$id,
                'user_id'=> $user,
                'filmfests_id'=>$filmfests_id,
                'university_id'=>$application->university_id,
                'nameRuleClips'=>$filmfest->name_rule_clips,
                'nameRulePoster'=>$filmfest->name_rule_poster,
                'nameRuleProduction'=>$filmfest->name_rule_production,
                'application_id'=>$application->number,
            ];
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_fouond'],404);
        }
    }

    public function pageSubmit1(Request $request)
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
                    //  云空间视频暂时无封面，先搁置
                    $movie_clip = CloudStorageFile::find($clips_id);
                    $movie_clips_video = $movie_clip->address;
                    $movie_clips_screen_shot = $movie_clip->screenshot;
                    $movie_clips_duration = $movie_clip->duration;
                    $message2[0]['code']=200;
                    $movie_clips_transcoding = $movie_clip->transcoding_video;
                    $movie_clips_video_m3u8 = $movie_clip->video_m3u8;
                }else{
                    $movie_clip = Tweet::find($clips_id)->video;
                    $movie_clips_video = $movie_clip->video;
                    $movie_clips_screen_shot = $movie_clip->screen_shot;
                    $movie_clips_duration = $movie_clip->duration;
                    $movie_clips_video_m3u8 = $movie_clip->video_m3u8;
                    $movie_clips_transcoding = $movie_clip->transcoding_video;
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
//                $destbucket2 = 'hivideo-video';
                $destbucket2 = 'test-video';
                $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket2,$destbucket2);
                $url = "http://video.ects.cdn.hivideo.com/".$movie_clips.'?avinfo';
                $ex = pathinfo($movie_clips, PATHINFO_EXTENSION);
                $fenBianLv = CloudStorage::getWidthAndHeight($movie_clips);
                $html = file_get_contents($url);
                $rule1 = "/\"width\":.*?,/";
                $rule2 = "/\"height\":.*?,/";
                $rule3 = "/\"duration\":.*?,/";
                preg_match($rule1,$html,$width);
                preg_match($rule2,$html,$height);
                preg_match($rule3,$html,$duration);
                $movie_clips_width =rtrim( explode(' ',$width[0])[1],',');
                $movie_clips_height = rtrim(explode(' ',$height[0])[1],',');
                $movie_clips_duration = (int)trim(rtrim(explode(' ',$duration[0])[1],','),'"');
                $newName = str_replace('.'.$ex,'_'.$ex.'.m3u8',$movie_clips);
                $message = CloudStorage::transcoding($destbucket2,$movie_clips,$movie_clips_width,$movie_clips_height,$choice=0);
                if($message){
//                    $movie_clips_video = 'v.cdn.hivideo.com/'.$movie_clips;
//                    $movie_clips_transcoding = 'v.cdn.hivideo.com/'.$newName;
//                    $movie_clips_video_m3u8 = 'v.cdn.hivideo.com/'.str_replace($ex,'m3u8',$movie_clips);
                    $movie_clips_video = config('constants.video_bucket_url').'/'.$movie_clips;
                    $movie_clips_transcoding = config('constants.video_bucket_url').'/'.$newName;
                    $movie_clips_video_m3u8 = config('constants.video_bucket_url').'/'.str_replace($ex,'m3u8',$movie_clips);
                }else{
                    return response()->json(['message'=>'片花保存失败',200]);
                }
                $cover = CloudStorage::saveCover($movie_clips,$movie_clips,$movie_clips_width,$movie_clips_height);
                if($cover){
                    $movie_clips_screen_shot = $movie_clips.'vframe-001_'.$movie_clips_width.'*'.$movie_clips_height.'_.jpg';
                }else{
                    return response()->json(['message'=>'保存图片失败'],200);
                }


            }
            $keys1 = [];
            array_push($keys1,$poster);
            $keyPairs1 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            $srcbucket1 = 'hivideo-img-ects';
//            $destbucket1 = 'hivideo-img';
            $destbucket1 = 'test-img';
            $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            if($message1[0]['code']==200 && $message2[0]['code']==200){
                if($is_original_video == 1){
                    if((int)$is_cloud===0){
                        DB::beginTransaction();
                        $production = TweetProduction::where('tweet_id','=',$production_id)->first();
                        $oldTweet = Tweet::find($production_id);
                        $oldVideoDuration =$oldTweet->duration;
                        $oldVideo = $oldTweet->video;
                        $widthAndHeight = $oldTweet->screen_shot;
                        $width = explode('*',$widthAndHeight)[0];
                        $height = explode('*',$widthAndHeight)[1];
                        if(!$production){
                            return response()->json(['message'=>'数据不存在'],200);
                        }
                        $production -> join_university_id = $university_id;
                        $production -> poster = $poster;
                        $production -> movie_clips = $movie_clips_video;
                        $production -> movie_clips_screen_shot = $movie_clips_screen_shot;
                        $production -> movie_clips_duration = $movie_clips_duration;
                        $production -> movie_clios_video_m3u8 = $movie_clips_video_m3u8;
                        $production -> movie_clios_transcoding = $movie_clips_transcoding;
                        $production -> time_add = time();
                        $production -> time_update = time();
                        $production -> save();
                        $tweet_production_id = $production->id;

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
                        $application_form -> production_id = $tweet_production_id;
                        $application_form -> is_over = 1;
                        $application_form -> number = $number;
                        $application_form -> save();
                        $units = $application_form->filmType()->get();
                        $pass = [];
                        if($units->count()>0){
                            foreach ($units as $item => $value)
                            {
                                $filmfestFilmType = FilmfestFilmfestType::where('filmfest_id',$filmfests_id)
                                    ->where('type_id',$value->id)->first();
                                if($filmfestFilmType->is_auto_pass === 1){
                                    if($oldVideoDuration<($filmfestFilmType->lt_time) || $oldVideoDuration>($filmfestFilmType->gt_time)){
                                        $is_pass = true;
                                        array_push($pass,$is_pass);
                                    }else{
                                        $is_pass = false;
                                        array_push($pass,$is_pass);
                                    }
                                }

                            }
                        }
                        if(is_null($pass)){
                            $is_pass = true;
                        }else{
                            if(in_array(false,$pass)){
                                $is_pass = false;
                            }else{
                                $is_pass = true;
                            }
                        }
                        $filmfestProductionData = new FilmfestsProductions;
                        $filmfestProductionData -> filmfests_id = $filmfests_id;
                        $filmfestProductionData -> tweet_productions_id = $tweet_production_id;
                        $filmfestProductionData -> time_add = time();
                        $filmfestProductionData -> time_update = time();
                        if(!$is_pass){
                            $filmfestProductionData -> status = 4;
                        }else{
                            $filmfestProductionData -> status = 0;

                        }
                        $filmfestProductionData -> save();

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

                        $active_id = Filmfests::find($filmfests_id)->active_id;
                        $active = Activity::find($active_id);
                        $active->work_count = $active->work_count + 1;
                        $active->users_count = $active->users_count + 1;
                        $active->save();

                        $newActivityUser = new ActivityUser;
                        $newActivityUser -> activity_id = $active_id;
                        $newActivityUser -> user_id = $user;
                        $newActivityUser -> time_add = time();
                        $newActivityUser -> time_update = time();
                        $newActivityUser -> save();

                        $newActivityTweet = new TweetActivity;
                        $newActivityTweet -> activity_id = Filmfests::find($filmfests_id)->active_id;
                        $newActivityTweet -> tweet_id = $production_id;
                        $newActivityTweet -> user_id = $user;
                        $newActivityTweet -> time_add = time();
                        $newActivityTweet -> time_update = time();
                        $newActivityTweet -> save();

                        DB::commit();
//                        $this->check($production_id);
                        return response()->json(['transcoding_id'=>null,'message'=>'success','tweet_id'=>$production_id,'height'=>$height,'width'=>$width,'filmfest_id'=>$filmfests_id],200);
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
                                    $message10 = CloudStorage::transcoding($bucket,$key,$width,$height,$choice=1);
                                    if($message10){
                                        $finallyAddress = str_replace($ex,'m3u8',$newAddress);
                                        $transcoding_video = str_replace('.'.$ex,'_'.$ex.'.m3u8',$key);
//                        $production -> transcoding_video = 'v.cdn.hivideo.com/'.$newAddress.'.m3u8';
                                        $production -> transcoding_video = 'v.cdn.hivideo.com/'.$transcoding_video;
                                        $production -> transcoding_id = $message10;
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

                                        $childData = new TweetProduction;
                                        $childData -> tweet_id = $id;
                                        $childData -> is_current = 1;
                                        $childData -> time_add = time();
                                        $childData -> time_update = time();
                                        $childData -> poster = $poster;
                                        $childData -> movie_clips = $movie_clips_video;
                                        $childData -> movie_clips_screen_shot = $movie_clips_screen_shot;
                                        $childData -> movie_clips_duration = $movie_clips_duration;
                                        $childData -> movie_clios_video_m3u8 = $movie_clips_video_m3u8;
                                        $childData -> movie_clios_transcoding = $movie_clips_transcoding;
                                        $childData -> join_university_id = $university_id;
                                        $childData -> save();

                                        $application_form = Application::where('id','=',$application_id)->first();
                                        $application_form -> time_update = time();
                                        $application_form -> production_id = $childData->id;
                                        $application_form -> is_over = 1;
                                        $application_form -> number = $number;
                                        $application_form -> save();

                                        $units = $application_form->filmType()->get();
                                        $pass = [];
                                        if($units->count()>0){
                                            foreach ($units as $item => $value)
                                            {
                                                $filmfestFilmType = FilmfestFilmfestType::where('filmfest_id',$filmfests_id)
                                                    ->where('type_id',$value->id)->first();
                                                if($filmfestFilmType->is_auto_pass === 1){
                                                    if($duration<($filmfestFilmType->lt_time) || $duration>($filmfestFilmType->gt_time)){
                                                        $is_pass = true;
                                                        array_push($pass,$is_pass);
                                                    }else{
                                                        $is_pass = false;
                                                        array_push($pass,$is_pass);
                                                    }
                                                }

                                            }
                                        }
                                        if(is_null($pass)){
                                            $is_pass = true;
                                        }else{
                                            if(in_array(false,$pass)){
                                                $is_pass = false;
                                            }else{
                                                $is_pass = true;
                                            }
                                        }

                                        $filmfestProductionData = new FilmfestsProductions;
                                        $filmfestProductionData -> filmfests_id = $filmfests_id;
                                        $filmfestProductionData -> tweet_productions_id = $childData->id;
                                        $filmfestProductionData -> time_add = time();
                                        $filmfestProductionData -> time_update = time();
                                        if(!$is_pass){
                                            $filmfestProductionData -> status = 4;
                                        }else{
                                            $filmfestProductionData -> status = 0;

                                        }
                                        $filmfestProductionData -> save();


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

                                        $active_id = Filmfests::find($filmfests_id)->active_id;
                                        $active = Activity::find($active_id);
                                        $active->work_count = $active->work_count + 1;
                                        $active->users_count = $active->users_count + 1;
                                        $active->save();

                                        $newActivityUser = new ActivityUser;
                                        $newActivityUser -> activity_id = $active_id;
                                        $newActivityUser -> user_id = $user;
                                        $newActivityUser -> time_add = time();
                                        $newActivityUser -> time_update = time();
                                        $newActivityUser -> save();

                                        $newActivityTweet = new TweetActivity;
                                        $newActivityTweet -> activity_id = Filmfests::find($filmfests_id)->active_id;
                                        $newActivityTweet -> tweet_id = $id;
                                        $newActivityTweet -> user_id = $user;
                                        $newActivityTweet -> time_add = time();
                                        $newActivityTweet -> time_update = time();
                                        $newActivityTweet -> save();

                                        DB::commit();
//                                        $this->check($id);
                                        return response()->json(['transcoding_id'=>$message10,'message'=>'success','tweet_id'=>$id,'height'=>$height,'width'=>$width,'filmfest_id'=>$filmfests_id],200);

                                    }else{
                                        $production -> active =8;
                                        $production -> updated_at = time();
                                        $production -> save();
                                        $production -> error_reason = '转码失败';
                                        DB::commit();
                                        return response()->json(['message'=>'转码失败'],200);
                                    }
                                }else{
                                    $message10 = CloudStorage::transcoding($bucket,$key,$width,$height,$choice=0);
                                    if($message10){
                                        $finallyAddress = str_replace($ex,'m3u8',$newAddress);
                                        $production -> transcoding_id = $message10;
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

                                        $childData = new TweetProduction;
                                        $childData -> tweet_id = $id;
                                        $childData -> is_current = 1;
                                        $childData -> time_add = time();
                                        $childData -> time_update = time();
                                        $childData -> poster = $poster;
                                        $childData -> movie_clips = $movie_clips_video;
                                        $childData -> movie_clips_screen_shot = $movie_clips_screen_shot;
                                        $childData -> movie_clips_duration = $movie_clips_duration;
                                        $childData -> movie_clios_video_m3u8 = $movie_clips_video_m3u8;
                                        $childData -> movie_clios_transcoding = $movie_clips_transcoding;
                                        $childData -> join_university_id = $university_id;
                                        $childData -> save();

                                        $application_form = Application::where('id','=',$application_id)->first();
                                        $application_form -> time_update = time();
                                        $application_form -> production_id = $childData->id;
                                        $application_form -> is_over = 1;
                                        $application_form -> number = $number;
                                        $application_form -> save();

                                        $units = $application_form->filmType()->get();
                                        $pass = [];
                                        if($units->count()>0){
                                            foreach ($units as $item => $value)
                                            {
                                                $filmfestFilmType = FilmfestFilmfestType::where('filmfest_id',$filmfests_id)
                                                    ->where('type_id',$value->id)->first();
                                                if($filmfestFilmType->is_auto_pass === 1){
                                                    if($duration<($filmfestFilmType->lt_time) || $duration>($filmfestFilmType->gt_time)){
                                                        $is_pass = true;
                                                        array_push($pass,$is_pass);
                                                    }else{
                                                        $is_pass = false;
                                                        array_push($pass,$is_pass);
                                                    }
                                                }

                                            }
                                        }
                                        if(is_null($pass)){
                                            $is_pass = true;
                                        }else{
                                            if(in_array(false,$pass)){
                                                $is_pass = false;
                                            }else{
                                                $is_pass = true;
                                            }
                                        }

                                        $filmfestProductionData = new FilmfestsProductions;
                                        $filmfestProductionData -> filmfests_id = $filmfests_id;
                                        $filmfestProductionData -> tweet_productions_id = $childData->id;
                                        $filmfestProductionData -> time_add = time();
                                        $filmfestProductionData -> time_update = time();
                                        if(!$is_pass){
                                            $filmfestProductionData -> status = 4;
                                        }else{
                                            $filmfestProductionData -> status = 0;

                                        }
                                        $filmfestProductionData -> save();

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

                                        $active_id = Filmfests::find($filmfests_id)->active_id;
                                        $active = Activity::find($active_id);
                                        $active->work_count = $active->work_count + 1;
                                        $active->users_count = $active->users_count + 1;
                                        $active->save();

                                        $newActivityUser = new ActivityUser;
                                        $newActivityUser -> activity_id = $active_id;
                                        $newActivityUser -> user_id = $user;
                                        $newActivityUser -> time_add = time();
                                        $newActivityUser -> time_update = time();
                                        $newActivityUser -> save();

                                        $newActivityTweet = new TweetActivity;
                                        $newActivityTweet -> activity_id = Filmfests::find($filmfests_id)->active_id;
                                        $newActivityTweet -> tweet_id = $id;
                                        $newActivityTweet -> user_id = $user;
                                        $newActivityTweet -> time_add = time();
                                        $newActivityTweet -> time_update = time();
                                        $newActivityTweet -> save();

                                        DB::commit();
//                                        $this->check($id);
                                        return response()->json(['transcoding_id'=>$message10,'message'=>'success','tweet_id'=>$id,'height'=>$height,'width'=>$width,'filmfest_id'=>$filmfests_id],200);
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
                            $message10 = CloudStorage::transcoding($bucket,$key,$width,$height,$choice=1);
                            if($message10){
                                $finallyAddress = str_replace($ex,'m3u8',$newAddress);
                                $transcoding_video = str_replace('.'.$ex,'_'.$ex.'.m3u8',$key);
//                        $production -> transcoding_video = 'v.cdn.hivideo.com/'.$newAddress.'.m3u8';
                                $production -> transcoding_video = 'v.cdn.hivideo.com/'.$transcoding_video;
                                $production -> transcoding_id = $message10;
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

                                $childData = new TweetProduction;
                                $childData -> tweet_id = $id;
                                $childData -> is_current = 1;
                                $childData -> time_add = time();
                                $childData -> time_update = time();
                                $childData -> poster = $poster;
                                $childData -> movie_clips = $movie_clips_video;
                                $childData -> movie_clips_screen_shot = $movie_clips_screen_shot;
                                $childData -> movie_clips_duration = $movie_clips_duration;
                                $childData -> movie_clios_video_m3u8 = $movie_clips_video_m3u8;
                                $childData -> movie_clios_transcoding = $movie_clips_transcoding;
                                $childData -> join_university_id = $university_id;
                                $childData -> save();

                                $application_form = Application::where('id','=',$application_id)->first();
                                $application_form -> time_update = time();
                                $application_form -> production_id = $childData->id;
                                $application_form -> is_over = 1;
                                $application_form -> number = $number;
                                $application_form -> save();

                                $units = $application_form->filmType()->get();
                                $pass = [];
                                if($units->count()>0){
                                    foreach ($units as $item => $value)
                                    {
                                        $filmfestFilmType = FilmfestFilmfestType::where('filmfest_id',$filmfests_id)
                                            ->where('type_id',$value->id)->first();
                                        if($filmfestFilmType->is_auto_pass === 1){
                                            if($duration<($filmfestFilmType->lt_time) || $duration>($filmfestFilmType->gt_time)){
                                                $is_pass = true;
                                                array_push($pass,$is_pass);
                                            }else{
                                                $is_pass = false;
                                                array_push($pass,$is_pass);
                                            }
                                        }

                                    }
                                }
                                if(is_null($pass)){
                                    $is_pass = true;
                                }else{
                                    if(in_array(false,$pass)){
                                        $is_pass = false;
                                    }else{
                                        $is_pass = true;
                                    }
                                }

                                $filmfestProductionData = new FilmfestsProductions;
                                $filmfestProductionData -> filmfests_id = $filmfests_id;
                                $filmfestProductionData -> tweet_productions_id = $childData->id;
                                $filmfestProductionData -> time_add = time();
                                $filmfestProductionData -> time_update = time();
                                if(!$is_pass){
                                    $filmfestProductionData -> status = 4;
                                }else{
                                    $filmfestProductionData -> status = 0;

                                }
                                $filmfestProductionData -> save();

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

                                $active_id = Filmfests::find($filmfests_id)->active_id;
                                $active = Activity::find($active_id);
                                $active->work_count = $active->work_count + 1;
                                $active->users_count = $active->users_count + 1;
                                $active->save();

                                $newActivityUser = new ActivityUser;
                                $newActivityUser -> activity_id = $active_id;
                                $newActivityUser -> user_id = $user;
                                $newActivityUser -> time_add = time();
                                $newActivityUser -> time_update = time();
                                $newActivityUser -> save();

                                $newActivityTweet = new TweetActivity;
                                $newActivityTweet -> activity_id = Filmfests::find($filmfests_id)->active_id;
                                $newActivityTweet -> tweet_id = $id;
                                $newActivityTweet -> user_id = $user;
                                $newActivityTweet -> time_add = time();
                                $newActivityTweet -> time_update = time();
                                $newActivityTweet -> save();

                                DB::commit();
//                                $this->check($id);
                                return response()->json(['transcoding_id'=>$message10,'message'=>'success','tweet_id'=>$id,'height'=>$height,'width'=>$width,'filmfest_id'=>$filmfests_id],200);

                            }else{
                                $production -> active =8;
                                $production -> updated_at = time();
                                $production -> save();
                                $production -> error_reason = '转码失败';
                                DB::commit();
                                return response()->json(['message'=>'转码失败'],200);
                            }
                        }else{
                            $message10 = CloudStorage::transcoding($bucket,$key,$width,$height,$choice=0);
                            if($message10){
                                $finallyAddress = str_replace($ex,'m3u8',$newAddress);
                                $production -> transcoding_id = $message10;
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

                                $childData = new TweetProduction;
                                $childData -> tweet_id = $id;
                                $childData -> is_current = 1;
                                $childData -> time_add = time();
                                $childData -> time_update = time();
                                $childData -> poster = $poster;
                                $childData -> movie_clips = $movie_clips_video;
                                $childData -> movie_clips_screen_shot = $movie_clips_screen_shot;
                                $childData -> movie_clips_duration = $movie_clips_duration;
                                $childData -> movie_clios_video_m3u8 = $movie_clips_video_m3u8;
                                $childData -> movie_clios_transcoding = $movie_clips_transcoding;
                                $childData -> join_university_id = $university_id;
                                $childData -> save();

                                $application_form = Application::where('id','=',$application_id)->first();
                                $application_form -> time_update = time();
                                $application_form -> production_id = $childData->id;
                                $application_form -> is_over = 1;
                                $application_form -> number = $number;
                                $application_form -> save();

                                $units = $application_form->filmType()->get();
                                $pass = [];
                                if($units->count()>0){
                                    foreach ($units as $item => $value)
                                    {
                                        $filmfestFilmType = FilmfestFilmfestType::where('filmfest_id',$filmfests_id)
                                            ->where('type_id',$value->id)->first();
                                        if($filmfestFilmType->is_auto_pass === 1){
                                            if($duration<($filmfestFilmType->lt_time) || $duration>($filmfestFilmType->gt_time)){
                                                $is_pass = true;
                                                array_push($pass,$is_pass);
                                            }else{
                                                $is_pass = false;
                                                array_push($pass,$is_pass);
                                            }
                                        }

                                    }
                                }
                                if(is_null($pass)){
                                    $is_pass = true;
                                }else{
                                    if(in_array(false,$pass)){
                                        $is_pass = false;
                                    }else{
                                        $is_pass = true;
                                    }
                                }

                                $filmfestProductionData = new FilmfestsProductions;
                                $filmfestProductionData -> filmfests_id = $filmfests_id;
                                $filmfestProductionData -> tweet_productions_id = $childData->id;
                                $filmfestProductionData -> time_add = time();
                                $filmfestProductionData -> time_update = time();
                                if(!$is_pass){
                                    $filmfestProductionData -> status = 4;
                                }else{
                                    $filmfestProductionData -> status = 0;

                                }
                                $filmfestProductionData -> save();



//                                $content = new TweetContent;
//                                $content -> tweet_id = $id;
//                                $content -> content = $application_form->production_des;
//                                $content -> created_at = time();
//                                $content -> updated_at = time();
//                                $content -> save();
                                DB::table('zx_tweet_content')->insert(
                                    [
                                        'tweet_id'=>$id,
                                        'content'=>$application_form->production_des,
                                        'created_at'=>time(),
                                        'updated_at'=>time()
                                    ]
                                );

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


                                $active_id = Filmfests::find($filmfests_id)->active_id;
                                $active = Activity::find($active_id);
                                $active->work_count = $active->work_count + 1;
                                $active->users_count = $active->users_count + 1;
                                $active->save();

                                $newActivityUser = new ActivityUser;
                                $newActivityUser -> activity_id = $active_id;
                                $newActivityUser -> user_id = $user;
                                $newActivityUser -> time_add = time();
                                $newActivityUser -> time_update = time();
                                $newActivityUser -> save();

                                $newActivityTweet = new TweetActivity;
                                $newActivityTweet -> activity_id = Filmfests::find($filmfests_id)->active_id;
                                $newActivityTweet -> tweet_id = $id;
                                $newActivityTweet -> user_id = $user;
                                $newActivityTweet -> time_add = time();
                                $newActivityTweet -> time_update = time();
                                $newActivityTweet -> save();

                                DB::commit();
//                                $this->check($id);
                                return response()->json(['transcoding_id'=>$message10,'message'=>'success','tweet_id'=>$id,'height'=>$height,'width'=>$width,'filmfest_id'=>$filmfests_id],200);
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
                return response()->json(['message'=>'异常21'],200);
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
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
                    //  云空间视频暂时无封面，先搁置
                    $movie_clip = CloudStorageFile::find($clips_id);
                    $movie_clips_video = $movie_clip->address;
                    $movie_clips_screen_shot = $movie_clip->screenshot;
                    $movie_clips_duration = $movie_clip->duration;
                    $message2[0]['code']=200;
                    $movie_clips_transcoding = $movie_clip->transcoding_video;
                    $movie_clips_video_m3u8 = $movie_clip->video_m3u8;
                }else{
                    $movie_clip = Tweet::find($clips_id)->video;
                    $movie_clips_video = $movie_clip->video;
                    $movie_clips_screen_shot = $movie_clip->screen_shot;
                    $movie_clips_duration = $movie_clip->duration;
                    $movie_clips_video_m3u8 = $movie_clip->video_m3u8;
                    $movie_clips_transcoding = $movie_clip->transcoding_video;
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
//                $destbucket2 = 'hivideo-video';
                $destbucket2 = config('constants.video_bucket');
                $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket2,$destbucket2);
                $url = "http://video.ects.cdn.hivideo.com/".$movie_clips.'?avinfo';
                $ex = pathinfo($movie_clips, PATHINFO_EXTENSION);
                $fenBianLv = CloudStorage::getWidthAndHeight($movie_clips);
                $html = file_get_contents($url);
                $rule1 = "/\"width\":.*?,/";
                $rule2 = "/\"height\":.*?,/";
                $rule3 = "/\"duration\":.*?,/";
                preg_match($rule1,$html,$width);
                preg_match($rule2,$html,$height);
                preg_match($rule3,$html,$duration);
                $movie_clips_width =rtrim( explode(' ',$width[0])[1],',');
                $movie_clips_height = rtrim(explode(' ',$height[0])[1],',');
                $movie_clips_duration = (int)trim(rtrim(explode(' ',$duration[0])[1],','),'"');
                $newName = str_replace('.'.$ex,'_'.$ex.'.m3u8',$movie_clips);
                $message = CloudStorage::transcoding($destbucket2,$movie_clips,$movie_clips_width,$movie_clips_height,$choice=0);
                if($message){
//                    $movie_clips_video = 'v.cdn.hivideo.com/'.$movie_clips;
//                    $movie_clips_transcoding = 'v.cdn.hivideo.com/'.$newName;
//                    $movie_clips_video_m3u8 = 'v.cdn.hivideo.com/'.str_replace($ex,'m3u8',$movie_clips);
                    $movie_clips_video = config('constants.video_bucket_url').'/'.$movie_clips;
                    $movie_clips_transcoding = config('constants.video_bucket_url').'/'.$newName;
                    $movie_clips_video_m3u8 = config('constants.video_bucket_url').'/'.str_replace($ex,'m3u8',$movie_clips);
                }else{
                    return response()->json(['message'=>'片花保存失败',200]);
                }
                $cover = CloudStorage::saveCover($movie_clips,$movie_clips,$movie_clips_width,$movie_clips_height);
                if($cover){
                    $movie_clips_screen_shot = $movie_clips.'vframe-001_'.$movie_clips_width.'*'.$movie_clips_height.'_.jpg';
                }else{
                    return response()->json(['message'=>'保存图片失败'],200);
                }


            }
            $keys1 = [];
            if($poster){
                array_push($keys1,$poster);
                $keyPairs1 = array();
                foreach($keys1 as $key)
                {
                    $keyPairs1[$key] = $key;
                }
                $srcbucket1 = 'hivideo-img-ects';
//                $destbucket1 = 'hivideo-img';
                $destbucket1 = config('constants.image_bucket');
                $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            }else{
                $message1[0]['code']=200;
            }

//            $message1[0]['code']=200;
//            $message2[0]['code']=200;
            if($message1[0]['code']==200 && $message2[0]['code']==200){
                if($is_original_video == 1){
                    return response()->json(['message'=>'暂无服务']);
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
                    $tweet -> active = 0;
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
                    //  扩展名
                    $ex = pathinfo($address, PATHINFO_EXTENSION);
                    //  分辨率
                    $fenBianLv = $width.'*'.$height;
                    $newAddress = str_replace('.'.$ex, '_'.$fenBianLv.'.'.$ex, $address);
                    $production -> active = 0;
                    $production -> video =$newAddress;
//                    if($width>=1280){
//                        $finallyAddress = str_replace($ex,'m3u8',$newAddress);
//                        $transcoding_video = str_replace('.'.$ex,'_'.$ex.'.m3u8',$key);
//                        $production -> transcoding_video = 'v.cdn.hivideo.com/'.$newAddress.'.m3u8';
//                        $production -> transcoding_video = 'v.cdn.hivideo.com/'.$transcoding_video;
//                        $production -> video ='v.cdn.hivideo.com/'.$newAddress;
                        $production -> video =config('constants.video_bucket_url').'/'.$newAddress;
//                        $production -> high_video = 'v.cdn.hivideo.com/high/'.$finallyAddress;
//                        $production -> norm_video = 'v.cdn.hivideo.com/norm/'.$finallyAddress;
//                        $production -> video_m3u8 ='v.cdn.hivideo.com/'.$finallyAddress;
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
                        $childData = new TweetProduction;
                        $childData -> tweet_id = $id;
                        $childData -> is_current = 1;
                        $childData -> time_add = time();
                        $childData -> time_update = time();
                        $childData -> poster = $poster;
                        $childData -> movie_clips = $movie_clips_video;
                        $childData -> movie_clips_screen_shot = $movie_clips_screen_shot;
                        $childData -> movie_clips_duration = $movie_clips_duration;
                        $childData -> movie_clios_video_m3u8 = $movie_clips_video_m3u8;
                        $childData -> movie_clios_transcoding = $movie_clips_transcoding;
                        $childData -> join_university_id = $university_id;
                        $childData -> save();

                        $application_form = Application::where('id','=',$application_id)->first();
                        $application_form -> time_update = time();
                        $application_form -> production_id = $childData->id;
                        $application_form -> is_over = 1;
//                        $application_form -> number = $number;
                        $application_form -> save();
                        $units = $application_form->filmType()->get();
                        $pass = [];
                        if($units->count()>0){
                            foreach ($units as $item => $value)
                            {
                                $filmfestFilmType = FilmfestFilmfestType::where('filmfest_id',$filmfests_id)
                                    ->where('type_id',$value->id)->first();
                                if($filmfestFilmType->is_auto_pass === 1){
                                    if($duration<($filmfestFilmType->lt_time) || $duration>($filmfestFilmType->gt_time)){
                                        $is_pass = true;
                                        array_push($pass,$is_pass);
                                    }else{
                                        $is_pass = false;
                                        array_push($pass,$is_pass);
                                    }
                                }

                            }
                        }
                        if(is_null($pass)){
                            $is_pass = true;
                        }else{
                            if(in_array(false,$pass)){
                                $is_pass = false;
                            }else{
                                $is_pass = true;
                            }
                        }

                        $filmfestProductionData = new FilmfestsProductions;
                        $filmfestProductionData -> filmfests_id = $filmfests_id;
                        $filmfestProductionData -> tweet_productions_id = $childData->id;
                        $filmfestProductionData -> time_add = time();
                        $filmfestProductionData -> time_update = time();
                        if(!$is_pass){
                            $filmfestProductionData -> status = 4;
                        }else{
                            $filmfestProductionData -> status = 0;

                        }
                        $filmfestProductionData -> save();

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
                            $newFilmfestUniversity -> join_count = 1;
                            $newFilmfestUniversity -> save();
                        }else{
                            $oldFilmfestUniversity -> join_count = $oldFilmfestUniversity -> join_count + 1;
                            $oldFilmfestUniversity -> save();
                        }

                        $content = new TweetContent;
                        $content -> tweet_id = $id;
                        $content -> content = $application_form->production_des;
                        $content -> created_at = time();
                        $content -> updated_at = time();
                        $content ->save();

                        $active_id = Filmfests::find($filmfests_id)->active_id;
                        $active = Activity::find($active_id);
                        $active->work_count = $active->work_count + 1;
                        $active->users_count = $active->users_count + 1;
                        $active->save();

                        $newActivityUser = new ActivityUser;
                        $newActivityUser -> activity_id = $active_id;
                        $newActivityUser -> user_id = $user;
                        $newActivityUser -> time_add = time();
                        $newActivityUser -> time_update = time();
                        $newActivityUser -> save();

                        $newActivityTweet = new TweetActivity;
                        $newActivityTweet -> activity_id = Filmfests::find($filmfests_id)->active_id;
                        $newActivityTweet -> tweet_id = $id;
                        $newActivityTweet -> user_id = $user;
                        $newActivityTweet -> time_add = time();
                        $newActivityTweet -> time_update = time();
                        $newActivityTweet -> save();

                        DB::commit();
//                                $this->check($id);
                        $shouldData = [
                            'address'=>$address,
                            'tweet_id'=>$id,
                            'height'=>$height,
                            'width'=>$width,
                            'filmfest_id'=>$filmfests_id
                        ];
                        return response()->json(['message'=>'success','data'=>$shouldData],200);

                }
            }else{
                return response()->json(['message'=>'片花或海报保存失败'],200);
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function qiepian()
    {
        $NotifyData = file_get_contents("php://input");
        $res = json_decode($NotifyData)->code;
        if ($res === 0 ) {
            $key = json_decode($NotifyData)->items[0]->key;
            switch ($key) {
                case strstr($key, 'norm'):
                    $tweet_id = getNeedBetween($key, '&', '&&');
//                    $new_url = 'v.cdn.hivideo.com/' . json_decode($NotifyData)->items[0]->key;
                    $new_url = config('constants.video_bucket_url').'/'.json_decode($NotifyData)->items[0]->key;
                    $new_res = Tweet::find($tweet_id)->update(['norm_video' => $new_url]);
                    $production_id = Tweet::find($tweet_id)->tweetProduction()->first()->id;
                    $filmfestProduction = FilmfestsProductions::where('filmfests_id', 1)
                        ->where('tweet_productions_id', $production_id)->first();
                    $filmfestProduction->videoStatus = 0;
                    $filmfestProduction->save();
                    break;
                case strstr($key, 'adapt'):
                    $tweet_id = getNeedBetween($key, '&', '&&');
//                    $new_url = 'v.cdn.hivideo.com/' . json_decode($NotifyData)->items[0]->key;
                    $new_url = config('constants.video_bucket_url').'/' . json_decode($NotifyData)->items[0]->key;
                    $new_res = Tweet::find($tweet_id)->update(['transcoding_video' => $new_url]);
                    $production_id = Tweet::find($tweet_id)->tweetProduction()->first()->id;
                    $filmfestProduction = FilmfestsProductions::where('filmfests_id', 1)
                        ->where('tweet_productions_id', $production_id)->first();
                    $filmfestProduction->videoStatus = 0;
                    $filmfestProduction->save();
                    break;
                case strstr($key, 'original'):
                    $tweet_id = getNeedBetween($key, '&', '&&');
//                    $new_url = 'v.cdn.hivideo.com/' . json_decode($NotifyData)->items[0]->key;
                    $new_url = config('constants.video_bucket_url').'/' . json_decode($NotifyData)->items[0]->key;
                    $new_res = Tweet::find($tweet_id)->update(['video_m3u8' => $new_url]);
                    $production_id = Tweet::find($tweet_id)->tweetProduction()->first()->id;
                    $filmfestProduction = FilmfestsProductions::where('filmfests_id', 1)
                        ->where('tweet_productions_id', $production_id)->first();
                    $filmfestProduction->videoStatus = 0;
                    $filmfestProduction->save();
                    break;
                case strstr($key, 'high'):
                    $tweet_id = getNeedBetween($key, '&', '&&');
//                    $new_url = 'v.cdn.hivideo.com/' . json_decode($NotifyData)->items[0]->key;
                    $new_url = config('constants.video_bucket_url').'/' . json_decode($NotifyData)->items[0]->key;
                    $new_res = Tweet::find($tweet_id)->update(['high_video' => $new_url]);
                    $production_id = Tweet::find($tweet_id)->tweetProduction()->first()->id;
                    $filmfestProduction = FilmfestsProductions::where('filmfests_id', 1)
                        ->where('tweet_productions_id', $production_id)->first();
                    $filmfestProduction->videoStatus = 0;
                    $filmfestProduction->save();
                    break;
                default :
                    die;

            }
        }elseif($res == 3){
            $way = json_decode($NotifyData)->items[0]->cmd;
            $a = stripos($way,'adapt/m3u8');
            if($a!=false){
                $key = json_decode($NotifyData)->inputKey;
                $tweet_id = Tweet::where('video','like','%'.$key.'%')->first()->id;
                $production_id = Tweet::find($tweet_id)->tweetProduction()->first()->id;
                $filmfestProduction = FilmfestsProductions::where('filmfests_id', 1)
                    ->where('tweet_productions_id', $production_id)->first();
                if($filmfestProduction->videoStatus == 0){
                    $filmfestProduction->videoStatus = 0;
                }else{
                    $filmfestProduction->videoStatus = 2;
                }
                $filmfestProduction->save();
            }

        }elseif($res == 4){
            $key = json_decode($NotifyData)->inputKey;
            $tweet_id = Tweet::where('video','like','%'.$key.'%')->first()->id;
            $production_id = Tweet::find($tweet_id)->tweetProduction()->first()->id;
            $filmfestProduction = FilmfestsProductions::where('filmfests_id', 1)
                ->where('tweet_productions_id', $production_id)->first();
            $filmfestProduction->videoStatus = 0;
            $filmfestProduction->save();
        }
    }
    
    public function getSubmit(Request $request)
    {
        try{
            $address = $request->get('address');
            $tweet_id = $request->get('id');
            $height = (int)$request->get('height');
            $width = (int)$request->get('width');
            $filmfest_id = $request->get('filmfest_id');
            if($width>=1280){
                $choice = 1;
            }else{
                $choice = 0;
            }
            $keys1 = [];
            array_push($keys1,$address);
            $keyPairs1 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            $srcbucket = 'hivideo-video-ects';
//            $destbucket = 'hivideo-video';
            $destbucket = config('constants.video_bucket');
//            $notice = 'http://www.hivideo.com/api/notice_transcoding';
            $notice = 'http://www.goobird.com/api/notice_transcoding';
            //  扩展名
            $ex = pathinfo($address, PATHINFO_EXTENSION);
            //  分辨率
            $fenBianLv = CloudStorage::getWidthAndHeight($address);
            //  移动到正式空间  还没改名字
            $message = CloudStorage::copyfile($keyPairs1,$srcbucket,$destbucket);
            $newAddress = str_replace('.'.$ex, '_'.$fenBianLv.'.'.$ex, $address);
            //  重命名
            $move = CloudStorage::reNameFile($destbucket,$address,$destbucket,$newAddress);
            //  截图
            $cover = CloudStorage::saveCover($address,$newAddress,$width,$height);
//            $bb = 'img.cdn.hivideo.com/'.$address.'vframe-001_'.$fenBianLv.'_.jpg';
            $bb = config('constants.img_bucket_url').'/'.$address.'vframe-001_'.$fenBianLv.'_.jpg';
            //  转码
            $message = CloudStorage::transcoding_tweet($tweet_id,$destbucket,$newAddress,$width,$height,$choice,$notice);
            // 更改状态
            $tweet = Tweet::find($tweet_id);
            $tweet -> screen_shot = $bb;
            $tweet -> save();
            $this->check($tweet_id);
            //  鉴黄
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found']);
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


    public function check($id)
//    public function check(Request $request)
    {
//        $id = $request->get('tweet_id');
        //        //获取动态信息
        $tweet = Tweet::find($id);
//        \DB::table('tweet_to_qiniu')->where('tweet_id', $id)->update(['active' => 2]);
        //如果被删除则标记为检测通过
        if ($tweet->active === 3 || $tweet->active === 5 ){
            YellowCheck::where('tweet_id',$id)->update(['active'=>2]);
            die();
        }
        //封面路径
        $url = CloudStorage::ImageCheck($tweet->screen_shot);

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Content-type:application/x-www-form-urlencoded\r\n" .
                    "Referer:http://www.goobird.com",
            ],
        ];

//        $context = stream_context_create($opts);
//        $image_qpulp = file_get_contents($url, false, $context);
//
//        $image_qpulp_res = json_decode($image_qpulp, true);

//        if ($image_qpulp_res['result']['label'] == 0) {
//            // 七牛检测未通过  涉及色情
//            Tweet::where('id', '=', $tweet->id)->update(['active' => 6]);
//
//            //创建记录
//            $tweet_qiniu_check = TweetQiniuCheck::create([
//                'user_id'   => $tweet->user_id,
//                'tweet_id' => $tweet->id,
//                'image_qpulp' => 2,
//                'create_time' => time(),
//            ]);
//
//            //创建私信
//            $tweet =  Tweet::find( $tweet->id);
//            $tweet_content = TweetContent::where('tweet_id',$tweet->id)->first()->content;
//            $time = time();
//            $tweet_content = $tweet_content ? "您最新发送的动态<{$tweet_content}>可能涉及违规,我们将尽快为您处理..." : "您于 ".date('Y-m-d H:i:s')." 发布的动态可能涉及违规,我们将尽快为您处理..." ;
//            PrivateLetter::create([
//                'from' => 1000437,
//                'to'    => $tweet->user_id,
//                'content'   => $tweet_content,
//                'user_type' => '1',
//                'created_at' => $time,
//                'updated_at' =>$time,
//            ]);
//
//        } else if ($image_qpulp_res['result']['label'] == 1) {
//            // 七牛检测未通过  涉及色情
//            Tweet::where('id', '=', $tweet->id)->update(['active' => 6]);
//            //创建记录
//            $tweet_qiniu_check = TweetQiniuCheck::create([
//                'user_id'   => $tweet->user_id,
//                'tweet_id' => $tweet->id,
//                'image_qpulp' => 1,
//                'create_time' => time(),
//            ]);
//
////            创建私信
//            $tweet =  Tweet::find( $tweet->id);
////
//            $tweet_content = TweetContent::where('tweet_id',$tweet->id)->first()->content;
//
//            $time = time();
//            $tweet_content = $tweet_content ? "您最新发送的动态<{$tweet_content}>可能涉及违规,我们将尽快为您处理..." : "您于 ".date('Y-m-d H:i:s')." 发布的动态可能涉及违规,我们将尽快为您处理..." ;
//            PrivateLetter::create([
//                'from' => 1000437,
//                'to'    => $tweet->user_id,
//                'content'   => $tweet_content,
//                'user_type' => '1',
//                'created_at' => $time,
//                'updated_at' =>$time,
//            ]);
//
//        } else {
//            $tweet_qiniu_check = TweetQiniuCheck::create([
//                'user_id'   => $tweet->user_id,
//                'tweet_id' => $tweet->id,
//                'image_qpulp' => 0,
//                'create_time' => time(),
//            ]);
//        }

        //政治人物检测
//        $url_z = CloudStorage::qpolitician($tweet->screen_shot);  //tupian
//
//        $opts_2 = [
//            'http' => [
//                'method' => 'GET',
//                'header' => "Content-type:application/x-www-form-urlencoded\r\n" .
//                    "Referer:http://www.goobird.com",
//            ],
//        ];
//        $context = stream_context_create($opts_2);
//        $qpolitician = file_get_contents($url_z, false, $context);
//
//        //取数据
//        $qpolitician_result = json_decode($qpolitician, true);
//
//        //写入检测记录
//        foreach ($qpolitician_result['result']['detections'] as $v) {
//            if (array_key_exists("sample", $v)) {
//                //写入记录
//                TweetQiniuCheck::where('id', '=', $tweet_qiniu_check->id)->update(['qpolitician' => 1]);
//
//                //修改状态
//                Tweet::where('id', '=', $tweet->id)->update(['active' => 6]);
//
//                //创建私信
//                $tweet =  Tweet::find( $tweet->id);
//
//                $tweet_content = TweetContent::where('tweet_id',$tweet->id)->first()->content;
//                $tweet_content = $tweet_content ? "您最新发送的动态<{$tweet_content}>可能涉及违规,我们将尽快为您处理..." : "您于 ".date('Y-m-d H:i:s')." 发布的动态可能涉及违规,我们将尽快为您处理..." ;
//                $time = time();
//
//                PrivateLetter::create([
//                    'from' => 1000240,
//                    'to'    => $tweet->user_id,
//                    'content'   => $tweet_content,
//                    'user_type' => '1',
//                    'created_at' => $time,
//                    'updated_at' =>$time,
//                ]);
//            }
//        }

//        $notice = "http://hivideo.com/api/yellowcheck";
        $notice = "http://goobird.com/api/yellowcheck";
        CloudStorage::yellowCheck($id,$tweet->video,$notice);
    }

}
