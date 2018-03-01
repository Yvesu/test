<?php

namespace App\Http\Controllers\NewWeb\User;

use App\Http\Middleware\Filmfest;
use App\Models\FilmfestFilmfestType;
use App\Models\Filmfests;
use App\Models\FilmfestUser\FilmfestUserFilmfestFilmtypeAwards;
use App\Models\FilmfestUser\FilmfestUserPermission;
use App\Models\FilmfestUser\FilmfestUserReviewChildLog;
use App\Models\FilmfestUser\FilmfestUserRole;
use App\Models\FilmfestUser\FilmfestUserRoleGroup;
use App\Models\FilmfestUser\FilmfestUserRolePermission;
use App\Models\JoinVideo;
use CloudStorage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class FilmfestIssueController extends Controller
{
    //
    public function setIndexTop(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $filmfest = Filmfests::find($filmfest_id);
            $name = '第'.$filmfest->period.'届'.$filmfest->name;
            $create_time = date('Y年m月d日 H:i',$filmfest->time_add);
            $logo = $filmfest->logo;
            $setStatus = $filmfest->set_status;
            switch ($setStatus){
                case 0:
                    $baseStatus = 0;
                    $ruleStatus = 0;
                    $filmStatus = 0;
                    $unitStatus = 0;
                    $detailStatus = 0;
                    $goStatus = 0;
                    $filmfestStatus = 0;
                    break;
                case 1:
                    $baseStatus = 1;
                    $ruleStatus = 0;
                    $filmStatus = 0;
                    $unitStatus = 0;
                    $detailStatus = 0;
                    $goStatus = 0;
                    $filmfestStatus = 0;
                    break;
                case 2:
                    $baseStatus = 1;
                    $ruleStatus = 1;
                    $filmStatus = 0;
                    $unitStatus = 0;
                    $detailStatus = 0;
                    $goStatus = 0;
                    $filmfestStatus = 0;
                    break;
                case 3:
                    $baseStatus = 1;
                    $ruleStatus = 1;
                    $filmStatus = 1;
                    $unitStatus = 0;
                    $detailStatus = 0;
                    $goStatus = 0;
                    $filmfestStatus = 0;
                    break;
                case 4:
                    $baseStatus = 1;
                    $ruleStatus = 1;
                    $filmStatus = 1;
                    $unitStatus = 1;
                    $detailStatus = 0;
                    $goStatus = 0;
                    $filmfestStatus = 0;
                    break;
                case 5:
                    $baseStatus = 1;
                    $detailStatus = 1;
                    $ruleStatus = 1;
                    $filmStatus = 1;
                    $unitStatus = 1;
                    $goStatus = 0;
                    $filmfestStatus = 1;
                    break;
                case 6:
                    $baseStatus = 1;
                    $detailStatus = 1;
                    $ruleStatus = 1;
                    $filmStatus = 1;
                    $unitStatus = 1;
                    $goStatus = 1;
                    $filmfestStatus = 1;
                    break;
                default:
                    return response()->json(['message'=>'异常'],200);
            }
            $status = [
                'detailStatus'=>$detailStatus,
                'ruleStatus'=>$ruleStatus,
                'filmStatus'=>$filmStatus,
                'unitStatus'=>$unitStatus,
                'goStatus'=>$goStatus,
                'baseStatus'=>$baseStatus,

            ];
            $data = [
                'name'=>$name,
                'filmfestStatus'=>$filmfestStatus,
                'create_time' => $create_time,
                'logo'=>$logo,
                'setStatus'=>$status,
            ];
            return response()->json(['data'=>$data],200);

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 基础设置页
     */
    public function baseSet(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $filmfest = Filmfests::find($filmfest_id);
            $data = [
                'cover'=>'http://'.$filmfest->cover,
                'detail'=>$filmfest->detail,
                'holdTime'=>date('Y.m.d',$filmfest->time_start).'-'.date('Y.m.d',$filmfest->time_end),
                'submitTime'=>date('Y.m.d',$filmfest->submit_start_time).'-'.date('Y.m.d',$filmfest->submit_end_time),
                'address_country'=>$filmfest->address_country,
                'address_province'=>$filmfest->address_province,
                'address_city'=>$filmfest->address_city,
                'address_county'=>$filmfest->address_county,
                'detail_address'=>$filmfest->detail_address,
            ];
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function saveBaseSet(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            DB::beginTransaction();
            $filmfest = Filmfests::find($filmfest_id);
            if($filmfest->is_open === 1){
                return response()->json(['message'=>'竞赛开始后不允许修改'],200);
            }
            $address = $request->get('cover',null);
            $detail = $request->get('detail',null);
            $address_country = $request->get('address_country','中国');
            $address_province = $request->get('address_province');
            $address_city = $request->get('address_city');
            $address_county = $request->get('address_county');
            $detail_address = $request->get('detail_address');
            if(is_null($address)){
                $filmfest = Filmfests::find($filmfest_id);
                if(!$filmfest->cover){
                    return response()->json(['message'=>'封面不能为空'],200);
                }
            }else{
                $url = "http://img.ects.cdn.hivideo.com/".$address.'?imageInfo';
                $html = file_get_contents($url);
                $res = json_decode($html, true);
                if($res['width']/$res['height'] != 2/1 || $res['width']<2000 || $res['height']<1000 || $res['size'] > 5*1024*1024 || !in_array($res['format'],['png','jpeg','jpg'])){
                    return response()->json(['message'=>'格式不正确'],200);
                }
                $keys = [];
                array_push($keys,$address);
                $keyPairs = array();
                foreach ($keys as $key)
                {
                    $keyPairs[$key] = $key;
                }
                $srcbucket = 'hivideo-img-ects';
                $destbucket = 'hivideo-img';
                $message2 = CloudStorage::copyfile($keyPairs,$srcbucket,$destbucket);
                if($message2[0]['code']==200) {

                    $filmfest->cover = "http://video.cdn.hivideo.com/" . $address;
                    if(is_null($detail)){
                        return response()->json(['message'=>'描述不能为空'],200);
                    }
                    $filmfest->detail = $detail;
                    $filmfest->detail_address = $detail_address;
                    $filmfest->address_country = $address_country;
                    $filmfest->address_province = $address_province;
                    $filmfest->address_city = $address_city;
                    $filmfest->address_county = $address_county;
                    $filmfest->address = $detail_address.$address_country.$address_province.$address_city.$address_county;
                    $filmfest->time_update = time();
                    $filmfest->set_status = 1;
                    $filmfest->save();

//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '上传了封面';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();

                }
                DB::commit();
            }
            return response()->json(['message'=>'success'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 详情设置显示页
     */
    public function detailSet(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $filmfest = Filmfests::find($filmfest_id);
            $data = [
                'cover'=>'http://'.$filmfest->cover,
                'des'=>$filmfest->des,
            ];
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],.404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 执行详情设置
     */
    public function doDetailSet(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $address = $request->get('cover',null);
            $des = $request->get('des',null);
            if(is_null($address) || is_null($des)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $url = "http://img.ects.cdn.hivideo.com/".$address.'?imageInfo';
            $html = file_get_contents($url);
            $res = json_decode($html, true);
            if($res['width']/$res['height'] != 2/1 || $res['width']<1920 || $res['height']<960 || $res['size'] > 5*1024*1024 || !in_array($res['format'],['png','jpeg','jpg'])){
                return response()->json(['message'=>'格式不正确'],200);
            }
            $keys = [];
            array_push($keys,$address);
            $keyPairs = array();
            foreach ($keys as $key)
            {
                $keyPairs[$key] = $key;
            }
            $srcbucket = 'hivideo-img-ects';
            $destbucket = 'hivideo-img';
            $message2 = CloudStorage::copyfile($keyPairs,$srcbucket,$destbucket);
            if($message2[0]['code']==200){
                DB::beginTransaction();
                $filmfest = Filmfests::find($filmfest_id);
                $filmfest -> cover = "http://video.cdn.hivideo.com/".$address;
                $filmfest -> des = $des;
                $filmfest -> time_update = time();
                $filmfest -> set_status = 1;
                $filmfest -> save();

//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '上传了封面';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();

                DB::commit();
                return response()->json(['message'=>'上传成功'],200);
            }else{
                return response()->json(['message'=>'上传失败'],200);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function setIndexRule(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $user = \Auth::guard('api')->user()->id;
            $nowTime = time();
            $filmfest = Filmfests::find($filmfest_id);
            $submitStartTime  = $filmfest->submit_start_time;       //  投片开始时间
            $submitStopTime = $filmfest->submit_end_time;           //  投片结束时间
            $firstSelectTime = $filmfest->check_time;               //  初审开始时间
            $againSelectTime = $filmfest->check_again_time;         //  复审开始时间
            $enterTime  = $filmfest->enter_time;                    //  入围评选时间
            $professionalTime = $filmfest->professional_time;       //  专业评选时间
            $endTime = $filmfest->time_end;                         //  公示时间
            $professionalStatus = 0;
            $enterStatus = 0;
            $againSelectStatus = 0;
            $firstSelectStatus = 0;
            $submitStatus = 0;
            $endStatus = 0;
            if($nowTime<=$submitStartTime){
                $submitStatus = 0;
            }elseif($nowTime<=$submitStopTime){
                $submitStatus = 1;
            }elseif($nowTime<$againSelectTime){
                $firstSelectStatus = 1;
                $submitStatus = 2;
            }elseif ($nowTime<$enterTime){
                $againSelectStatus = 1;
                $firstSelectStatus = 2;
                $submitStatus = 2;
            }elseif ($nowTime<$professionalTime){
                $enterStatus = 1;
                $againSelectStatus = 2;
                $firstSelectStatus = 2;
                $submitStatus = 2;
            }elseif ($nowTime<=$endTime){
                $professionalStatus = 1;
                $enterStatus = 2;
                $againSelectStatus = 2;
                $firstSelectStatus = 2;
                $submitStatus = 2;
            }else{
                $endStatus = 2;
                $professionalStatus = 2;
                $enterStatus = 2;
                $againSelectStatus = 2;
                $firstSelectStatus = 2;
                $submitStatus = 2;
            }
            $status = [
                [
                    'name'=>'征片',
                    'status'=>$submitStatus,
                    'time'=>date('Y/m/d',$submitStartTime).'-'.date('Y/m/d',$submitStopTime),
                ],
                [
                    'name'=>'初审',
                    'status'=>$firstSelectStatus,
                    'time'=>date('Y/m/d',$firstSelectTime).'-'.date('Y/m/d',$againSelectTime),
                ],
                [
                    'name'=>'复审',
                    'status'=>$againSelectStatus,
                    'time'=>date('Y/m/d',$againSelectTime).'-'.date('Y/m/d',$enterTime),
                ],
                [
                    'name'=>'入围',
                    'status'=>$enterStatus,
                    'time'=>date('Y/m/d',$enterTime).'-'.date('Y/m/d',$professionalTime),
                ],
                [
                    'name'=>'专业',
                    'status'=>$professionalStatus,
                    'time'=>date('Y/m/d',$professionalTime).'-'.date('Y/m/d',$endTime),
                ],
                [
                    'name'=>'获奖公示',
                    'status'=>$endStatus,
                    'time'=>date('Y/m/d',$endTime),
                ],
            ];

            $mainRoleGroup = FilmfestUserRoleGroup::where('filmfest_id',$filmfest_id)->get();
            $roleGroup = [];
            if($mainRoleGroup->count()>0){
                foreach($mainRoleGroup as $k => $v)
                {
                    $name = $v->name;
                    $roleGroupStatus = $v->status;
                    $num = $v->num;
                    $quotaNum = $v->quota_num;
                    $examMethod = $v->exam_method;

                    $tempData = [
                        'id'=>$v->id,
                        'name'=>$name,
                        'status'=>$roleGroupStatus,
                        'num'=>$num,
                        'quota'=>$quotaNum,
                        'download_status'=>$v->download_status,
                        'app_select_status'=>$v->app_select_status,
                        'enter_end_status'=>$v->enter_end_status,
                        'member_list'=>$v->member_list,
                        'exam_method'=>$examMethod,
                    ];
                    array_push($roleGroup,$tempData);
                }
            }else{
                return response()->json(['message'=>'您还没有添加任何分组，请先添加分组'],200);
            }

            return response()->json(['status'=>$status,'role_group'=>$roleGroup],200);

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 执行规则设置
     */
    public function doSetRule(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $filmfest = Filmfests::find($filmfest_id);
            if($filmfest->is_open === 1){
                return response()->json(['message'=>'竞赛开始后不允许修改'],200);
            }
            $user = $user = \Auth::guard('api')->user()->id;
            $roleGroupId = $request->get('role_group_id',null);
            $roleGroupId = rtrim($roleGroupId,'|');
            DB::beginTransaction();
            //开启或者关闭角色组
            if(!is_null($roleGroupId)){
                $roleGroupId = explode('|',$roleGroupId);
                foreach ($roleGroupId as $k => $V)
                {
                    $this->changeRoleGroup($filmfest_id,$user,$v);
                }
            }


            //角色组评分方式
            $examMethod = $request->get('exam_method',null);
            $examMethod = rtrim($examMethod,'|');           //  role_group_id1:way1|role_group_id2:way2
            if(!is_null($examMethod)){
                $examMethod = explode('|',$examMethod);
                foreach ($examMethod as $k => $v)
                {
                    $unit = explode(':',$v);
                    $roleGroup = FilmfestUserRoleGroup::find($unit[0]);
                    $roleGroup->exam_method = $unit[1];
                    $roleGroup->time_update = time();
                    $roleGroup->save();

                }
            }


            //  改变角色组限额
            $roleGroupNum = $request->get('role_group_num',null);
            $roleGroupNum = rtrim($roleGroupNum,'|');       //  role_group_id1:num1|role_group_id2:num2
            if(is_null($roleGroupNum)){
                $roleGroupNum = explode('|',$roleGroupNum);
                foreach ($roleGroupNum as $k => $v)
                {
                    $unit = explode(':',$v);
                    $this->changeRoleGroupNum($filmfest_id,$user,$unit[0],$unit[1]);
                }
            }

            //  改变角色组权限
            $roleGroupPermission = $request->get('role_group_permission',null);
            $roleGroupPermission = rtrim($roleGroupPermission,'|');
            if(!is_null($roleGroupPermission)){
                $roleGroupPermission = explode('|',$roleGroupPermission);   //  role_group_id1:p1-p2-p3-p4|role_group_id2:p1-p2-p3-p4
                foreach ($roleGroupPermission as $k => $v)
                {
                    $units = explode(':',$v);
                    $unit = explode('-',$units[1]);
                    if((int)$unit[0]===1){
                        $this->changeRoleGroupDownload($filmfest_id,$user,$units[0]);
                    }
                    if((int)$unit[2]===1){
                        $this->changeRoleGroupEnter($filmfest_id,$user,$units[0]);
                    }
                    if((int)$unit[3]===1){
                        $this->memberList($filmfest_id,$user,$units[0]);
                    }
                }
            }


            $filmfest->set_status = 2;
            $filmfest->time_update = time();
            $filmfest-> save();
            DB::commit();
            return response()->json(['message'=>'保存成功'],200);



        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }





    /**
     * @param $filmfest_id
     * @param $user
     * @param $roleGroupId
     * @return \Illuminate\Http\JsonResponse
     * 改变角色组状态
     */
    public function changeRoleGroup($filmfest_id,$user,$roleGroupId)
    {
        try{
            $role_group = FilmfestUserRoleGroup::find($roleGroupId);
            if(!$role_group){
                return response()->json(['message'=>'异常'],200);
            }
            $status = $role_group->status;
            if((int)$status === 1){
                return $this->closeRoleGroup($filmfest_id,$user,$roleGroupId);
            }else{
                return $this->openRoleGroup($filmfest_id,$user,$roleGroupId);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param $filmfest_id
     * @param $user
     * @param $roleGroupId
     * @return \Illuminate\Http\JsonResponse
     * 打开角色组
     */
    private function openRoleGroup($filmfest_id,$user,$roleGroupId)
    {
        try{
            if(is_null($roleGroupId)){
                return response()->json(['message'=>'数据不合法'],200);
            }
//            DB::beginTransaction();
            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
            $roleGroup -> status = 1;
            $roleGroup -> time_update = time();
            $roleGroup -> save();
            $role = $roleGroup->role()->get();
            if($role->count()>0){
                foreach ($role as $k => $v)
                {
                    $newRole = FilmfestUserRole::find($v->id);
                    $newRole -> status =  1;
                    $newRole -> time_update = time();
                    $newRole -> save();
                }
            }
//            $log = new FilmfestUserReviewChildLog;
//            $log -> user_id = $user;
//            $log -> filmfest_id = $filmfest_id;
//            $log -> doing = '开启了'.$roleGroup->name.'角色组';
//            $log -> time_add = time();
//            $log -> time_update = time();
//            $log -> save();
//            DB::commit();
            return response()->json(['message'=>'开启成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['data'=>'not_found'],200);
        }
    }


    /**
     * @param $filmfest_id
     * @param $user
     * @param $roleGroupId
     * @return \Illuminate\Http\JsonResponse
     * 关闭角色组
     */
    private function closeRoleGroup($filmfest_id,$user,$roleGroupId)
    {
        try{
            if(is_null($roleGroupId)){
                return response()->json(['message'=>'数据不合法'],200);
            }
//            DB::beginTransaction();
            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
            $roleGroup -> status = 0;
            $roleGroup -> time_update = time();
            $roleGroup -> save();
            $role = $roleGroup->role()->get();
            if($role->count()>0){
                foreach ($role as $k => $v)
                {
                    $newRole = FilmfestUserRole::find($v->id);
                    $newRole -> status =  0;
                    $newRole -> time_update = time();
                    $newRole -> save();
                }
            }
//            $log = new FilmfestUserReviewChildLog;
//            $log -> user_id = $user;
//            $log -> filmfest_id = $filmfest_id;
//            $log -> doing = '关闭了'.$roleGroup->name.'角色组';
//            $log -> time_add = time();
//            $log -> time_update = time();
//            $log -> save();
//            DB::commit();
            return response()->json(['message'=>'关闭成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['data'=>'not_found'],200);
        }
    }

//    public function increaseRoleGroupNum(Request $request)
//    {
//        try{
//            $filmfest_id = $request->get('id');
//            $user = \Auth::guard('api')->user()->id;
//            $roleGroupId = $request->get('role_group_id');
//            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
//            $groupStatus = $roleGroup->status;
//            if((int)$groupStatus === 0){
//                return response()->json(['message'=>'您需要先开启此角色组'],200);
//            }
//            $oldNum = $roleGroup->num;
//            $newNum = $oldNum+1;
//            $oldSum = 0;
//            $roleGroups = FilmfestUserRoleGroup::where('filmfest_id',$filmfest_id)->where('name','not like','%冻结%')->get();
//            if($roleGroups->count()>0){
//                foreach ($roleGroups as $k => $v)
//                {
//                    $oldSum = $oldSum+$v->num;
//                }
//            }
//            $filmfest = Filmfests::find($filmfest_id);
//            $limitNum = $filmfest->admin_num;
//            if(($oldSum+1)>$limitNum){
//                return response()->json(['message'=>'超出人数限制'],200);
//            }
//            DB::beginTransaction();
//            $roleGroup -> num = $newNum;
//            $roleGroup -> save();
//            $log = new FilmfestUserReviewChildLog;
//            $log -> user_id = $user;
//            $log -> filmfest_id = $filmfest_id;
//            $log -> doing = '对'.$roleGroup->name.'角色组人数加1';
//            $log -> time_add = time();
//            $log -> time_update = time();
//            $log -> save();
//            DB::commit();
//            return response()->json(['message'=>'增加成功'],200);
//        }catch (ModelNotFoundException $q){
//            return response()->json(['error'=>'not_found'],404);
//        }
//    }
//
//    public function decreaseRoleGroupNum(Request $request)
//    {
//        try{
//            $filmfest_id = $request->get('id');
//            $user = \Auth::guard('api')->user()->id;
//            $roleGroupId = $request->get('role_group_id');
//            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
//            $groupStatus = $roleGroup->status;
//            if((int)$groupStatus === 0){
//                return response()->json(['message'=>'您需要先开启此角色组'],200);
//            }
//            $oldNum = $roleGroup->num;
//            if($oldNum<=0){
//                return response()->json(['message'=>'人数已经为0了'],200);
//            }
//            $newNum = $oldNum-1;
//            DB::beginTransaction();
//            $roleGroup -> num = $newNum;
//            $roleGroup -> save();
//            $log = new FilmfestUserReviewChildLog;
//            $log -> user_id = $user;
//            $log -> filmfest_id = $filmfest_id;
//            $log -> doing = '对'.$roleGroup->name.'角色组人数减1';
//            $log -> time_add = time();
//            $log -> time_update = time();
//            $log -> save();
//            DB::commit();
//            return response()->json(['message'=>'减少成功'],200);
//        }catch (ModelNotFoundException $q){
//            return response()->json(['error'=>'not_found'],404);
//        }
//    }


    /**
     * @param $filmfest_id
     * @param $user
     * @param $roleGroupId
     * @param $newNum
     * @return \Illuminate\Http\JsonResponse
     * 改变角色组配额
     */
    public function changeRoleGroupNum($filmfest_id,$user,$roleGroupId,$newNum)
    {
        try{
//            $filmfest_id = $request->get('id');
//            $user = \Auth::guard('api')->user()->id;
//            $roleGroupId = $request->get('role_group_id');
//            $newNum = $request->get('num');
            if($newNum<0){
                return response()->json(['message'=>'不能为负值'],200);
            }
            $sum = 0;
//            DB::beginTransaction();
            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
            $roleGroup -> num = $newNum;
            $roleGroup -> time_update = time();
            $roleGroup -> save();
            $roleGroups = FilmfestUserRoleGroup::where('filmfest_id',$filmfest_id)->where('name','not like','%冻结%')->get();
            if($roleGroups->count()>0){
                foreach ($roleGroups as $k => $v)
                {
                    $sum = $sum+$v->num;
                }
            }
            $filmfest = Filmfests::find($filmfest_id);
            $limitNum = $filmfest->admin_num;
            if($limitNum<$sum){
                DB::rollBack();
                return response()->json(['message'=>$roleGroup->name.'超出人数限制'],200);
            }
//            $log = new FilmfestUserReviewChildLog;
//            $log -> user_id = $user;
//            $log -> filmfest_id = $filmfest_id;
//            $log -> doing = '将'.$roleGroup->name.'角色组人数变更为'.$newNum.'人';
//            $log -> time_add = time();
//            $log -> time_update = time();
//            $log -> save();
//            DB::commit();
            return response()->json(['message'=>'变更成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


//    public function increaseRoleGroupQuotaNum(Request $request)
//    {
//        try{
//            $filmfest_id = $request->get('id');
//            $user = \Auth::guard('api')->user()->id;
//            $roleGroupId = $request->get('role_group_id');
//            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
//            $groupStatus = $roleGroup->status;
//            if((int)$groupStatus === 0){
//                return response()->json(['message'=>'您需要先开启此角色组'],200);
//            }
//            $oldNum = $roleGroup->quota_num;
//            $newNum = $oldNum+1;
//            DB::beginTransaction();
//            $roleGroup -> quota_num = $newNum;
//            $roleGroup -> save();
//            $log = new FilmfestUserReviewChildLog;
//            $log -> user_id = $user;
//            $log -> filmfest_id = $filmfest_id;
//            $log -> doing = '对'.$roleGroup->name.'配额数加1';
//            $log -> time_add = time();
//            $log -> time_update = time();
//            $log -> save();
//            DB::commit();
//            return response()->json(['message'=>'增加成功'],200);
//
//        }catch (ModelNotFoundException $q){
//            return response()->json(['error'=>'not_found'],404);
//        }
//    }
//
//
//    public function decreaseRoleGroupQuotaNum(Request $request)
//    {
//        try{
//            $filmfest_id = $request->get('id');
//            $user = \Auth::guard('api')->user()->id;
//            $roleGroupId = $request->get('role_group_id');
//            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
//            $groupStatus = $roleGroup->status;
//            if((int)$groupStatus === 0){
//                return response()->json(['message'=>'您需要先开启此角色组'],200);
//            }
//            $oldNum = $roleGroup->quota_num;
//            if($oldNum<=0){
//                return response()->json(['message'=>'人数已经为0了'],200);
//            }
//            $newNum = $oldNum-1;
//            DB::beginTransaction();
//            $roleGroup -> quota_num = $newNum;
//            $roleGroup -> time_update = time();
//            $roleGroup -> save();
//            $log = new FilmfestUserReviewChildLog;
//            $log -> user_id = $user;
//            $log -> filmfest_id = $filmfest_id;
//            $log -> doing = '对'.$roleGroup->name.'配额数减1';
//            $log -> time_add = time();
//            $log -> time_update = time();
//            $log -> save();
//            DB::commit();
//            return response()->json(['message'=>'减少成功'],200);
//
//        }catch (ModelNotFoundException $q){
//            return response()->json(['error'=>'not_found'],404);
//        }
//    }
//
//    public function changeRoleGroupQuotaNum(Request $request)
//    {
//        try{
//            $filmfest_id = $request->get('id');
//            $roleGroupId = $request->get('role_group_id');
//            $user = \Auth::guard('api')->user()->id;
//            $num = (int)$request->get('num');
//            if($num<0){
//                return response()->json(['message'=>'不能为负值'],200);
//            }
//            DB::beginTransaction();
//            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
//            $roleGroup -> quota_num = $num;
//            $roleGroup -> time_update = time();
//            $roleGroup -> save();
//            $log = new FilmfestUserReviewChildLog;
//            $log -> user_id = $user;
//            $log -> filmfest_id = $filmfest_id;
//            $log -> doing = '对'.$roleGroup->name.'配额数设置为'.$num;
//            $log -> time_add = time();
//            $log -> time_update = time();
//            $log -> save();
//            DB::commit();
//            return response()->json(['message'=>'设置成功'],200);
//
//        }catch (ModelNotFoundException $q){
//            return response()->json(['error'=>'not_found'],404);
//        }
//    }


    /**
     * @param $filmfest_id
     * @param $user
     * @param $roleGroupId
     * @return \Illuminate\Http\JsonResponse
     * 改变角色组下载状态
     */
    public function changeRoleGroupDownload($filmfest_id,$user,$roleGroupId)
    {
        try{
//            $filmfest_id = $request->get('id');
//            $roleGroupId = $request->get('role_group_id');
//            $user = \Auth::guard('api')->user()->id;
//            DB::beginTransaction();
            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
            $status = $roleGroup->download_status;
            if((int)$status === 1){
                $roleGroup -> download_status = 0;
                $roleGroup -> time_update = time();
                $roleGroup -> save();

                $permission_id = FilmfestUserPermission::where('permission_name','like','%下载%')
                    ->first()->id;
                $role = FilmfestUserRole::where('filmfest_id',$filmfest_id)
                    ->whereHas('group',function ($q) use($roleGroupId){
                        $q->where('filmfest_user_role_group.id',$roleGroupId);
                    })->get();
                if($role->count()>0){
                    foreach ($role as $k => $v)
                    {
                        FilmfestUserRolePermission::where('role_id',$v)->where('permission_id',$permission_id)->delete();
                    }
                }
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '关闭'.$roleGroup->name.'角色组内所有角色对参赛作品的下载权限';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'关闭成功'],200);
            }else{
                $roleGroup -> download_status = 1;
                $roleGroup -> time_update = time();
                $roleGroup -> save();

                $permission_id = FilmfestUserPermission::where('permission_name','like','%下载%')
                    ->first()->id;
                $role = FilmfestUserRole::where('filmfest_id',$filmfest_id)
                    ->whereHas('group',function ($q) use($roleGroupId){
                        $q->where('filmfest_user_role_group.id',$roleGroupId);
                    })->get();
                if($role->count()>0){
                    foreach ($role as $k => $v)
                    {
                        $newRolePermission = new FilmfestUserRolePermission;
                        $newRolePermission -> role_id = $v;
                        $newRolePermission -> permission_id = $permission_id;
                        $newRolePermission -> time_add = time();
                        $newRolePermission -> time_update = time();
                        $newRolePermission -> save();
                    }
                }
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '开启'.$roleGroup->name.'角色组内所有角色对参赛作品的下载权限';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'开启成功'],200);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param $filmfest_id
     * @param $user
     * @param $roleGroupId
     * @return \Illuminate\Http\JsonResponse
     * 改变角色组结束后能否进入后台
     */
    public function changeRoleGroupEnter($filmfest_id,$user,$roleGroupId)
    {
        try{
//            $filmfest_id = $request->get('id');
//            $roleGroupId = $request->get('role_group_id');
//            $user = \Auth::guard('api')->user()->id;
//            DB::beginTransaction();
            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
            $status = $roleGroup->enter_end_status;
            if((int)$status===1){
                $roleGroup -> enter_end_status = 0;
                $roleGroup -> time_update = time();
                $roleGroup -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '禁止'.$roleGroup->name.'角色组内所有角色竞赛结束后访问后台';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'禁止成功'],200);
            }else{
                $roleGroup -> enter_end_status = 1;
                $roleGroup -> time_update = time();
                $roleGroup -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '允许'.$roleGroup->name.'角色组内所有角色竞赛结束后访问后台';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'允许成功'],200);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function startPage(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $filmfest = Filmfests::find($filmfest_id);
            $submitTime = $filmfest->submit_end_time;
            $countDownTime = $submitTime - time();
            if($countDownTime<0){
                return response()->json(['message'=>'已经超过了提交时间'],200);
            }
            $days = floor($countDownTime/86400);
            $hours = floor(($countDownTime-86400*$days)/3600);
            $minutes = floor((($countDownTime-86400*$days)-3600*$hours)/60);
            $seconds = floor((($countDownTime-86400*$days)-3600*$hours)-60*$minutes);
            $countDownTime = $days.'天  '.$hours.':'.$minutes.':'.$seconds;
            $countDown = [
                'countDownTime'=>$countDownTime,
                'des'=>'距离提交作品截止还有',
            ];
            return response()->json(['data'=>$countDown],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 开启竞赛
     */
    public function start(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $user = \Auth::guard('api')->user()->id;
            DB::beginTransaction();
            $filmfest = Filmfest::find($filmfest_id);
            $filmfest -> is_open = 1;
            $filmfest -> set_status = 6;
            $filmfest -> time_update = time();
            $filmfest -> save();

            $log = new FilmfestUserReviewChildLog;
            $log -> user_id = $user;
            $log -> filmfest_id = $filmfest_id;
            $log -> doing = '开启了第'.$filmfest->period.$filmfest->name;
            $log -> time_add = time();
            $log -> time_update = time();
            $log -> save();
            DB::commit();
            return response()->json(['message'=>'开启成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 是否允许结束后显示名单
     */
    public function memberList($filmfest_id,$user,$roleGroupId)
    {
        try{
//            $filmfest_id = $request->get('id');
//            $user = \Auth::guard('api')->user()->id;
//            $roleGroupId = $request->get('role_group_id');
//            DB::beginTransaction();
            $roleGroup = FilmfestUserRoleGroup::find($roleGroupId);
            $status = $roleGroup->member_list;
            if((int)$status===1){
                $roleGroup -> member_list = 0;
                $roleGroup -> time_update = time();
                $roleGroup -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '禁止'.$roleGroup->name.'角色组结束后公布成员';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'禁止成功'],200);
            }else{
                $roleGroup -> member_list = 1;
                $roleGroup -> time_update = time();
                $roleGroup -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '允许'.$roleGroup->name.'角色组结束后公布成员';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'允许成功'],200);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 上传片头
     */
    public function titleOfFilm($filmfest_id,$user,$address)
    {
        try{
            if(is_null($address)){
                return response()->json(['message'=>'数据不合法'],200);
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
            if($width<1920 || $height<1080 || $width/$height != 16/9){
                return response()->json(['message'=>'格式不正确'],200);
            }
            $keys = [];
            array_push($keys,$address);
            $keyPairs = array();
            foreach ($keys as $key)
            {
                $keyPairs[$key] = $key;
            }
            $srcbucket = 'hivideo-video-ects';
            $destbucket = 'hivideo-video';
            $message2 = CloudStorage::copyfile($keyPairs,$srcbucket,$destbucket);
            if($message2[0]['code']==200){
//                DB::beginTransaction();
                $filmfest = Filmfests::find($filmfest_id);
                $filmfest -> title_of_film = "http://video.cdn.hivideo.com/".$address;
                $filmfest -> time_update = time();
                $filmfest -> save();
                $activity_id = $filmfest->activity()->first()->id;
                $joinvideo = JoinVideo::where('activity_id',$activity_id)->first();
                if($joinvideo){
                    $joinvideo ->head_video = "video.cdn.hivideo.com/".$address;
                    $joinvideo -> active = 1;
                    $joinvideo -> updated_at = time();
                    $joinvideo -> duration = $joinvideo->duration + $duration;
                    $joinvideo -> save();

                }else{
                    $newJoinVideo = new JoinVideo;
                    if($filmfest->period){
                        $newJoinVideo -> name = '第'.$filmfest->period.'届'.$filmfest->name.'片头片尾';
                    }else{
                        $newJoinVideo -> name = $filmfest->name.'片头片尾';
                    }
                    $newJoinVideo -> intro = '头尾尺寸必须一致';
                    $newJoinVideo -> head_video = "video.cdn.hivideo.com/".$address;
                    $newJoinVideo -> active = 1;
                    $newJoinVideo -> weight_height = $width.'*'.$height;
                    $newJoinVideo -> activity_id = $activity_id;
                    $newJoinVideo -> created_at = time();
                    $newJoinVideo -> updated_at = time();
                    $newJoinVideo -> duration = $duration;
                    $newJoinVideo -> save();
                }


//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '上传了片头';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//
//                DB::commit();
                return response()->json(['message'=>'上传成功'],200);
            }else{
                return response()->json(['message'=>'上传失败'],200);
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 上传片尾
     */
    public function tailLeader($filmfest_id,$user,$address)
    {
        try{
            if(is_null($address)){
                return response()->json(['message'=>'数据不合法'],200);
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
            if($width<1920 || $height<1080 || $width/$height != 16/9){
                return response()->json(['message'=>'格式不正确'],200);
            }
            $keys = [];
            array_push($keys,$address);
            $keyPairs = array();
            foreach ($keys as $key)
            {
                $keyPairs[$key] = $key;
            }
            $srcbucket = 'hivideo-video-ects';
            $destbucket = 'hivideo-video';
            $message2 = CloudStorage::copyfile($keyPairs,$srcbucket,$destbucket);
            if($message2[0]['code']==200){
//                DB::beginTransaction();
                $filmfest = Filmfests::find($filmfest_id);
                $filmfest -> tail_leader = "http://video.cdn.hivideo.com/".$address;
                $filmfest -> time_update = time();
                $filmfest -> save();

                $activity_id = $filmfest->activity()->first()->id;
                $joinvideo = JoinVideo::where('activity_id',$activity_id)->first();
                if($joinvideo){
                    $joinvideo ->tail_video = "video.cdn.hivideo.com/".$address;
                    $joinvideo -> active = 1;
                    $joinvideo -> updated_at = time();
                    $joinvideo -> duration = $joinvideo->duration + $duration;
                    $joinvideo -> save();

                }else{
                    $newJoinVideo = new JoinVideo;
                    if($filmfest->period){
                        $newJoinVideo -> name = '第'.$filmfest->period.'届'.$filmfest->name.'片头片尾';
                    }else{
                        $newJoinVideo -> name = $filmfest->name.'片头片尾';
                    }
                    $newJoinVideo -> intro = '头尾尺寸必须一致';
                    $newJoinVideo -> tail_video = "video.cdn.hivideo.com/".$address;
                    $newJoinVideo -> active = 1;
                    $newJoinVideo -> weight_height = $width.'*'.$height;
                    $newJoinVideo -> activity_id = $activity_id;
                    $newJoinVideo -> created_at = time();
                    $newJoinVideo -> updated_at = time();
                    $newJoinVideo -> duration = $duration;
                    $newJoinVideo -> save();
                }


//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '上传了片头';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//
//                DB::commit();
                return response()->json(['message'=>'上传成功'],200);
            }else{
                return response()->json(['message'=>'上传失败'],200);
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }




    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 影片设置
     */
    public function filmSet(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $user = \Auth::guard('api')->user()->id;
            $filmfest = Filmfests::find($filmfest_id);
            $titles_of_film_status = $filmfest->titles_of_film_status;
            $tail_leader_status = $filmfest->tail_leader_status;
            $wartermark_status = $filmfest->wartermark_status;
            $wartermark_place = $filmfest->wartermark_place;
            $data = [
                [
                    'name'=>'片头',
                    'status'=>$titles_of_film_status,
                ],
                [
                    'name'=>'片尾',
                    'status'=>$tail_leader_status,
                ],
                [
                    'name'=>'水印',
                    'status'=>$wartermark_status,
                    'place'=>$wartermark_place,
                ]

            ];
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 上传水印
     */
    public function wartermark($filmfest_id,$user,$address)
    {
        try{
            if(is_null($address)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $url = "http://img.ects.cdn.hivideo.com/".$address.'?imageInfo';
            $html = file_get_contents($url);
            $res = json_decode($html, true);
            if($res['width']<400 || $res['height']<400 || $res['size'] > 5*1024*1024 || !in_array($res['format'],['png','jpeg'])){
                return response()->json(['message'=>'格式不正确'],200);
            }
            $keys = [];
            array_push($keys,$address);
            $keyPairs = array();
            foreach ($keys as $key)
            {
                $keyPairs[$key] = $key;
            }
            $srcbucket = 'hivideo-img-ects';
            $destbucket = 'hivideo-img';
            $message2 = CloudStorage::copyfile($keyPairs,$srcbucket,$destbucket);
            if($message2[0]['code']==200){
//                DB::beginTransaction();
                $filmfest = Filmfests::find($filmfest_id);
                $filmfest -> warter_mark = "http://img.cdn.hivideo.com/".$address;
                $filmfest -> time_update = time();
                $filmfest -> save();

//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '上传了水印';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//
//                DB::commit();
                return response()->json(['message'=>'上传成功'],200);
            }else{
                return response()->json(['message'=>'上传失败'],200);
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 改变片头状态
     */
    public function changeTitleOfFilmStatus($filmfest_id,$user)
    {
        try{
//            DB::beginTransaction();
            $filmfest = Filmfests::find($filmfest_id);
            $status = $filmfest->title_of_film_status;
            if((int)$status===1){
                $filmfest -> title_of_film_status = 0;
                $filmfest -> time_update = time();
                $filmfest -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '关闭片头';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'关闭成功'],200);
            }else{
                $filmfest -> title_of_film_status = 1;
                $filmfest -> time_update = time();
                $filmfest -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '打开片头';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'开启成功'],200);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 改变片尾状态
     */
    public function changeTailLeaderStatus($filmfest_id,$user)
    {
        try{
//            DB::beginTransaction();
            $filmfest = Filmfests::find($filmfest_id);
            $status = $filmfest->tail_leader_status;
            if((int)$status===1){
                $filmfest -> tail_leader_status = 0;
                $filmfest -> time_update = time();
                $filmfest -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '关闭片尾';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'关闭成功'],200);
            }else{
                $filmfest -> tail_leader_status = 1;
                $filmfest -> time_update = time();
                $filmfest -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '打开片尾';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'开启成功'],200);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param $filmfest_id
     * @param $user
     * @return \Illuminate\Http\JsonResponse
     * 改变水印状态
     */
    public function changeWartermarkStatus($filmfest_id,$user)
    {
        try{
//            DB::beginTransaction();
            $filmfest = Filmfests::find($filmfest_id);
            $status = $filmfest->wartermark_status;
            if((int)$status===1){
                $filmfest -> wartermark_status = 0;
                $filmfest -> time_update = time();
                $filmfest -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '关闭水印';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'关闭成功'],200);
            }else{
                $filmfest -> wartermark_status = 1;
                $filmfest -> time_update = time();
                $filmfest -> save();
//                $log = new FilmfestUserReviewChildLog;
//                $log -> user_id = $user;
//                $log -> filmfest_id = $filmfest_id;
//                $log -> doing = '打开水印';
//                $log -> time_add = time();
//                $log -> time_update = time();
//                $log -> save();
//                DB::commit();
                return response()->json(['message'=>'开启成功'],200);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param $filmfest_id
     * @param $user
     * @param $newPlace
     * @return \Illuminate\Http\JsonResponse
     * 改变水印位置
     */
    public function changeWarterMarkPlace($filmfest_id,$user,$newPlace)
    {
//        DB::beginTransaction();
        $filmfest = Filmfests::find($filmfest_id);
        switch ($newPlace){
            case 1:
                $des = '水印位置改变为右下角';
                break;
            case 2:
                $des = '水印位置改变为右上角';
                break;
            case 3:
                $des = '水印位置改变为左上角';
                break;
            case 4:
                $des = '水印位置改变为左下角';
                break;
            default:
                $des = '水印位置改变为右下角';
                $newPlace = 1;
                break;
        }
        $filmfest->wartermark_place = $newPlace;
        $filmfest->time_update = time();
        $filmfest->save();

//        $log = new FilmfestUserReviewChildLog;
//        $log -> user_id = $user;
//        $log -> filmfest_id = $filmfest_id;
//        $log -> doing = $des;
//        $log -> time_add = time();
//        $log -> time_update = time();
//        $log -> save();
//        DB::commit();

        return response()->json(['message'=>'改变成功'],200);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 保存影片设置
     */
    public function filmSetSave(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            DB::beginTransaction();
            $filmfest = Filmfests::find($filmfest_id);
            if($filmfest->is_open === 1){
                return response()->json(['message'=>'竞赛开始后不允许修改'],200);
            }
            $user = \Auth::guard('api')->user()->id;
            $is_change_title_of_film = $request->get('is_change_title_of_film',0);
            $is_change_tail_leader = $request->get('is_change_tail_leader',0);
            $is_change_wartermark = $request->get('is_change_wartermark',0);
            $is_up_wartermark = $request->get('is_up_wartermark',0);
            $is_up_title_of_film = $request->get('is_up_title_of_film',0);
            $is_up_tail_leader = $request->get('is_up_tail_leader',0);
            $title_of_film_address = $request->get('title_of_film_address',null);
            $tail_leader_address = $request->get('$tail_leader_address',null);
            $wartermark_address = $request->get('$wartermark_address',null);
            $is_change_wartermark_place = $request->get('is_change_wartermark_place',0);
            $newPlace = $request->get('$newPlace',1);
            if((int)$is_up_title_of_film === 1){
                $this->titleOfFilm($filmfest_id,$user,$title_of_film_address);
            }
            if((int)$is_change_title_of_film === 1){
                $this->changeTitleOfFilmStatus($filmfest_id,$user);
            }
            if((int)$is_change_tail_leader === 1){
                $this->changeTailLeaderStatus($filmfest_id,$user);
            }
            if((int)$is_change_wartermark === 1){
                $this->changeWartermarkStatus($filmfest_id,$user);
            }
            if((int)$is_up_wartermark === 1){
                $this->wartermark($filmfest_id,$user,$wartermark_address);
            }
            if((int)$is_up_tail_leader === 1){
                $this->tailLeader($filmfest_id,$user,$tail_leader_address);
            }
            if((int)$is_change_wartermark_place === 1){
                $this->changeWarterMarkPlace($filmfest_id,$user,$newPlace);
            }


            $filmfest->set_status = 2;
            $filmfest->time_update = time();
            $filmfest-> save();

            DB::commit();

            return response()->json(['message'=>'保存成功']);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 单元和奖项设置页
     */
    public function unitsAndAwards(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $user = \Auth::guard('api')->user()->id;
            $units = Filmfests::find($filmfest_id)->filmFestType()->get();
            $data  = [];
            if($units->count()>0){
                foreach ($units as $k => $v)
                {
                    $unit_id = $v->id;
                    $unit_name = $v->name;
                    $filmfestFilmtype = FilmfestFilmfestType::where('filmfest_id','=',$filmfest_id)
                        ->where('type_id',$unit_id)->first();
                    $filmfestFilmtype_id = $filmfestFilmtype->id;
                    $is_auto_pass = $filmfestFilmtype->is_auto_pass;
                    $lt_time = $filmfestFilmtype->lt_time;
                    $gt_time = $filmfestFilmtype->gt_time;
                    FilmfestUserFilmfestFilmtypeAwards::where('filmfest_filmtype_id',$filmfestFilmtype_id)->where('status',0)->delete();
                    $awards = $filmfestFilmtype->filmfestUserFilmfestFilmtypeAwards()->get();
                    $award = [];
                    if($awards->count()>0){
                        foreach ($awards as $kk => $vv)
                        {
                            if((int)$vv->status === 1){
                                $awardName = $vv->name;
                                $quota = $vv->quota;
                                if($vv->gt_time == ''){
                                    $time = (($vv->lt_time)/60).'分钟以上';
                                }elseif($vv->gt_time < $vv->gt_time){
                                    $time = '时间设置的不合法';
                                }else{
                                    $time = (($vv->lt_time)/60).'-'.(($vv->gt_time)/60);
                                }
                                $tempData = [
                                    'awardName'=>$awardName,
                                    'quota'=>$quota,
                                    'time'=>$time,
                                    'des'=>'',
                                ];
                                array_push($award,$tempData);
                            }

                        }
                        if(is_null($award)){
                            $tempData = [
                                'des'=>'请至少添加一个奖项',
                            ];
                            array_push($award,$tempData);
                        }
                    }else{
                        $tempData = [
                            'des'=>'请至少添加一个奖项',
                        ];
                        array_push($award,$tempData);
                    }
                    $tempDatas = [
                        'unit_id'=>$unit_id,
                        'is_auto_pass'=>$is_auto_pass,
                        'lt_time'=>$lt_time,
                        'gt_time'=>$gt_time,
                        'unit_name'=>$unit_name,
                        'awards' => $award,
                    ];
                    array_push($data,$tempDatas);
                }
            }
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加单元和奖项
     */
    public function addUnitAndAwards(Request $request)
    {
        try{
            DB::beginTransaction();
            $filmfest_id = $request->get('id');
            $filmfest = Filmfests::find($filmfest_id);
            if($filmfest->is_open === 1){
                return response()->json(['message'=>'竞赛开始后不允许修改'],200);
            }
            $user = \Auth::guard('api')->user()->id;
            $unit_id = $request->get('unit_id');
            $awardName = $request->get('awardName',null);
            $quota = $request->get('quota',0);

            if(is_null($awardName)){
                return response()->json(['message'=>'奖项名称不能为空'],200);
            }

            if($quota === 0){
                return response()->json(['message'=>'奖项配额必须大于0'],200);
            }

            $filmfestFilmtype_id = FilmfestFilmfestType::where('filmfest_id',$filmfest_id)->where('type_id',$unit_id)->first()->id;
            $newAward = new FilmfestUserFilmfestFilmtypeAwards;
            $newAward -> name = $awardName;
            $newAward -> quota = $quota;
            $newAward -> filmfest_filmtype_id = $filmfestFilmtype_id;
            $newAward -> time_add = time();
            $newAward -> time_update = time();
            $newAward -> status = 0;
            $newAward -> save();
            DB::commit();
            return response()->json(['message'=>'添加成功，不要忘记保存哦！'],200);


        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 保存单元奖项
     */
    public function saveUnitAndAwards(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            DB::beginTransaction();
            $filmfest = Filmfests::find($filmfest_id);
            if($filmfest->is_open === 1){
                return response()->json(['message'=>'竞赛开始后不允许修改'],200);
            }
            $unitValue = $request->get('unitValue',null);    //    unit_id1:lt_time-gt_time-is_auto_pass|unit_id2:lt_time-gt_time-is_auto_pass
            $unitValue = rtrim($unitValue,'|');
            $unitValue = explode('|',$unitValue);
            foreach ($unitValue as $k => $v)
            {
                $unit = explode(':',$v);
                $filmfestFilmtype = FilmfestFilmfestType::where('filmfest_id',$filmfest_id)->where('type_id',$unit[0])->first();
                $filmfestFilmtype_id = $filmfestFilmtype->id;
                $baseData = explode('-',$unit[1]);
                $filmfestFilmtype = FilmfestFilmfestType::find($filmfestFilmtype_id);
                $filmfestFilmtype->is_auto_pass = $baseData[0];
                $filmfestFilmtype->lt_time = $baseData[1];
                $filmfestFilmtype->gt_time = $baseData[2];
                $filmfestFilmtype->time_update = time();
                $filmfestFilmtype->save();
                $awards = FilmfestUserFilmfestFilmtypeAwards::where('filmfest_filmtype_id',$filmfestFilmtype_id)
                    ->where('status',0)->get();
                if($awards->count()>0){
                    foreach ($awards as $kk => $vv)
                    {
                        $award = FilmfestUserFilmfestFilmtypeAwards::find($vv->id);
                        $award -> status = 1;
                        $award -> time_update = time();
                        $award -> save();
                    }
                }

            }



            $filmfest -> set_status = 4;
            $filmfest -> time_update = time();
            $filmfest -> save();
            DB::commit();
            return response()->json(['message'=>'保存成功'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function editDes(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $filmfest = Filmfests::find($filmfest_id);
            $data = $filmfest->des;
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function doEditDes(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $des = $request->get('des');
            $filmfest = Filmfests::find($filmfest_id);
            $filmfest->des = $des;
            $filmfest->set_status = 5;
            $filmfest -> time_update = time();
            $filmfest -> save();
            return response()->json(['message'=>'success'],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }



}
