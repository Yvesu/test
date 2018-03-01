<?php

namespace App\Http\Controllers\NewWeb\User;

use App\Models\Address\AddressCountry;
use App\Models\Address\AddressCounty;
use App\Models\Filmfest\Application;
use App\Models\Filmfest\JoinUniversity;
use App\Models\FilmfestFilmType;
use App\Models\Filmfests;
use App\Models\FilmfestUser\FilmfestUserPermission;
use App\Models\FilmfestUser\FilmfestUserRole;
use App\Models\FilmfestUser\FilmfestUserRoleGroup;
use App\Models\JoinVideo;
use App\Models\JoinVideoTweet;
use App\Models\TweetProduction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FilmfestUserController extends Controller
{
    //
    private $paginate = 10;

    public function rolePower(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_id = $request->get('role_id');
            $user = \Auth::guard('api')->user()->id;
            $role_id = $request->get('role_id',null);
            $type = $request->get('type',0);
            $searchKey = $request->get('key',null);
            $searchCountry = $request->get('country',null);
            $searchLanguage = $request->get('language',null);
            $searchDuration = $request->get('duration',0);
            $searchStatus = $request->get('status',0);
            $searchSchool = $request->get('school',null);
            $page = $request->get('page',1);
            $order = $request->get('order',null);
            $by = $request->get('by',null);
            $time = $request->get('time');
            if(is_null($time)){
                $startTime = 0;
                $endTime = null;
            }else{
                $startTime = strtotime(explode('-',$time)[0]);
                $endTime = strtotime(explode('-',$time)[1]);
            }
            $role = FilmfestUserRole::find($role_id);
                if((int)$role->status===0){
                    return response()->json(['message'=>'此功能未对您开放'],200);
                }
                $role = $role->des;
                if(strstr($role,'初审')||strstr($role,'发起')){
                    //  当前作品
                    $allProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.status','!=',2);
                    })->orderBy('id')->get()->count();
                    //  通过
                    $adoptProduction = TweetProduction::select('id')
                        ->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                            $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                                ->where('filmfests_tweet_production.again_select_status','!=',0);
                        })->orderBy('id')->get()->count();
                    //  待定
                    $waitProduction = TweetProduction::select('id')
                        ->whereHas('filmfestProduction',function ($q)use($filmfest_id){
                            $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                                ->where('filmfests_tweet_production.status','>=',3);
                        })->orderBy('id')->get()->count();
                    //  淘汰
                    $passProduction = TweetProduction::select('id')
                        ->whereHas('filmfestProduction',function ($q)use($filmfest_id){
                            $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                                ->where('filmfests_tweet_production.status',0);
                        })->orderBy('id')->get()->count();
                    $mainData = Application::where('filmfests_id',$filmfest_id)->where('is_over',1)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->SelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->offset(($page-1)*($this->paginate))->limit($this->paginate)->get();
                    $mainDataNum = Application::select('id')->where('is_over',1)->where('filmfests_id',$filmfest_id)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->SelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->get()->count();
                    $pageNum = ceil($mainDataNum/($this->paginate));
                }elseif(strstr($role,'复审')||strstr($role,'发起')){
                    //  当前作品
                    $allProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.again_select_status','!=',0);
                    })->orderBy('id')->get()->count();
                    //  通过
                    $adoptProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.join_select_status','=',2);
                    })->orderBy('id')->get()->count();
                    //  待定
                    $waitProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.again_select_status','=',1);
                    })->orderBy('id')->get()->count();
                    //  淘汰
                    $passProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.again_select_status','=',2)
                            ->where('filmfests_tweet_production.join_select_status','=',0);
                    })->orderBy('id')->get()->count();
                    $mainData = Application::where('filmfests_id',$filmfest_id)->where('is_over',1)->where('is_over',1)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->AgainSelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->offset(($page-1)*($this->paginate))->limit($this->paginate)->get();
                    $mainDataNum = Application::select('id')->where('is_over',1)->where('filmfests_id',$filmfest_id)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->AgainSelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->get()->count();
                    $pageNum = ceil($mainDataNum/($this->paginate));
                }elseif (strstr($role,'入围')||strstr($role,'发起')){
                    //  当前作品
                    $allProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.join_select_status','!=',0);
                    })->orderBy('id')->get()->count();
                    //  已审
                    $adoptProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.join_select_status','=',2);
                    })->orderBy('id')->get()->count();
                    //  待定
                    $waitProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.join_select_status','=',1);
                    })->orderBy('id')->get()->count();
                    //  淘汰
                    $passProduction = '';
                    $mainData = Application::where('filmfests_id',$filmfest_id)->where('is_over',1)->where('is_over',1)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->JoinSelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->offset(($page-1)*($this->paginate))->limit($this->paginate)->get();
                    $mainDataNum = Application::select('id')->where('is_over',1)->where('filmfests_id',$filmfest_id)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->JoinSelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->get()->count();
                    $pageNum = ceil($mainDataNum/($this->paginate));
                }elseif (strstr($role,'专业')||strstr($role,'发起')){
                    //  全部
                    $allProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.perofessuibal_select_status','!=',0);
                    })->orderBy('id')->get()->count();
                    //  已审
                    $adoptProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.perofessuibal_select_status','=',2);
                    })->orderBy('id')->get()->count();
                    //  待定
                    $waitProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.perofessuibal_select_status','=',1);
                    })->orderBy('id')->get()->count();
                    //  淘汰
                    $passProduction = '';
                    $mainData = Application::where('filmfests_id',$filmfest_id)->where('is_over',1)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->PerofessuibalSelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->offset(($page-1)*($this->paginate))->limit($this->paginate)->get();
                    $mainDataNum = Application::select('id')->where('is_over',1)->where('filmfests_id',$filmfest_id)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->PerofessuibalSelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->get()->count();
                    $pageNum = ceil($mainDataNum/($this->paginate));
                }elseif (strstr($role,'获奖')||strstr($role,'发起')){
                    //  获奖
                    $allProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.status',4)
                            ->where('filmfests_tweet_production.again_select_status',2)
                            ->where('filmfests_tweet_production.join_select_status',2)
                            ->where('filmfests_tweet_production.perofessuibal_select_status',2)
                            ->where('filmfests_tweet_production.is_win','!=',1);
                    })->orderBy('id')->get()->count();
                    //  未获奖
                    $passProduction = TweetProduction::select('id')->whereHas('filmfestProduction',function ($q) use($filmfest_id){
                        $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                            ->where('filmfests_tweet_production.status',4)
                            ->where('filmfests_tweet_production.again_select_status',2)
                            ->where('filmfests_tweet_production.join_select_status',2)
                            ->where('filmfests_tweet_production.perofessuibal_select_status',2)
                            ->where('filmfests_tweet_production.is_win','!=',0);
                    })->orderBy('id')->get()->count();
                    $waitProduction = '';
                    $adoptProduction = '';
                    $mainData = Application::where('filmfests_id',$filmfest_id)->where('is_over',1)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->WinSelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->offset(($page-1)*($this->paginate))->limit($this->paginate)->get();
                    $mainDataNum = Application::select('id')->where('is_over',1)->where('filmfests_id',$filmfest_id)->Type($type,$filmfest_id)->Key($searchKey)->Country($searchCountry)->Duration($searchDuration)
                        ->WinSelectStatus($searchStatus,$filmfest_id)->School($searchSchool)->Order($order,$by)->RangeTime($startTime,$endTime)->get()->count();
                    $pageNum = ceil($mainDataNum/($this->paginate));
                }elseif (strstr($role,'日志')||strstr($role,'发起')){
                    $mainData = TweetProduction::get();
                }else{
                    return response()->json(['message'=>'Hey! Brother,Are you kidding me?']);
                }
                if($mainData->count()>0){
                    $data = [];
                    foreach ($mainData as $k => $v)
                    {
                        $des = $v->production_des;
                        $name = $v->name;
                        $number = $v->number;
                        $poster = $v->production()->first()->poster?$v->production->poster:$v->production()->first()->tweet()->first()->screen_shot;
                        if((int)($v->productionTweet->status) === 4){
                            if((int)($v->productionTweet->again_select_status) === 1){
                                $status = '通过';
                            }elseif ($v->productionTweet->again_select_status == 2){
                                $status = '通过';
//                                if((int)($v->productionTweet->join_select_status === 1)){
//                                    $status = '待审';
//                                }elseif ($v->productionTweet->join_select_status == 2){
//                                    if((int)($v->productionTweet->perofessuibal_select_status) === 1){
//                                        $status = '待审';
//                                    }elseif ($v->productionTweet->perofessuibal_select_status == 2){
//                                        if((int)($v->productionTweet->is_win === 1)){
//                                            $status = '获奖';
//                                        }else{
//                                            $status = '已评审';
//                                        }
//                                    }else{
//                                        $status = '未进入';
//                                    }
//                                }else{
//                                    $status = '未进入';
//                                }
                            }elseif ($v->productionTweet->again_select_status == 2 && $v->productionTweet->join_select_status == 0){
                                $statusDes = '通过';
                                $status = 4;
                            }else{
                                $statusDes = '待审';
                                $status = 1;
                            }
                        }elseif((int)($v->productionTweet->status) === 3){
                            $statusDes = '待审';
                            $status = 1;
                        }elseif((int)($v->productionTweet->status) === 0){
                            $statusDes = '淘汰';
                            $status = 3;
                        }else{
                            $status = '未参赛';
                        }
                        if($v->productionTweet->videoStatus === 1){
                            $statusDes = '处理中';
                            $status = 2;
                        }elseif ($v->productionTweet->videoStatus === 2){
                            $statusDes = '处理失败';
                            $status = 0;
                        }
                        $application_id = $v->id;
                        $production_id = $v->production()->first()->id;
                        $tempData = [
                            'des'=>$des,
                            'name'=>$name,
                            'number'=>$number,
                            'poster'=>'http://img.cdn.hivideo.com/'.$poster,
                            'status'=>$status,
                            'status_des'=>$statusDes,
                            'application_id'=>$application_id,
                            'production_id'=>$production_id,
                        ];
                        array_push($data,$tempData);
                    }
                    if($type == 999){
                        return response()->json(['filmfest_id'=>$filmfest_id,'role_id'=>$role_id,'message'=>'暂无作品！','top'=>[['label'=>'当前作品','num'=>$allProduction],['label'=>'淘汰','num'=>$passProduction],['label'=>'待定','num'=>$waitProduction],['label'=>'通过','num'=>$adoptProduction]],'pageNum'=>$pageNum,'sumNum'=>$mainDataNum],200);
                    }
                    return response()->json(['filmfest_id'=>$filmfest_id,'role_id'=>$role_id,'data'=>$data,'top'=>[['label'=>'当前作品','num'=>$allProduction],['label'=>'淘汰','num'=>$passProduction],['label'=>'待定','num'=>$waitProduction],['label'=>'通过','num'=>$adoptProduction]],'pageNum'=>$pageNum,'sumNum'=>$mainDataNum]);
                }else{
                    return response()->json(['filmfest_id'=>$filmfest_id,'role_id'=>$role_id,'message'=>'暂无作品！','top'=>[['label'=>'当前作品','num'=>$allProduction],['label'=>'淘汰','num'=>$passProduction],['label'=>'待定','num'=>$waitProduction],['label'=>'通过','num'=>$adoptProduction]],'pageNum'=>$pageNum],200);
                }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 审片详情
     */
    public function detail(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_id = $request->get('role_id');
            $application_id = $request->get('application_id');
            $production_id = $request->get('production_id');
            if(is_null($application_id) || is_null($production_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $applicationDetail = Application::find($application_id);
            $number = $applicationDetail->number;
            $name1 = $applicationDetail->name;
            $production = TweetProduction::where('id',$production_id)->first();
            if($production->filmfestFilmType()->first()){
                $units = [];
                foreach ($production->filmfestFilmType as $k => $v)
                {
                    $name = $v->name;
                    array_push($units,['name'=>$name]);
                }
            }else{
                $units = '';
            }
            $tweet = $production->tweet()->first();
            $screen_shot = $tweet->screen_shot;
            $data = [
                'filmfest_id'=>$filmfest_id,
                'role_id'=>$role_id,
                'number'=>$number,
                'name'=>$name1,
                'units'=>$units,
                'screen_shot'=>'http://'.$screen_shot
            ];
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function detailClips(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_id = $request->get('role_id');
            $application_id = $request->get('application_id');
            $production_id = $request->get('production_id');
            if(is_null($application_id) || is_null($production_id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $production = TweetProduction::where('id',$production_id)->first();
            $data = [
                [
                    'name'=>'原始',
                    'url'=>'http://'.$production->movie_clios_video_m3u8,
                ],
                [
                    'name'=>'流畅',
                    'url'=>'http://'.$production->movie_clios_transcoding,
                ],
            ];
            $duration = floor(($production->movie_clips_duration)/60).':'.($production->movie_clips_duration)%60;
            $screen_shot = 'http://img.cdn.hivideo.com/'.$production->movie_clips_screen_shot;
            return response()->json(['data'=>$data,'duration'=>$duration,'screen_shot'=>$screen_shot],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function detailDownload(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_id = $request->get('role_id');
            $user = \Auth::guard('api')->user()->id;
            $production_id = $request->get('production_id');
            $is_download = FilmfestUserPermission::where('permission_name','like','%下载%')
                ->whereHas('role',function ($q) use($role_id){
                    $q->where('filmfest_user_role.id',$role_id);
                })->first();
            if($is_download){
                $is_private = TweetProduction::find($production_id)->tweet()->first()->is_download;
                if($is_private == 1){
                    $status = 1;
                }else{
                    $status = 0;
                }

            }else{
                $status = 0;
            }
            return response()->json(['data'=>$status],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }




    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 审片视频地址
     */
    public function video(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_id = $request->get('role_id');
            $user = \Auth::guard('api')->user()->id;
            $production_id = $request->get('production_id');
            $production = TweetProduction::where('id',$production_id)
                ->whereHas('filmfest',function ($q) use($filmfest_id){
                    $q->where('filmfests_id',$filmfest_id);
            })->first();
            $type = $request->get('type',1);
            if($production){
                $tweet = $production->tweet()->first();
                $filmfest = Filmfests::find($filmfest_id);
//                $tweet_id = $tweet->id;
//                $activity_id = $filmfest->active_id;
//                $join_video = JoinVideo::where('activity_id',$activity_id)->first();
//                $join_video_id = $join_video->id;
//                $video = JoinVideoTweet::where('tweet_id',$tweet_id)->where('join_video_id',$join_video_id)->first();
                $duration = $tweet->duration;
//                $duration = $tweet->duration + $join_video->duration;
                $title = $filmfest->titles_of_film;
                $resolution_ratio = [];
                if($tweet->high_video) {
                    $high_video = [
                        'label'=>2,
                        'name'=>'高清',
                        'url'=>'http://'.$tweet->high_video,
                        'title'=>'http://'.$title,
//                                'url'=>'http://video.ects.cdn.hivideo.com/11.mp4',
//                                'title'=>'http://video.ects.cdn.hivideo.com/&1&&11.mp4',
                        'duration'=>floor($duration/60).':'.$duration%60,
                    ];
                    array_push($resolution_ratio,$high_video);
                }
                if($tweet->norm_video) {
                    $norm_video = [
                        'label'=>3,
                        'name'=>'标准',
                        'url'=>'http://'.$tweet->norm_video,
                        'title'=>'http://'.$title,
//                                'url'=>'http://video.ects.cdn.hivideo.com/11.mp4',
//                                'title'=>'http://video.ects.cdn.hivideo.com/&1&&11.mp4',
                        'duration'=>floor($duration/60).':'.$duration%60,
                    ];
                    array_push($resolution_ratio, $norm_video);
                }

                if($tweet->video_m3u8){
                    $super_video = [
                        'label'=>4,
                        'name'=>'超清',
//                                'url'=>'http://'.$tweet->video,
                        'url'=>'http://'.$tweet->video_m3u8,
                        'title'=>'http://'.$title,
//                                'url'=>'http://video.ects.cdn.hivideo.com/11.mp4',
//                                'title'=>'http://video.ects.cdn.hivideo.com/&1&&11.mp4',
                        'duration'=>floor($duration/60).':'.$duration%60,
                    ];
                    array_push($resolution_ratio,$super_video);
                }

                if($tweet->transcoding_video) {
                    $adapt_video = [
                        'label' => 1,
                        'name' => '流畅',
                        'url' => 'http://' . $tweet->transcoding_video,
//                                'url'=>'http://video.ects.cdn.hivideo.com/11.mp4',
//                                'title'=>'http://video.ects.cdn.hivideo.com/&1&&11.mp4',
                        'title' => 'http://' . $title,
                        'duration' => floor($duration / 60) . ':' . $duration % 60,
                    ];
                    array_push($resolution_ratio, $adapt_video);
                }

                $video = [
                    'label'=>4,
                    'name'=>'原始',
                    'url'=>'http://'.$tweet->video,
                    'title'=>'http://'.$title,
                    'duration'=>floor($duration/60).':'.$duration%60,
                ];
                array_push($resolution_ratio,$video);


                $is_ok = User::where('id',$user)
                    ->whereHas('filmfest_role',function ($q) use($filmfest_id){
                        $q->Where([['filmfest_user_role.role_name','like','%发起%'],['filmfest_user_role.filmfest_id',$filmfest_id]]);
                    })->first();
                if($is_ok){
                    $role_id = 1;
                }
                if($type == 2) {
                    $is_download = FilmfestUserRole::where('id',$role_id)->where('filmfest_id',$filmfest_id)
                        ->whereHas('permission',function ($q){
                            $q->where('filmfest_user_permission.permission_name','like','%下载%')->where('filmfest_user_permission.status',1);
                        })->first();
                    if($is_download){
                        array_push($resolution_ratio, ['label'=>5,'name'=>'原始视频','url'=>'http://'.$tweet->video]);
                    }else{
                        return response()->json(['message'=>'权限不够']);
                    }

                }elseif((int)$type === 1){
                    $is_watch = FilmfestUserRole::where('id',$role_id)->where('filmfest_id',$filmfest_id)
                        ->whereHas('permission',function ($q){
                            $q->where('filmfest_user_permission.permission_name','like','%观看%');
                        })->first();
                    if(!$is_watch){array_push($resolution_ratio, ['label'=>5,'name'=>'原始视频','url'=>'http://'.$tweet->video]);
                        return response()->json(['message'=>'权限不够']);
                    }
                }
                return response()->json(['data'=>$resolution_ratio],200);
            }else{
                return response()->json(['message'=>'Hey! Brother,Are you kidding me?']);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 详情页下部内容
     */
    public function bottom(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_id = $request->get('role_id');
            $production_id = $request->get('production_id');
            $application_id = $request->get('application_id');
            $production = TweetProduction::where('id',$production_id)
                ->whereHas('filmfest',function ($q) use($filmfest_id){
                    $q->where('filmfests_id',$filmfest_id);
                })->first();
            $type = $request->get('type',1);
            if($production){
                $poster = $production->poster?$production->poster:$production->tweet()->first()->screen_shot;
                $application =  Application::find($application_id);
                if((int)$type === 1){
                    $text = $application->production_des;
                }else{
                    $text = [
                        [
                            'des'=>'作者:',
                            'name'=>$application->creater_name,
                        ],
                        [
                            'des'=>'导演:',
                            'name'=>$application->director_name,
                        ],
                        [
                            'des'=>'摄影:',
                            'name'=>$application->photography_name,
                        ],
                        [
                            'des'=>'编剧:',
                            'name'=>$application->scriptwriter_name,
                        ],
                        [
                            'des'=>'剪辑:',
                            'name'=>$application->cutting_name,
                        ],
                        [
                            'des'=>'主演:',
                            'name'=>$application->hero_name.'、'.$application->heroine_name,
                        ],
                        [
                            'des'=>'指导老师:',
                            'name'=>$application->adviser_name,
                        ],

                    ];

                }
                $data = [
                    'poster'=>'http://img.cdn.hivideo.com/'.$poster,
                    'content'=>$text,
                ];
                return response()->json(['data'=>$data]);
            }else{
                return response()->json(['message'=>'Hey! Brother,Are you kidding me?']);
            }
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 操作
     */
    public function detailHandle(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_id = $request->get('role_id');
            $user = \Auth::guard('api')->user()->id;
            $role_group_id = $request->get('role_group_id');
//            $role = FilmfestUserRole::find($role_id);
//            $data = [];
//            if($role->permission()->first()){
//                foreach ($role->permission as $k => $v)
//                {
//                    array_push($data,['id'=>$v->id,'name'=>$v->permission_name]);
//                }
//            }
            $roleGroup = FilmfestUserRoleGroup::find($role_group_id);
            $examMethod = $roleGroup->exam_method;
            if($examMethod == 2){
                $data = [
                    [
                        'des'=>'通过',
                        'label'=>1
                    ],
                    [
                        'des'=>'淘汰',
                        'label'=>2
                    ],
                    'status'=>1
                ];
            }else{
                $data = [
                    [
                        'des'=>'评分',
                        'label'=>1
                    ],
                    'status'=>2
                ];
            }

            return response()->json(['data'=>$data]);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    public function applicationFrom(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
            $role_id = $request->get('role_id');
            $production_id = $request->get('production_id');
            $application_id = $request->get('application_id');
            $type = $request->get('type',1);
            $application = Application::find($application_id);
            $pdf  =  new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // 设置文档信息
            $pdf->SetCreator('嗨！视频');
            $pdf->SetAuthor('嗨！视频');
            $pdf->SetTitle('嗨！视频');
            $pdf->SetSubject('嗨！视频');
            $pdf->SetKeywords('TCPDF, PDF, PHP');

            // 设置页眉和页脚信息
            $pdf->SetHeaderData('hivideo.png', 30, 'www.HiVideo.com', '嗨！视频', [0, 64, 255], [0, 64, 128]);
            $pdf->setFooterData([0, 64, 0], [0, 64, 128]);

            // 设置页眉和页脚字体
            $pdf->setHeaderFont(['stsongstdlight', '', '12']);
            $pdf->setFooterFont(['helvetica', '', '8']);

            // 设置默认等宽字体
            $pdf->SetDefaultMonospacedFont('courier');

            // 设置间距
            $pdf->SetMargins(15, 15, 15);//页面间隔
            $pdf->SetHeaderMargin(5);//页眉top间隔
            $pdf->SetFooterMargin(10);//页脚bottom间隔

            // 设置分页
            $pdf->SetAutoPageBreak(false, 25);

            // 设置自动换页
            $pdf->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);

            // 设置图像比例因子
            $pdf->setImageScale(1.25);

            // 设置默认字体构造子集模式
            $pdf->setFontSubsetting(true);

            // 设置字体 stsongstdlight支持中文
//        $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFont('stsongstdlight', '', 8);

            // 添加一页
            $pdf->AddPage();
            $duration = floor(($application->duration)/60).'分'.(($application->duration)%60).'秒';
            $units = $application->filmType()->get();
            $unit = '';
            foreach($units as $k => $v)
            {
                $unit .= $v->name.'、';
            }
            $unit = rtrim($unit,'、');
            $is_orther_web = $application->is_orther_web === 1 ? '是':'否';
            $copyright = $application->copyright === 0 ? '完整版权归作者所有':'网站或其他合作方所有';
            $is_collective = $application->is_collective === 0 ?'否':'是';
            $create_collective_name = $application->create_collective_name ? $application->create_collective_name:'无';
            $create_time = date('Y/m/d',$application->create_start_time).' - '.date('Y/m/d',$application->create_end_time);
            $contact_ways = $application->contactWay()->get();
            $contact_way = '';
            foreach ($contact_ways as $k => $v)
            {
                $contact_way1 = str_replace('；','、',$v->contact_way);
                $contact_way .= $contact_way1.'、';
            }
            $contact_way = rtrim($contact_way,'、');
            $schoolAndMajor = $application->university_name.' '.$application->major;
            $communication_address = $application->communication_address_country.' '.$application->communication_address_province.' '.$application->communication_address_city.' '.$application->communication_address_county.' '.$application->communication_detail_address;
            $papers = 'http://img.cdn.hivideo.com/'.$application->papers;
            $pdf->Ln(5);//换行符
            $html = "
            <h2 align=\"center\">第25届北京大学生电影节报名表</h2>
            <p>作品编号:{$application->number}</p>
            <p>作品名称:{$application->name}<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$application->english_name}</p>
            <p>作品长度:{$duration}</p>
            <p>参与单元:{$unit}</p>
            <p>是否上传过其他视频网站、或获取过相关拍摄资助:{$is_orther_web}<br>{$copyright}</p>
            <p>是否集体作品:{$is_collective}</p>
            <p>集体创作人数:{$application->create_people_num}</p>
            <p>团队创作名称:{$create_collective_name}</p>
            <p>制作时间:{$create_time}</p>
            <p>作品简介:</p>
            <p>{$application->production_des}</p>
            <p>{$application->production_english_des}</p>
            <p>作者姓名:{$application->creater_name}</p>
            <p>导演姓名:{$application->director_name}</p>
            <p>编剧姓名:{$application->scriptwriter_name}</p>
            <p>摄影姓名:{$application->photography_name}</p>
            <p>剪辑姓名:{$application->cutting_name}</p>
            <p>男主角姓名:{$application->hero_name}</p>
            <p>女主角姓名:{$application->heroine_name}</p>
            <p>电话/电子邮件:{$contact_way}</p>
            <p>所属学校、专业:{$schoolAndMajor}</p>
            <p>指导老师:{$application->adviser_name}</p>
            <p>指导老师联系方式:{$application->adviser_phone}</p>
            <p>通讯地址:{$communication_address}</p>
            <p>作者简介:{$application->creater_des}</p>
            <p>其他作者简介及分工介绍:{$application->other_creater_des}</p>
            <img src=\"http://img.cdn.hivideo.com/{$application->papers}\" align=\"middle\" />
        ";
            $pdf->writeHTML($html, true, false, true, false, '');
            ob_end_clean();
            //输出PDF
            if($type===1){
                $pdf->Output('报名表.pdf', 'I');//I输出、D下载
            }else{
                $pdf->Output('报名表.pdf', 'D');//I输出、D下载
            }

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 作品类别
     */
    public function productionType(Request $request)
    {
        try{
            $filmfest_id = $request->get('id');
                $type = [
                    [
                        'label'=>0,
                        'des'=>'全部作品'
                    ]
                ];
                $data = FilmfestFilmType::whereHas('filmFests',function ($q)use($filmfest_id){
                    $q->where('filmfests.id',$filmfest_id);
                })->get();
                if($data->count()>0){
                    foreach ($data as $k => $v)
                    {
                        $id = $v->id;
                        $name = $v->name;
                        $tempData = [
                            'label'=>$id,
                            'des'=>$name,
                        ];
                        array_push($type,$tempData);
                    }
                    array_push($type,['label'=>999,'des'=>'Hivideo高分'],['label'=>1000,'des'=>'涉嫌情色及政治人物']);
                }

                return response()->json(['data'=>$type],200);

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 作品国家
     */
    public function productionCountry(Request $request)
    {
        try{


                $data = AddressCountry::get();
                $country =  [];
                if($data->count()>0){
                    foreach ($data as $k => $v)
                    {
                        $tempData = [
                            'label'=>$v->id,
                            'des'=>$v->Name,
                        ];
                        array_push($country,$tempData);
                    }
                }

            return response()->json(['data'=>$country]);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 作品时长
     */
    public function productionDuration(Request $request)
    {
        try{

                $data = [
                    [
                        'label'=>0,
                        'des'=>'全部',
                    ],
                    [
                        'label'=>1,
                        'des'=>'小于10分'
                    ],
                    [
                        'label'=>2,
                        'des'=>'小于30分'
                    ],
                    [
                        'label'=>3,
                        'des'=>'小于1小时'
                    ],
                    [
                        'label'=>4,
                        'des'=>'一小时以上'
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
     * 作品状态
     */
    public function productionStatus(Request $request)
    {
        try{
            $role_id = $request->get('role_id');
            $role = FilmfestUserRole::find($role_id)->des;
            if(strstr($role,'评')){
                $data = [
                    [
                        'label'=>0,
                        'des'=>'全部',
                    ],
                    [
                        'label'=>1,
                        'des'=>'已审'
                    ],
                    [
                        'label'=>2,
                        'des'=>'未审'
                    ]
                ];
            }elseif (strstr($role,'获')){
                $data = [
                    [
                        'label'=>0,
                        'des'=>'全部',
                    ],
                    [
                        'label'=>1,
                        'des'=>'已获奖'
                    ],
                    [
                        'label'=>2,
                        'des'=>'未获奖'
                    ]
                ];
            }
            else{
                $data = [
                    [
                        'label'=>0,
                        'des'=>'全部'
                    ],
                    [
                        'label'=>1,
                        'des'=>'通过'
                    ],
                    [
                        'label'=>2,
                        'des'=>'待审'
                    ],
                    [
                        'label'=>3,
                        'des'=>'淘汰'
                    ]

                ];
            }
                return response()->json(['data'=>$data],200);

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 作品学校
     */
    public function productionSchool(Request $request)
    {
        try{
                $school = [
                    [
                        'label'=>0,
                        'des'=>'全部',
                    ]
                ];
                $data = JoinUniversity::get();
                if($data->count()>0){
                    foreach ($data as $k => $v)
                    {
                        $tempData = [
                            'label'=>$v->id,
                            'des'=>$v->name,
                        ];
                        array_push($school,$tempData);
                    }
                }
                return response()->json(['data'=>$school],200);


        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 作品排序
     */
    public function productionOrder(Request $request)
    {
        try{
                $data = [
                    [
                        'label'=>'number',
                        'des'=>'编号顺序',
                        'by'=>'asc'
                    ],
                    [
                        'label'=>'number',
                        'des'=>'编号倒序',
                        'by'=>'desc'
                    ],
                    [
                        'label'=>'duration',
                        'des'=>'时长顺序',
                        'by'=>'asc'
                    ],
                    [
                        'label'=>'duration',
                        'des'=>'时长倒序',
                        'by'=>'desc'
                    ],
                    [
                        'label'=>2,
                        'des'=>'兼报多单元',
                        'by'=>'desc'
                    ],
                ];
                return response()->json(['data'=>$data]);

        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }

}
