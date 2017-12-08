<?php

namespace App\Http\Controllers\NewAdmin\Template;

use Auth;
use CloudStorage;
use App\Models\Make\MakeTemplateFile;
use App\Models\Make\MakeTemplateFolder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{

    private $paginate = 20;
    private $protocol = 'http://';

    //

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 模板-全部页面
     */
    public function index(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $type = $request->get('type_id',null);
            $operator = $request->get('operator_id',null);
            $time = $request->get('time',0);
            $duration = $request->get('duration',0);
            $count = $request->get('count',0);
            $page = $request->get('page',1);
            DB::beginTransaction();
            $mainData = $this->mainData($page,$name,$type,$operator,$time,$duration,$count);
            $data = $this->finallyData($mainData,1);
            DB::commit();
            return $data;
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found']);
        }


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 模板-推荐页
     */
    public function recommend(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $type = $request->get('type_id',null);
            $operator = $request->get('operator_id',null);
            $time = $request->get('time',0);
            $duration = $request->get('duration',0);
            $count = $request->get('count',0);
            $page = $request->get('page',1);
            $recommend=1;
            DB::beginTransaction();
            $mainData = $this->mainData($page,$name,$type,$operator,$time,$duration,$count,$recommend);
            $data = $this->finallyData($mainData,2);
            DB::commit();
            return $data;
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 热门页
     */
    public function hot(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $type = $request->get('type_id',null);
            $operator = $request->get('operator_id',null);
            $time = $request->get('time',0);
            $duration = $request->get('duration',0);
            $count = $request->get('count',0);
            $page = $request->get('page',1);
            $recommend=null;
            $ishot = 1;
            DB::beginTransaction();
            $mainData = $this->mainData($page,$name,$type,$operator,$time,$duration,$count,$recommend,$ishot);
            $data = $this->finallyData($mainData,3);
            DB::commit();
            return $data;
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 屏蔽仓
     */
    public function shield(Request $request)
    {
        try{
            $name = $request->get('name',null);
            $type = $request->get('type_id',null);
            $operator = $request->get('operator_id',null);
            $time = $request->get('time',0);
            $duration = $request->get('duration',0);
            $count = $request->get('count',0);
            $page = $request->get('page',1);
            $recommend=null;
            $ishot = null;
            $status = 2;
            DB::beginTransaction();
            $mainData = $this->mainData($page,$name,$type,$operator,$time,$duration,$count,$recommend,$ishot,$status);
            $data = $this->finallyData($mainData,4);
            DB::commit();
            return $data;
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found']);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 分类
     */
    public function type(Request $request)
    {
        try{
            $active = $request->get('active',1);
            DB::beginTransaction();
            $mainData = MakeTemplateFolder::where('active','=',$active)->orderBy('sort')->get();
            $data = [];
            $space = MakeTemplateFile::get()->sum('size');
            $max = $mainData->max('sort');
            $min = $mainData->min('sort');
            $num = $mainData->count();
            foreach($mainData as $k => $v)
            {
                $cover = $this->protocol.$v->cover;
                $time = $v->time_add;
                $creater = $v->belongsToAdministrator->name;
                $count = $v->count;
                $downloadNum = $v->hasManyFiles->count();
                $usageSpace = (int)$v->hasManyFiles->sum('size');
                $usageSpaceProportion = (round($usageSpace/$space,2)*100).'%';
                if($active == 1){
                    if($v->sort == $min && $v->sort == $max){
                        $behavior = [
                            'stop'=>'停用'
                        ];
                    }elseif ($v->sort == $min){
                        $behavior = [
                            'down'=>'向下',
                            'stop'=>'停用'
                        ];
                    }elseif($v->sort == $max){
                        $behavior = [
                            'up'=>'向上',
                            'stop'=>'停用'
                        ];
                    }else{
                        $behavior = [
                            'up' => '向上',
                            'down'=> '向下',
                            'stop'=> '停用',
                        ];
                    }
                    $batchBehavior = [
                        'stop' => '停用',
                    ];
                }else{
                    if($v->sort == $min && $v->sort == $max){
                        $behavior = [
                            'stop'=>'启用'
                        ];
                    }elseif ($v->sort == $min){
                        $behavior = [
                            'down'=>'向下',
                            'stop'=>'启用'
                        ];
                    }elseif($v->sort == $max){
                        $behavior = [
                            'up'=>'向上',
                            'stop'=>'启用'
                        ];
                    }else{
                        $behavior = [
                            'up' => '向上',
                            'down'=> '向下',
                            'stop'=> '启用',
                        ];
                    }
                    $batchBehavior = [
                        'stop'=> '启用',
                    ];
                }


                $tempData = [
                    'cover' => $cover,
                    'time' => $time,
                    'creater' => $creater,
                    'count' => $count,
                    'downloadNum' => $downloadNum,
                    'usageSpaceAndProportion' => $usageSpace.'/'.$usageSpaceProportion,
                    'behavior' => $behavior
                ];

                array_push($data,$tempData);

            }

            DB::commit();
            return response()->json(['data'=>$data,'num'=>$num,'batchBehavior'=>$batchBehavior]);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加分类
     */
    public function addType(Request $request)
    {
        try{
            $keys1 = [];
            $admin = Auth::guard('api')->user();
            $creater = $admin->id;
            $name = $request->get('name');
            $cover = $request->get('cover');
            $active = $request->get('active',0);
            if(is_null($name) || is_null($cover)){
                return response()->json(['error'=>'不能添加空值'],200);
            }

            // 判断分类是否存在，如果存在，返回并将信息存至session中
            if(MakeTemplateFolder::where('name',$name)->first()){
                return response()->json(['error'=>'已存在'],200);
            }
            DB::beginTransaction();
            $data = new MakeTemplateFolder;
            if($active == 1)
            {
                $sort =1 + MakeTemplateFolder::max('sort');
                $data ->sort = $sort;
            }
            $data->name = $name;
            $data->cover = $this->protocol.'img.cdn.hivideo.com/'.$cover;
            $data->active = $active;
            $data->create_id =$creater;
            $data->time_add = time();
            $data->time_update = time();
            $data->save();
            array_push($keys1,$cover);
            $keyPairs1 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            $srcbucket1 = 'hivideo-img-ects';
            $destbucket1 = 'hivideo-img';
            $message = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            DB::commit();
            return response()->json(['data'=>'添加成功',$message],200);
        }catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * @param $page
     * @param null $name
     * @param null $type
     * @param null $operator
     * @param int $time
     * @param int $duration
     * @param int $count
     * @param null $recommend
     * @param null $ishot
     * @param null $status
     * @return mixed
     * 产生主要数据
     */
    private function mainData($page,$name=null,$type=null,$operator=null,$time=0,$duration=0,$count=0,$recommend=null,$ishot=null,$status=null)
    {
        switch ($time){
            case 0:
                $time = 0;
                break;
            case 1:
                $time = strtotime(date('Y-m-d',time()));
                break;
            case 2:
                $time = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                break;
            case 3:
                $time = mktime(0,0,0,date('m'),1,date('Y'));
                break;
            default:
                $time = 0;
        }
        // 播放时长变形
        switch ($duration){
            case 0:
                $duration = 0;
                break;
            case 1:
                $duration = 10*60;
                break;
            case 2:
                $duration = 30*60;
                break;
            case 3:
                $duration = 60*60;
                break;
            default:
                $duration = 0;
        }

        $allData = MakeTemplateFile::where('active','=',1)->where('test_result','=',1)->Name($name)->Type($type)->Operator($operator,$status,$recommend,$ishot)
            ->Time($time)->Duration($duration)->Counta($count);
        if($recommend == 1){
            $mainData = $allData->where('recommend','=',1)->forPage($page,$this->paginate)->get();
        } elseif($status == 2) {
            $mainData = $allData->where('status', '=', 2)->forPage($page, $this->paginate)->get();
        } elseif($ishot == 1){
            $mainData = $allData->where('ishot','=',1)->forPage($page,$this->paginate)->get();
        } else{
            $mainData = $allData->where('status','=',1)->forPage($page,$this->paginate)->get();
        }
        return $mainData;
    }


    /**
     * @param $mainData
     * @param $classify
     * @return \Illuminate\Http\JsonResponse
     * 生成最终的数据
     */
    private function finallyData($mainData,$classify)
    {
        $data = [];
        if($classify == 1){
            $batchBehavior = [
                'dotype' => '分类',
                'recommend'=>'推荐',
                'cancelrecommend'=>'取消推荐',
                'hot'=>'热门',
                'cancelhot'=>'取消热门',
                'dosheild'=>'屏蔽'
            ];
        }elseif ($classify ==2){
            $batchBehavior = [
                'dotype' => '分类',
                'cancelrecommend'=>'取消推荐',
                'hot'=>'热门',
                'cancelhot'=>'取消热门',
                'dosheild'=>'屏蔽'
            ];
        }elseif ($classify ==3){
            $batchBehavior = [
                'dotype' => '分类',
                'recommend'=>'推荐',
                'cancelrecommend'=>'取消推荐',
                'cancelhot'=>'取消热门',
                'dosheild'=>'屏蔽'
            ];
        }elseif ($classify ==4){
            $batchBehavior = [
                'cancelshield'=>'取消屏蔽',
                'delete'=>'删除',
            ];
        }
        foreach ($mainData as $k=>$v)
        {
            $folder = $v->belongsToFolder()->first() ? $v->belongsToFolder()->first()->name:null;
            $userName = $v->belongsToUser()->first() ? $v->belongsToUser()->first()->nickname:null;
            $cover = $this->protocol.($v->cover);
            $intro = $v->intro;
            $duration = (($v->duration)/60).':'.(($v->duration)%60);
            $time_add = date('Y-m-d H:i:s',$v->time_add);
            $count = $v->count;
            $integral = $v->integral;
            if($classify == 1){                 //  全部页
                if($v->checker_id){
                    if($v->dotype_id){
                        if($v->ishot_id){
                            if($v->dorecommend_id){
                                $people = $v->belongsToChecker->name.','.$v->belongsToType->name.','.$v->belongsToHot->name.','.$v->belongsToRecommender->name;
                            }else{
                                $people = $v->belongsToChecker->name.','.$v->belongsToType->name.','.$v->belongsToHot->name;
                            }
                        }else{
                            if($v->dorecommend_id){
                                $people = $v->belongsToChecker->name.','.$v->belongsToType->name.','.$v->belongsToRecommender->name;
                            }else{
                                $people = $v->belongsToChecker->name.','.$v->belongsToType->name;
                            }
                        }
                    }else{
                        if($v->ishot_id){
                            if($v->dorecommend_id){
                                $people = $v->belongsToChecker->name.','.$v->belongsToHot->name.','.$v->belongsToRecommender->name;
                            }else{
                                $people = $v->belongsToChecker->name.','.$v->belongsToHot->name;
                            }
                        }else{
                            if($v->dorecommend_id){
                                $people = $v->belongsToChecker->name.','.$v->belongsToRecommender->name;
                            }else{
                                $people = $v->belongsToChecker->name;
                            }
                        }
                    }
                }else{
                    $people = '';
                }
                if($v->recommend == 0){
                   if($v->ishot == 0){
                       $behavior = [
                           'dotype' => '分类',
                           'recommend'=>'推荐',
                           'hot'=>'热门',
                           'dosheild'=>'屏蔽'
                       ];
                   }else{
                       $behavior = [
                           'dotype' => '分类',
                           'recommend'=>'推荐',
                           'cancelhot'=>'取消热门',
                           'dosheild'=>'屏蔽'
                       ];
                   }
                }else{
                    if($v->ishot == 0){
                        $behavior = [
                            'dotype' => '分类',
                            'cancelrecommend'=>'取消推荐',
                            'hot'=>'热门',
                            'dosheild'=>'屏蔽'
                        ];
                    }else{
                        $behavior = [
                            'dotype' => '分类',
                            'cancelrecommend'=>'取消推荐',
                            'cancelhot'=>'取消热门',
                            'dosheild'=>'屏蔽'
                        ];
                    }
                }



            }elseif($classify == 2){            //  推荐页
                if($v->dorecommend_id){
                    $people = $v->belongsToRecommender->name;
                }else{
                    $people = '';
                }

                if($v->ishot == 0){
                    $behavior = [
                        'dotype' => '分类',
                        'cancelrecommend'=>'取消推荐',
                        'hot'=>'热门',
                        'dosheild'=>'屏蔽'
                    ];
                }else{
                    $behavior = [
                        'dotype' => '分类',
                        'cancelrecommend'=>'取消推荐',
                        'cancelhot'=>'取消热门',
                        'dosheild'=>'屏蔽'
                    ];
                }



            }elseif($classify == 3){            //  热门页
                if($v->ishot_id){
                    $people = $v->belongsToHot->name;
                }else{
                    $people = '';
                }

                if($v->recommend == 0){

                    $behavior = [
                        'dotype' => '分类',
                        'recommend'=>'推荐',
                        'cancelhot'=>'取消热门',
                        'dosheild'=>'屏蔽'
                    ];

                }else{

                    $behavior = [
                        'dotype' => '分类',
                        'cancelrecommend'=>'取消推荐',
                        'cancelhot'=>'取消热门',
                        'dosheild'=>'屏蔽'
                    ];

                }



            }elseif($classify == 4){            //  屏蔽页
                if($v->dosheild_id){
                    $people = $v->belongsToShield->name;
                }else{
                    $people = '';
                }

                $behavior = [
                    'cancelshield'=>'取消屏蔽',
                    'delete'=>'删除',
                ];


            }

            $tempData = [
                'type'=>$folder,
                'Name'=>$userName,
                'cover'=>$cover,
                'intro' => $intro,
                'duration' => $duration,
                'time_add' => $time_add,
                'count' => $count,
                'integral' => $integral,
                'operator' => $people,
                'behavior' => $behavior,


            ];

            array_push($data,$tempData);

        }
        $sumnum = MakeTemplateFile::where('active','=',1)->where('test_result','=',1)->get()->count();
        $todaynew = MakeTemplateFile::where('active','=',1)->where('test_result','=',1)->where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();
        return response()->json(['data'=>$data,'batchBehavior'=>$batchBehavior,'sumnum'=>$sumnum,'todaynew'=>$todaynew]);
    }

}
