<?php

namespace App\Http\Controllers\NewAdmin\User;

use App\Models\LocalAuth;
use App\Models\PrivilegeUser;
use App\Models\User;
use App\Models\User\UserIntegral;
use function foo\func;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserManageController extends Controller
{
    private $paginate = 20;

    private $protocol = 'http://';

    //

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 用户管理主页
     */
    public function index(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $userType = $request->get('userType',null);
            $fans = $request->get('fans',0);
            $playCount = $request->get('playCount',0);
            $productionNum = $request->get('productionNum',0);
            $integral = $request->get('integral',0);
            $page = $request->get('page',1);

            DB::beginTransaction();
            //  注册用户  以及男女比例
            $userNum = User::all()->count();
            if($userNum == 0){
                $womenUserNumProportion = '0%';
                $menUserNumProportion = '0%';
            }else{
                $womenUserNum = User::where('sex','=',0)->get()->count();
                $menUserNum =  User::where('sex','=',1)->get()->count();
                $womenUserNumProportion = (round($womenUserNum/$userNum,2)*100).'%';
                $menUserNumProportion = (round($menUserNum/$userNum,2)*100).'%';
            }

            //  今日新用户及男女比例
            $todayNewUser = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('active','<>','0')->where('active','<>',5)->get()->count();
            if($todayNewUser == 0)
            {
                $todayNewUserWomen ='0'.'%';
                $todayNewUserMen ='0'.'%';
            }else{
                $todayNewUserWomen = (round((User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('sex','=',0)->where('active','<>',5)->where('active','<>','0')->get()->count())/$todayNewUser,2)*100).'%';
                $todayNewUserMen = (round((User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('sex','=',1)->where('active','<>',5)->where('active','<>','0')->get()->count())/$todayNewUser,2)*100).'%';
            }

            if($integral == 0)
            {
                $mainData = User::where('active','<>',0)->where('active','<>',5)->Name($name)->UserType($userType)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }else{
                $mainData = User::WhereHas('hasManyIntegral',function($q) use ($integral){
                    $q->where('integral_count','>=',$integral);
                })->where('active','<>',0)->where('active','<>',5)->Name($name)->UserType($userType)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)
                    ->forPage($page,$this->paginate)
                    ->get();
            }

            //  表格数据
            $data = [];
            foreach ($mainData as $k => $v)
            {
                $role = '';
                if($v->is_vip == 0 && $v->is_phonenumber == 1){
                    $role = '普通用户';
                }elseif ($v->is_vip == 0 && $v->is_phonenumber == 0 && $v->is_thirdparty == 1){
                    $role = '第三方';
                }elseif ($v->is_vip != 0 && $v->is_phonenumber ==1 && $v->verify != 2){
                    $role = 'VIP'.$v->vip;
                }elseif ($v->is_phonenumber == 0 && $v->is_thirdparty == 0){
                    $role = '游客';
                }elseif ($v->is_vip != 0 && $v->is_phonenumber ==1 && $v->verify == 2){
                    $role = '机构';
                }
                $phone = '';
                if($v->is_phonenumber == 1){
                    $phone = $v->hasOneLocalAuth->username;
                }elseif ($v->is_phonenumber == 0 && $v->is_thirdparty == 1){
                    foreach($v->hasManyOAuth as $kk => $vv){
                        $phone .= $vv->oauth_name.' ';
                    }
                }else{
                    $phone = '';
                }

                $integralSum = $v->hasManyIntegral()->first() ? $v->hasManyIntegral->integral_count : 0;
                if($v->verify != 0){
                    if($v->is_phonenumber == 1 ){
                        if($v->active == 1){
                            $behavior = [
                                'active' => '精选',
                                'level' => '升级',
                                'stop' => '冻结',
                            ];
                        }elseif($v->active == 2){
                            $behavior = [
                                'active' => '取消精选',
                                'level' => '升级',
                                'stop' => '冻结',
                            ];
                        }else{
                            $behavior = [];
                        }
                    }else{
                        if($v->active == 1){
                            $behavior = [
                                'active' => '精选',
                                'stop' => '冻结',
                            ];
                        }elseif($v->active == 2){
                            $behavior = [
                                'active' => '取消精选',
                                'stop' => '冻结',
                            ];
                        }else{
                            $behavior = [];
                        }
                    }
                }else{
                    if($v->is_vip != 7 && $v->is_phonenumber == 1 ){

                            $behavior = [
                                'level' => '升级',
                                'stop' => '冻结',
                            ];

                    }else{

                            $behavior = [
                                'stop' => '冻结',
                            ];

                    }
                }




                $tempData = [
                    'id' => $v->id,
                    'sex' => $v->sex,
                    'vipLevel' => $role,
                    'avatar' => $this->protocol.$v->avatar,
                    'nickname' => $v->nickname,
                    'phone' => $phone,
                    'time_add' => $v->created_at,
                    'activeIndex' => '暂无',
                    'work_count' => $v->work_count,
                    'play_count' => $v->browse_times,
                    'integralSum' => $integralSum,
                    'behavior' => $behavior,
                ];

                array_push($data,$tempData);

            }
            $batchBehavior = [
                'dc'=>'精选',
                'cc'=>'取消精选',
                'stop'=>'冻结',
                'levelUp'=>'升级',
            ];
            DB::commit();
            return response()->json(['batchBehavior'=>$batchBehavior,'data'=>$data,'userNum'=>$userNum,'womenUserNumProportion'=>$womenUserNumProportion,'menUserNumProportion'=>$menUserNumProportion,'todayNewUser'=>$todayNewUser,'todayNewUserWomen'=>$todayNewUserWomen,'todayNewUserMen'=>$todayNewUserMen],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function getVipLevel()
    {
        try{
            $vipLevel = [
                [
                    'label'=>'0',
                    'des' => '普通用户',
                ],
                [
                    'label'=>'1',
                    'des' => 'VIP',
                ],

                [
                    'label'=>'2',
                    'des' => '第三方',
                ],
                [
                    'label'=>'3',
                    'des' => '机构',
                ],
            ];
            return response()->json(['data'=>$vipLevel],200);
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 第三方用户页面
     */
    public function thirdparty(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $thirdtype = $request->get('thirdtype',0);
            switch ($thirdtype){
                case 0:
                    $thirdtype = null;
                    break;
                case 1:
                    $thirdtype = 'qq';
                    break;
                case 2:
                    $thirdtype = 'weixin';
                    break;
                case 3:
                    $thirdtype = 'weibo';
                    break;
                default:
                    $thirdtype = null;
                    break;
            }
            $fans = $request->get('fans',0);
            $playCount = $request->get('playCount',0);
            $productionNum = $request->get('productionNum',0);
            $integral = $request->get('integral',0);
            $page = $request->get('page',1);

            DB::beginTransaction();
            //  第三方登录的用户数及各种比例
            $thirdpartyUserNum = User::where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->where('active','<>','0')->where('active','<>',5)->get()->count();
            if($thirdpartyUserNum == 0)
            {
                $thirdpartyQQProportion = '0%';
                $thirdpartyWeixinProportion = '0%';
                $thirdpartyWeiboProportion = '0%';
            }else{
                $thirdpartyQQNum = User::where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->where('active','<>','0')->where('active','<>',5)->whereHas('hasManyOAuth',function ($q){
                    $q->where('oauth_name','=','qq');
                })->get()->count();
                $thirdpartyWeixinNum = User::where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->where('active','<>','0')->where('active','<>',5)->whereHas('hasManyOAuth',function ($q){
                    $q->where('oauth_name','=','weixin');
                })->get()->count();
                $thirdpartyWeiboNum = User::where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->where('active','<>','0')->where('active','<>',5)->whereHas('hasManyOAuth',function ($q){
                    $q->where('oauth_name','=','weibo');
                })->get()->count();
                $thirdpartyQQProportion = (round($thirdpartyQQNum/$thirdpartyUserNum,2)*100).'%';
                $thirdpartyWeixinProportion = (round($thirdpartyWeixinNum/$thirdpartyUserNum,2)*100).'%';
                $thirdpartyWeiboProportion = (round($thirdpartyWeiboNum/$thirdpartyUserNum,2)*100).'%';
            }



            //  今日新增及各种比例
            $todayNewUserNum = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->where('active','<>','0')->where('active','<>',5)->get()->count();
            if($todayNewUserNum == 0)
            {
                $todayNewQQProportion = '0%';
                $todayNewWeixinProportion = '0%';
                $todayNewWeiboProportion = '0%';
            }else{
                $todayNewQQNum = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->where('active','<>','0')->where('active','<>',5)->whereHas('hasManyOAuth',function ($q){
                    $q->where('oauth_name','=','qq');
                })->get()->count();
                $todayNewWeixinNum = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->where('active','<>','0')->where('active','<>',5)->whereHas('hasManyOAuth',function ($q){
                    $q->where('oauth_name','=','weixin');
                })->get()->count();
                $todayNewWeiboNum = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->where('active','<>','0')->where('active','<>',5)->whereHas('hasManyOAuth',function ($q){
                    $q->where('oauth_name','=','weibo');
                })->get()->count();

                $todayNewQQProportion = (round($todayNewQQNum/$todayNewUserNum,2)*100).'%';
                $todayNewWeixinProportion = (round($todayNewWeixinNum/$todayNewUserNum,3)*100).'%';
                $todayNewWeiboProportion = (round($todayNewWeiboNum/$todayNewUserNum,3)*100).'%';
            }

            //  主要数据
            if($integral == 0){
                $maindata = User::whereHas('hasManyOAuth',function ($q) use ($thirdtype){
                    $q->where('oauth_name','like',"%$thirdtype%");
                })->where('active','<>',0)->where('active','<>',5)->where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)
                    ->forPage($page,$this->paginate)
                    ->get();
            }else{
                $maindata = User::WhereHas('hasManyIntegral',function($q) use ($integral){
                    $q->where('integral_count','>=',$integral);
                })->whereHas('hasManyOAuth',function ($q) use ($thirdtype){
                    $q->where('oauth_name','like',"%$thirdtype%");
                })->where('active','<>',0)->where('active','<>',5)->where('is_thirdparty','=',1)->where('is_phonenumber','=','0')->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)
                    ->forPage($page,$this->paginate)
                    ->get();
            }

            $data = [];
            foreach($maindata as $k => $v)
            {
                $integralSum = $v->hasManyIntegral()->first() ? $v->hasManyIntegral->integral_count : 0;
                $third = $v->hasManyOAuth()->first() ? $v->hasManyOAuth()->first()->oauth_name : ' ';
                if($v->verify != 0){
                    if($v->active == 1){
                        $behavior = [
                            'active' => '精选',
                            'stop' => '冻结',
                        ];
                    }elseif($v->active == 2){
                        $behavior = [
                            'active' => '取消精选',
                            'stop' => '冻结',
                        ];
                    }else{
                        $behavior = [];
                    }
                }else{
                    $behavior = [
                        'stop' => '冻结',
                    ];
                }

                $tempData = [
                    'id' => $v->id,
                    'sex' => $v->sex,
                    'third' => $third,
                    'avatar' => $this->protocol.$v->avatar,
                    'nickname' => $v->nickname,
                    'phone' => '未绑定',
                    'time_add' => $v->created_at,
                    'activeIndex' => '暂无',
                    'work_count' => $v->work_count,
                    'play_count' => $v->browse_times,
                    'integralSum' => $integralSum,
                    'behavior' => $behavior,
                ];

                array_push($data,$tempData);
            }
            $batchBehavior = [
                'dc'=>'精选',
                'cc'=>'取消精选',
                'stop'=>'冻结',
            ];
            DB::commit();
            return response()->json(['batchBehavior'=>$batchBehavior,'data'=>$data,'thirdpartyUserNum'=>$thirdpartyUserNum,'thirdpartyQQProportion'=>$thirdpartyQQProportion,'thirdpartyWeixinProportion'=>$thirdpartyWeixinProportion,'thirdpartyWeiboProportion'=>$thirdpartyWeiboProportion,'todayNewUserNum'=>$todayNewUserNum,'todayNewQQProportion'=>$todayNewQQProportion,'todayNewWeixinProportion'=>$todayNewWeixinProportion,'todayNewWeiboProportion'=>$todayNewWeiboProportion],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * 第三方页面的第三方类别条件
     */
    public function thirdType()
    {
        try{
            $data=[
                [
                    'label' => '0',
                    'des' => '全部',
                ],
                [
                    'label' => '1',
                    'des' => 'QQ',
                ],
                [
                    'label' => '2',
                    'des' => '微信',
                ],
                [
                    'label' => '3',
                    'des' => '新浪微博',
                ],


            ];
            return response()->json(['data'=>$data],200);
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * VIP用户页面
     */
    public function vipUser(Request $request)
    {
        try{

            $name = $request->get('name',null);
            $vipLevel = $request->get('vipLevel',null);
            $fans = $request->get('fans',0);
            $playCount = $request->get('playCount',0);
            $productionNum = $request->get('productionNum',0);
            $integral = $request->get('integral',0);
            $page = $request->get('page',1);

            //  vip用户总数和男女比例
            DB::beginTransaction();
            $vipUserNum = User::where('is_vip','>',0)->where('active','<>',0)->where('active','<>',5)->where('is_phonenumber','=',1)->get()->count();
            if($vipUserNum == 0)
            {
                $vipManUserProportino = '0%';
                $vipWomenUserProportino = '0%';
            }else{
                $vipManUserNum = User::where('sex','=',1)->where('is_vip','>',0)->where('active','<>',5)->where('active','<>',0)->where('is_phonenumber','=',1)->get()->count();
                $vipWomenUserNum = User::where('sex','=',0)->where('is_vip','>',0)->where('active','<>',5)->where('active','<>',0)->where('is_phonenumber','=',1)->get()->count();
                $vipManUserProportino = (round($vipManUserNum/$vipUserNum,2)*100).'%';
                $vipWomenUserProportino = (round($vipWomenUserNum/$vipUserNum,2)*100).'%';
            }

            //  今日新增VIP和男女比例
            $todayNewVipUserNum = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('is_vip','>',0)->where('active','<>',5)->where('active','<>',0)->where('is_phonenumber','=',1)->get()->count();
            if($todayNewVipUserNum == 0){
                $todayMan = 0;
                $todayWomen = 0;
            }else{
                $todayMan = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('sex','=',1)->where('is_vip','>',0)->where('active','<>',5)->where('active','<>',0)->where('is_phonenumber','=',1)->get()->count();
                $todayWomen = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('sex','=',0)->where('is_vip','>',0)->where('active','<>',5)->where('active','<>',0)->where('is_phonenumber','=',1)->get()->count();
            }

            //  主要表格数据
            if($integral == 0)
            {
                $mainData = User::where('active','<>',0)->where('active','<>',5)->where('is_vip','>',0)->where('is_phonenumber','=',1)->Name($name)->VipLevel($vipLevel)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }else{
                $mainData = User::WhereHas('hasManyIntegral',function($q) use ($integral){
                        $q->where('integral_count','>=',$integral);
                    })->where('active','<>',0)->where('active','<>',5)->where('is_vip','>',0)->where('is_phonenumber','=',1)->Name($name)->VipLevel($vipLevel)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)
                        ->forPage($page,$this->paginate)
                        ->get();
            }
            $data = [];
            foreach ($mainData as $k => $v)
            {

                $phone = $v->hasOneLocalAuth->username;
                $integralSum = $v->hasManyIntegral()->first() ? $v->hasManyIntegral->integral_count : 0;
                if($v->verify != 0){
                        if($v->active == 1){
                            $behavior = [
                                'active' => '精选',
                                'level' => '升级',
                                'stop' => '冻结',
                            ];
                        }elseif($v->active == 2){
                            $behavior = [
                                'active' => '取消精选',
                                'level' => '升级',
                                'stop' => '冻结',
                            ];
                        }else{
                            $behavior = [];
                        }

                }else{

                            $behavior = [
                                'level' => '升级',
                                'stop' => '冻结',
                            ];


                }


                $tempData = [
                    'id' => $v->id,
                    'sex' => $v->sex,
                    'vipLevel' => 'VIP'.$v->is_vip,
                    'avatar' => $this->protocol.$v->avatar,
                    'nickname' => $v->nickname,
                    'phone' => $phone,
                    'time_add' => $v->created_at,
                    'activeIndex' => '暂无',
                    'work_count' => $v->work_count,
                    'play_count' => $v->browse_times,
                    'integralSum' => $integralSum,
                    'behavior' => $behavior,
                ];

                array_push($data,$tempData);
            }
            $batchBehavior = [
                'dc'=>'精选',
                'cc'=>'取消精选',
                'stop'=>'冻结',
                'levelUp'=>'升级',
            ];
            DB::commit();
            return response()->json(['batchBehavior'=>$batchBehavior,'data'=>$data,'vipUserNum'=>$vipUserNum,'vipManUserProportino'=>$vipManUserProportino,'vipWomenUserProportino'=>$vipWomenUserProportino,'todayNewVipUserNum'=>$todayNewVipUserNum,'todayMan'=>$todayMan,'todayWomen'=>$todayWomen],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * vip用户界面专用类别
     */
    public function vipLevel()
    {
        try{
            $vipLevel = [];
            for($i=0;$i<=100;$i++)
            {
                $lv = [
                    'label'=>$i,
                    'des'=>'VIP'.$i
                ];
                array_push($vipLevel,$lv);
            }
            array_push($vipLevel,['label'=>101,'des'=>'VIP100以上']);
            return response()->json(['data'=>$vipLevel],200);
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 机构页面
     */
    public function organization(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $checker = $request->get('checker',null);
            $fans = $request->get('fans',0);
            $playCount = $request->get('playCount',0);
            $productionNum = $request->get('productionNum',0);
            $integral = $request->get('integral',0);
            $page = $request->get('page',1);

            //  机构用户数量及男女比例
            DB::beginTransaction();
            $organizationUserNum = User::where('active','<>',0)->where('active','<>',5)->where('verify','=',2)->where('is_phonenumber','=',1)->get()->count();
            if($organizationUserNum == 0)
            {
                $organizationManProportion = '0%';
                $organizationWomenProportion = '0%';
            }else{
                $organizationManNum = User::where('active','<>',0)->where('active','<>',5)->where('sex','=',1)->where('verify','=',2)->where('is_phonenumber','=',1)->get()->count();
                $organizationWomenNum = User::where('active','<>',0)->where('active','<>',5)->where('sex','=',0)->where('verify','=',2)->where('is_phonenumber','=',1)->get()->count();
                $organizationManProportion = (round($organizationManNum/$organizationUserNum,2)*100).'%';
                $organizationWomenProportion = (round($organizationWomenNum/$organizationUserNum,2)*100).'%';
            }

            //  今日新增机构 用户数量和男女数量
            $todayNewOrganizationNum = User::where('active','<>',0)->where('active','<>',5)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('verify','=',2)->where('is_phonenumber','=',1)->get()->count();
            if($todayNewOrganizationNum == 0)
            {
                $todayNewOrganizationManNum = 0;
                $todayNewOrganizationWomenNum = 0;
            }else{
                $todayNewOrganizationManNum = User::where('active','<>',0)->where('active','<>',5)->where('sex','=',1)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('verify','=',2)->where('is_phonenumber','=',1)->get()->count();
                $todayNewOrganizationWomenNum = User::where('active','<>',0)->where('active','<>',5)->where('sex','=',0)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('verify','=',2)->where('is_phonenumber','=',1)->get()->count();
            }

            //  表格主要数据
            if($integral == 0)
            {
                $mainData = User::where('active','<>',0)->where('active','<>',5)->where('verify','=',2)->where('is_phonenumber','=',1)->Checker($checker)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }else{
                $mainData = User::WhereHas('hasManyIntegral',function($q) use ($integral){
                    $q->where('integral_count','>=',$integral);
                })->where('verify_checker','=',$checker)->where('active','<>',0)->where('active','<>',5)->where('verify','=',2)->where('is_phonenumber','=',1)->Checker($checker)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }

            $data = [];
            foreach ($mainData as $k => $v)
            {
                $phone = $v->hasOneLocalAuth->username;
                $verify_checker = $v->verifyCheck()->first() ? $v->verifyCheck->name : " ";

                if($v->active == 1){
                    $behavior = [
                        'active' => '精选',
                        'stop' => '冻结',
                    ];
                }elseif($v->active == 2){
                    $behavior = [
                        'active' => '取消精选',
                        'stop' => '冻结',
                    ];
                }else{
                    $behavior = [];
                }

                $tempData = [
                    'id' => $v->id,
                    'sex' => $v->sex,
                    'avatar' => $this->protocol.$v->avatar,
                    'nickname' => $v->nickname,
                    'phone' => $phone,
                    'des' =>$v->verify_info,
                    'time_add' => $v->created_at,
                    'checker' => $verify_checker,
                    'verify_time' => $v->verify_time,
                    'behavior' => $behavior,
                ];

                array_push($data,$tempData);
            }
            $batchBehavior = [
                'dc'=>'精选',
                'cc'=>'取消精选',
                'stop'=>'冻结',
            ];
            DB::commit();
            return response()->json(['batchBehavior'=>$batchBehavior,'data'=>$data,'organizationUserNum'=>$organizationUserNum,'organizationManProportion'=>$organizationManProportion,'organizationWomenProportion'=>$organizationWomenProportion,'todayNewOrganizationNum'=>$todayNewOrganizationNum,'todayNewOrganizationManNum'=>$todayNewOrganizationManNum,'todayNewOrganizationWomenNum'=>$todayNewOrganizationWomenNum],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 个人认证用户页面
     */
    public function verifyUser(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $checker = $request->get('checker',null);
            $fans = $request->get('fans',0);
            $playCount = $request->get('playCount',0);
            $productionNum = $request->get('productionNum',0);
            $integral = $request->get('integral',0);
            $page = $request->get('page',1);

            //  机构用户数量及男女比例
            DB::beginTransaction();
            $verifyUserNum = User::where('active','<>',0)->where('active','<>',5)->where('verify','=',1)->where('is_phonenumber','=',1)->get()->count();
            if($verifyUserNum == 0)
            {
                $verifyManProportion = '0%';
                $verifyWomenProportion = '0%';
            }else{
                $verifyManNum = User::where('active','<>',0)->where('active','<>',5)->where('sex','=',1)->where('verify','=',1)->where('is_phonenumber','=',1)->get()->count();
                $verifyWomenNum = User::where('active','<>',0)->where('active','<>',5)->where('sex','=',0)->where('verify','=',1)->where('is_phonenumber','=',1)->get()->count();
                $verifyManProportion = (round($verifyManNum/$verifyUserNum,2)*100).'%';
                $verifyWomenProportion = (round($verifyWomenNum/$verifyUserNum,2)*100).'%';
            }

            //  今日新增机构 用户数量和男女数量
            $todayNewVerifyNum = User::where('active','<>',0)->where('active','<>',5)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('verify','=',1)->where('is_phonenumber','=',1)->get()->count();
            if($todayNewVerifyNum == 0)
            {
                $todayNewVerifyManNum = 0;
                $todayNewVerifyWomenNum = 0;
            }else{
                $todayNewVerifyManNum = User::where('active','<>',0)->where('active','<>',5)->where('sex','=',1)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('verify','=',1)->where('is_phonenumber','=',1)->get()->count();
                $todayNewVerifyWomenNum = User::where('active','<>',0)->where('active','<>',5)->where('sex','=',0)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('verify','=',1)->where('is_phonenumber','=',1)->get()->count();
            }

            //  表格主要数据
            if($integral == 0)
            {
                $mainData = User::where('active','<>',0)->where('active','<>',5)->Checker($checker)->where('verify','=',1)->where('is_phonenumber','=',1)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }else{
                $mainData = User::WhereHas('hasManyIntegral',function($q) use ($integral){
                    $q->where('integral_count','>=',$integral);
                })->Checker($checker)->where('active','<>',0)->where('active','<>',5)->where('verify','=',1)->where('is_phonenumber','=',1)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }


            $data = [];
            foreach ($mainData as $k => $v)
            {
                $phone = $v->hasOneLocalAuth->username;
                $verify_checker = $v->verifyCheck()->first() ? $v->verifyCheck->name : " ";

                    if($v->active == 1){
                        $behavior = [
                            'active' => '精选',
                            'level' => '升级',
                            'stop' => '冻结',
                        ];
                    }elseif($v->active == 2){
                        $behavior = [
                            'active' => '取消精选',
                            'level' => '升级',
                            'stop' => '冻结',
                        ];
                    }else{
                        $behavior = [];
                    }


                $tempData = [
                    'id' => $v->id,
                    'sex' => $v->sex,
                    'avatar' => $this->protocol.$v->avatar,
                    'nickname' => $v->nickname,
                    'phone' => $phone,
                    'des' =>$v->verify_info,
                    'time_add' => $v->created_at,
                    'checker' => $verify_checker,
                    'verify_time' => $v->verify_time,
                    'behavior' => $behavior,
                ];

                array_push($data,$tempData);
            }
            $batchBehavior = [
                'dc'=>'精选',
                'cc'=>'取消精选',
                'stop'=>'冻结',
                'levelUp'=>'升级',
            ];
            DB::commit();
            return response()->json(['batchBehavior'=>$batchBehavior,'data'=>$data,'verifyUserNum'=>$verifyUserNum,'verifyManProportion'=>$verifyManProportion,'verifyWomenProportion'=>$verifyWomenProportion,'todayNewVerifyNum'=>$todayNewVerifyNum,'todayNewOrganizationManNum'=>$todayNewVerifyManNum,'todayNewVerifyWomenNum'=>$todayNewVerifyWomenNum],200);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 创作者页面
     */
    public function creater(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $checker = $request->get('checker',null);
            $fans = $request->get('fans',0);
            $playCount = $request->get('playCount',0);
            $productionNum = $request->get('productionNum',0);
            $integral = $request->get('integral',0);
            $page = $request->get('page',1);

            //  创作者用户与男女比例
            DB::beginTransaction();
            $createrNum = PrivilegeUser::where('type','=',1)->get()->count();
            if($createrNum == 0)
            {
                $manCreaterProportion = 0;
                $womenCreaterProportion = 0;
            }else{
                $manCreaterNum = PrivilegeUser::whereHas('user',function ($q){
                    $q->where('sex','=',1);
                })->where('type','=',1)->get()->count();

                $womenCreaterNum = PrivilegeUser::whereHas('user',function ($q){
                    $q->where('sex','=',0);
                })->where('type','=',1)->get()->count();
                $manCreaterProportion = (round($manCreaterNum/$createrNum,2)*100).'%';
                $womenCreaterProportion = (round($womenCreaterNum/$createrNum,2)*100).'%';
            }

            //  今日新增创造者和男女数量
            $todayNewCreaterNum = PrivilegeUser::where('checker_time','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('type','=',1)->get()->count();
            if($todayNewCreaterNum == 0)
            {
                $todayNewManCreaterNum = 0;
                $todayNewWomenCreaterNum = 0;
            }else{
                $todayNewManCreaterNum = PrivilegeUser::whereHas('user',function ($q){
                    $q->where('sex','=',1);
                })->where('checker_time','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('type','=',1)->get()->count();

                $todayNewWomenCreaterNum = PrivilegeUser::whereHas('user',function ($q){
                    $q->where('sex','=',0);
                })->where('checker_time','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('type','=',1)->get()->count();
            }

            //  表格主要数据
            if($integral == 0)
            {
                $mainData = User::where('active','<>',0)->where('active','<>',5)->whereHas('privilegeUser',function ($q) use($checker) {
                    if(is_null($checker)){
                        $q->where('type','=',1);
                    }else{
                        $q->where('checker_id','=',$checker)->where('type','=',1);
                    }
                })->where('verify','<>',0)->where('is_phonenumber','=',1)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }else{
                $mainData = User::WhereHas('hasManyIntegral',function($q) use ($integral){
                    $q->where('integral_count','>=',$integral);
                })->whereHas('privilegeUser',function ($q) use($checker) {
                    if(is_null($checker)){
                        $q->where('type','=',1);
                    }else{
                        $q->where('checker_id','=',$checker)->where('type','=',1);
                    }
                })->where('verify','<>',0)->where('active','<>',0)->where('active','<>',5)->where('is_phonenumber','=',1)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }

            $data = [];
            foreach ($mainData as $k => $v)
            {

                $des = $v->privilegeUser()->where('type','=',1)->first() ? $v->privilegeUser()->where('type','=',1)->first()->checker_des: '';
                $phone = $v->hasOneLocalAuth->username;
                $checker_people = $v->privilegeUser()->where('type','=',1)->first()->administoar()->first()->name;

                    if($v->active == 1){
                        $behavior = [
                            'active' => '精选',
                            'stop' => '冻结',
                        ];
                    }elseif($v->active == 2){
                        $behavior = [
                            'active' => '取消精选',
                            'stop' => '冻结',
                        ];
                    }else{
                        $behavior = [];
                    }

                $tempData = [
                    'id' => $v->id,
                    'avatar' => $this->protocol.$v->acatar,
                    'nickname' => $v->nickname,
                    'phone' => $phone,
                    'des' => $des,
                    'time_add' => $v->created_at,
                    'checker_time' => $v->checker_time,
                    'checker' => $checker_people,
                    'behavior' => $behavior,
                ];
                array_push($data,$tempData);
            }
            $batchBehavior = [
                'dc'=>'精选',
                'cc'=>'取消精选',
                'stop'=>'冻结',
            ];
            DB::commit();
            return response()->json(['batchBehavior'=>$batchBehavior,'data'=>$data,'createrNum'=>$createrNum,'manCreaterProportion'=>$manCreaterProportion,'womenCreaterProportion'=>$womenCreaterProportion,'todayNewCreaterNum'=>$todayNewCreaterNum,'todayNewManCreaterNum'=>$todayNewManCreaterNum,'todayNewWomenCreaterNum'=>$todayNewWomenCreaterNum],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 审查者界面
     */
    public function investigate(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $checker = $request->get('checker',null);
            $fans = $request->get('fans',0);
            $playCount = $request->get('playCount',0);
            $productionNum = $request->get('productionNum',0);
            $integral = $request->get('integral',0);
            $page = $request->get('page',1);

            //  审查者用户与男女比例
            DB::beginTransaction();
            $investigateNum = PrivilegeUser::where('type','=',2)->get()->count();
            if($investigateNum == 0)
            {
                $manInvestigateProportion = 0;
                $womenInvestigateProportion = 0;
            }else{
                $manInvestigateNum = PrivilegeUser::whereHas('user',function ($q){
                    $q->where('sex','=',1);
                })->where('type','=',2)->get()->count();

                $womenInvestigatenum = PrivilegeUser::whereHas('user',function ($q){
                    $q->where('sex','=',0);
                })->where('type','=',2)->get()->count();
                $manInvestigateProportion = (round($manInvestigateNum/$investigateNum,2)*100).'%';
                $womenInvestigateProportion = (round($womenInvestigatenum/$investigateNum,2)*100).'%';
            }

            //  今日新增审查者和男女数量
            $todayNewInvestigateNum = PrivilegeUser::where('checker_time','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('type','=',2)->get()->count();
            if($todayNewInvestigateNum == 0)
            {
                $todayNewManInvestigateNum = 0;
                $todayNewWomenInvestigateNum = 0;
            }else{
                $todayNewManInvestigateNum = PrivilegeUser::whereHas('user',function ($q){
                    $q->where('sex','=',1);
                })->where('checker_time','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('type','=',2)->get()->count();

                $todayNewWomenInvestigateNum = PrivilegeUser::whereHas('user',function ($q){
                    $q->where('sex','=',0);
                })->where('checker_time','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('type','=',2)->get()->count();
            }

            //  表格主要数据
            if($integral == 0)
            {
                $mainData = User::where('active','<>',0)->where('active','<>',5)->whereHas('privilegeUser',function ($q) use($checker) {
                    if(is_null($checker)){
                        $q->where('type','=',2);
                    }else{
                        $q->where('checker_id','=',$checker)->where('type','=',2);
                    }
                })->where('verify','<>',0)->where('is_phonenumber','=',1)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }else{
                $mainData = User::WhereHas('hasManyIntegral',function($q) use ($integral){
                    $q->where('integral_count','>=',$integral);
                })->whereHas('privilegeUser',function ($q) use($checker) {
                    if(is_null($checker)){
                        $q->where('type','=',2);
                    }else{
                        $q->where('checker_id','=',$checker)->where('type','=',2);
                    }
                })->where('verify','<>',0)->where('active','<>',0)->where('active','<>',5)->where('is_phonenumber','=',1)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }

            $data = [];
            foreach ($mainData as $k => $v)
            {

                $des = $v->privilegeUser()->where('type','=',2)->first() ? $v->privilegeUser()->where('type','=',2)->first()->checker_des: '';
                $phone = $v->hasOneLocalAuth->username;
                $checker_people = $v->privilegeUser()->where('type','=',2)->first()->administoar()->first()->name;
                if($v->active == 1){
                    $behavior = [
                        'active' => '精选',
                        'stop' => '冻结',
                    ];
                }elseif($v->active == 2){
                    $behavior = [
                        'active' => '取消精选',
                        'stop' => '冻结',
                    ];
                }else{
                    $behavior = [];
                }

                $tempData = [
                    'id' => $v->id,
                    'avatar' => $this->protocol.$v->acatar,
                    'nickname' => $v->nickname,
                    'phone' => $phone,
                    'des' => $des,
                    'time_add' => $v->created_at,
                    'checker_time' => $v->checker_time,
                    'checker' => $checker_people,
                    'behavior' => $behavior,
                ];
                array_push($data,$tempData);
            }
            $batchBehavior = [
                'dc'=>'精选',
                'cc'=>'取消精选',
                'stop'=>'冻结',
            ];
            DB::commit();
            return response()->json(['batchBehaivor'=>$batchBehavior,'data'=>$data,'investigateNum'=>$investigateNum,'manInvestigateProportion'=>$manInvestigateProportion,'womenInvestigateProportion'=>$womenInvestigateProportion,'todayNewInvestigateNum'=>$todayNewInvestigateNum,'todayNewManInvestigateNum'=>$todayNewManInvestigateNum,'todayNewWomenInvestigateNum'=>$todayNewWomenInvestigateNum],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function choiceness(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $checker = $request->get('checker',null);
            $fans = $request->get('fans',0);
            $playCount = $request->get('playCount',0);
            $productionNum = $request->get('productionNum',0);
            $integral = $request->get('integral',0);
            $page = $request->get('page',1);

            DB::beginTransaction();
            //  精选用户数以及男女比例
            $choicenessNum = User::where('active','=',2)->get()->count();
            if($choicenessNum == 0)
            {
                $manChoicenessProportion = '0%';
                $womenChoicenessProportion = '0%';
            }else{
                $manChoicenessNum = User::where('active','=',2)->where('sex','=','1')->get()->count();
                $womenChoicenessNum = User::where('active','=',2)->where('sex','=','0')->get()->count();
                $manChoicenessProportion = (round($manChoicenessNum/$choicenessNum,2)*100).'%';
                $womenChoicenessProportion = (round($womenChoicenessNum/$choicenessNum,2)*100).'%';
            }


            //  今日新增精选用户及男女数量
            $todayNewChoicenessNum = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('active','=',2)->get()->count();
            if($todayNewChoicenessNum == 0){
                $todayNewChoicenessManNum = 0;
                $todayNewChoicenessWomenNum = 0;
            }else{
                $todayNewChoicenessManNum = User::where('sex','=',1)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('active','=',2)->get()->count();
                $todayNewChoicenessWomenNum = User::where('sex','=',0)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('active','=',2)->get()->count();
            }

            if($integral == 0)
            {
                $mainData = User::where('active','=',2)->Choiceness($checker)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }else{
                $mainData = User::WhereHas('hasManyIntegral',function($q) use ($integral){
                    $q->where('integral_count','>=',$integral);
                })->Choiceness($checker)->where('active','=',2)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }

            $data = [];
            foreach ($mainData as $k => $v)
            {
                $phone = $v->hasOneLocalAuth->username;
                $checker_people = $v->choiceness()->first()->name;
                $behavior = [
                    'cc' => '下架',
                    'stop' => '冻结'
                ];
                $tempData = [
                    'id' => $v->id,
                    'avatar' => $this->protocol.$v->avatar,
                    'nickname' => $v->nickname,
                    'phone' => $phone,
                    'des' => $v->verify_info,
                    'created_time' => $v->created_at,
                    'activeIndex' => '暂无',
                    'checnker' => $checker_people,
                    'behavior' => $behavior,

                ];

                array_push($data,$tempData);

            }
            $batchBehavior = [
                'cc' => '下架',
                'stop' => '冻结'
            ];
            DB::commit();
            return response()->json(['batchBehavior'=>$batchBehavior,'data'=>$data,'choicenessNum'=>$choicenessNum,'manChoicenessProportion'=>$manChoicenessProportion,'womenChoicenessProportion'=>$womenChoicenessProportion,'todayNewChoicenessNum'=>$todayNewChoicenessNum,'todayNewChoicenessManNum'=>$todayNewChoicenessManNum,'todayNewChoicenessWomenNum'=>$todayNewChoicenessWomenNum],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function stop(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $checker = $request->get('checker',null);
            $fans = $request->get('fans',0);
            $playCount = $request->get('playCount',0);
            $productionNum = $request->get('productionNum',0);
            $integral = $request->get('integral',0);
            $page = $request->get('page',1);

            DB::beginTransaction();
            //  冻结用户数以及男女比例
            $stopNum = User::where('active','=',0)->get()->count();
            if($stopNum == 0)
            {
                $manstopProportion = '0%';
                $womenstopProportion = '0%';
            }else{
                $manstopNum = User::where('active','=',0)->where('sex','=','1')->get()->count();
                $womenstopNum = User::where('active','=',0)->where('sex','=','0')->get()->count();
                $manstopProportion = (round($manstopNum/$stopNum,2)*100).'%';
                $womenstopProportion = (round($womenstopNum/$stopNum,2)*100).'%';
            }


            //  今日新增精选用户及男女数量
            $todayNewstopNum = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('active','=',0)->get()->count();
            if($todayNewstopNum == 0){
                $todayNewstopManNum = 0;
                $todayNewstopWomenNum = 0;
            }else{
                $todayNewstopManNum = User::where('sex','=',1)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('active','=',0)->get()->count();
                $todayNewstopWomenNum = User::where('sex','=',0)->where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('active','=',0)->get()->count();
            }

            if($integral == 0)
            {
                $mainData = User::where('active','=',0)->Stop($checker)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }else{
                $mainData = User::WhereHas('hasManyIntegral',function($q) use ($integral){
                    $q->where('integral_count','>=',$integral);
                })->Stop($checker)->where('active','=',0)->Name($name)->Fans($fans)->PlayCount($playCount)->ProductionNum($productionNum)->forPage($page,$this->paginate)->get();
            }

            $data = [];
            foreach ($mainData as $k => $v)
            {
                $phone = $v->hasOneLocalAuth()->first()?$v->hasOneLocalAuth()->first()->username:'';
                $checker_people = $v->stop()->first()->name;
                $behavior = [
                    'cs' => '解冻',
                    'delete' => '删除'
                ];
                $tempData = [
                    'id' => $v->id,
                    'avatar' => $this->protocol.$v->avatar,
                    'nickname' => $v->nickname,
                    'phone' => $phone,
                    'des' => $v->stop_cause,
                    'created_time' => $v->created_at,
                    'activeIndex' => '暂无',
                    'checnker' => $checker_people,
                    'behavior' => $behavior,

                ];

                array_push($data,$tempData);

            }
            $batchBehavior = [
                'cs' => '解冻',
                'delete' => '删除'
            ];
            DB::commit();
            return response()->json(['batchBehavior'=>$batchBehavior,'data'=>$data,'stopNum'=>$stopNum,'manstopProportion'=>$manstopProportion,'womenstopProportion'=>$womenstopProportion,'todayNewstopNum'=>$todayNewstopNum,'todayNewstopManNum'=>$todayNewstopManNum,'todayNewstopWomenNum'=>$todayNewstopWomenNum],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

}
