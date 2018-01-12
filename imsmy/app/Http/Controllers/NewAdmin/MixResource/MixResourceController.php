<?php

namespace App\Http\Controllers\NewAdmin\MixResource;

use App\Models\Make\MakeEffectsFolder;
use CloudStorage;
use App\Models\Admin\Administrator;
use App\Models\KeywordEffects;
use App\Models\Keywords;
use App\Models\Make\MakeEffectsFile;
use App\Models\Make\MakeEffectsFileTemporary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\DB;

class MixResourceController extends Controller
{

    private $protocol = 'http://';

    private $paginate = 20;
    //

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加发布信息页面
     */
    public function issue(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            DB::beginTransaction();
            $admin_info = Administrator::with('hasOneUser')->where('id',$admin->id)->firstOrFail(['user_id']);
            $user_id = $admin_info->user_id;
            $oldData = MakeEffectsFileTemporary::where('user_id','=',$user_id)->first();
            if($oldData){
                $data = [
                    'id'=>$oldData->id,
                    'user_id'=>$oldData->user_id,
                    'name'=>$oldData->name,
                    'address'=>$oldData->address?$this->protocol.'video.ects.cdn.hivideo.com/'.$oldData->address:null,
                    'cover'=>$oldData->cover?$this->protocol.'img.ects.cdn.hivideo.com/'.$oldData->cover:null,
                    'folder_id'=>$oldData->folder_id,
                    'duration'=>floor(($oldData->duration)/60).':'.(($oldData->duration)%60),
                    'size'=>$oldData->size,
                    'integral'=>$oldData->integral,
                    'vipfree'=>$oldData->vipfree,
                    'isalpha'=>$oldData->isalpha,
                    'mix_texture' => $oldData->mix_texture_id?$oldData->belongsToMixTexture->name:'',
                    'distinguishability_x'=>$oldData->distinguishability_x,
                    'distinguishability_y'=>$oldData->distinguishability_y,
                    'preview_address'=>$oldData->preview_address?$this->protocol.'viedo.ects.cdn.hivideo.com/'.$oldData->preview_address:null,
                ];


                if($oldData->keyWord()->first()){
                    foreach( $oldData->keyWord as $k => $keyWord)
                    {
                        $data['keyword']['keyword'.$k]=$keyWord->keyword;
                    }
                }

            }else{
                $newData = new MakeEffectsFileTemporary;
                $newData->user_id = $user_id;
                $newData->save();
                $data = [
                    'id'=>$newData->id,
                    'user_id'=>$newData->user_id,
                    'duration'=>'00:00',
                ];
            }
            DB::commit();
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 发布预览页面
     */
    public function issuePlay(Request $request)
    {
        try{
            $id = $request->get('id',null);
            $name = $request->get('name',null);
            $integral = $request->get('integral',0);
            $folder = $request->get('type_id',null);
            $duration = $request->get('duration',null);
            $distinguishability_x = $request->get('distinguishability_x',null);
            $distinguishability_y = $request->get('distinguishability_y',null);
            $cover = $request->get('cover',null);
            $preview_address = $request->get('preview_address',null);
            $address = $request->get('address',null);
            $size = $request->get('size',null);
            $keywords = $request->get('keywords',null);
            $vipfree = $request->get('vipfree',1);
            $isalpha = $request->get('isalpha',1);
            $mix_texture = $request->get('mix_texture_id',null);
            if(is_null($name)||is_null($duration)||is_null($folder)||is_null($distinguishability_x)||is_null($distinguishability_y)||is_null($cover)||is_null($preview_address)||is_null($address)||is_null($size)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $duration = explode(':',$duration);
            $duration = ($duration[0]*60) + $duration[1];
            DB::beginTransaction();
            $effect = MakeEffectsFileTemporary::find($id);
            $effect->integral = $integral;
            $effect->name = $name;
            $effect->integral = $integral;
            $effect->folder_id = $folder;
            $effect->duration = $duration;
            $effect->distinguishability_x = $distinguishability_x;
            $effect->distinguishability_y = $distinguishability_y;
            $effect->cover = $cover;
            $effect->preview_address = $preview_address;
            $effect->address = $address;
            $effect->size = $size;
            $effect->mix_texture_id = $mix_texture;
            $effect->isalpha = $isalpha;
            $effect->vipfree = $vipfree;
            $effect->save();
            $label = '';
            if(!is_null($keywords)){
                $keywords = explode('|',$keywords);
                $keywords = array_unique($keywords);
                KeywordEffects::where('effectsTemporary_id','=',$effect->id)->delete();
                foreach($keywords as $k => $v)
                {
                    $keyword = Keywords::where('keyword',$v)->first();
                    if($keyword){
                        $keyword_id = $keyword->id;
                    }else{
                        $newkeyword = new Keywords;
                        $newkeyword ->keyword = $v;
                        $newkeyword ->create_at = time();
                        $newkeyword ->update_at = time();
                        $newkeyword ->save();
                        $keyword_id = $newkeyword->id;
                    }
                    $keywordFragment = new KeywordEffects;
                    $keywordFragment -> keyword_id = $keyword_id;
                    $keywordFragment -> effectsTemporary_id = $effect->id;
                    $keywordFragment -> time_add = time();
                    $keywordFragment -> time_update = time();
                    $keywordFragment ->save();
                    $label .= $v.',';
                }
                $label = rtrim($label,',');
            }

            $content = [
                'id' => $effect->id,
                'user_id' => $effect->user_id,
                'cover' => $this->protocol.'img.ects.cdn.hivideo.com/'.$effect->cover,
                'description' => $effect->name,
                'duration' => floor(($effect->duration)/60).':'.(($effect->duration)%60),
                'integral' => $effect->integral,
                'label' => $label,
                'play' => $this->protocol.'video.ects.cdn.hivideo.com/'.$effect->preview_address,
                'size' => round((($effect->size)/1024)/1024,2),
                'mix_texture' => $oldData->mix_texture_id?$oldData->belongsToMixTexture->name:'',
                'distinguishability'=>$effect->distinguishability_x.'*'.$effect->distinguishability_y,
                'type'=> $effect->belongsToFolder->name,
                'vipfree'=>($effect->vipfree)==1?'vip免费':'vip不免费',
            ];
            DB::commit();
            return response()->json(['data'=>$content],200);


        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 执行发布页面
     */
    public function doIssue(Request $request)
    {
        try{
            $keys1 = [];
            $keys2 = [];
            $keys3 = [];
            DB::beginTransaction();
            $id = $request->get('id');
            $data = MakeEffectsFileTemporary::find($id);
            $effect = new MakeEffectsFile;
            $effect->name = $data->name;
            $effect->user_id = $data->user_id;
            $effect->address = 'v.cdn.hivideo.com/'.$data->address;
            $effect->preview_address = 'v.cdn.hivideo.com/'.$data->preview_address;
            $effect->cover = 'img.cdn.hivideo.com/'.$data->cover;
            $effect->folder_id = $data->folder_id;
            $effect->resolution = $data->distinguishability_x.'*'.$data->distinguishability_y;
            $effect->duration = $data->duration;
            $effect->size = $data->size;
            $effect->mix_texture_id  = $data->mix_texture_id;
            $effect->integral = $data->integral;
            $effect->isalpha = $data->isalpha;
            $effect->time_add = time();
            $effect->time_update = time();
            $effect->vipfree = $data->vipfree;
            $effect->save();
            array_push($keys2,$data->preview_address);
            array_push($keys1,$data->cover);
            array_push($keys3,$data->address);
            $effect_id = $effect->id;
            KeywordEffects::where('effectsTemporary_id',$id)->update(['effects_id'=>$effect_id,'effectsTemporary_id'=>null]);
            $keyPairs1 = array();
            $keyPairs2 = array();
            $keyPairs3 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            foreach ($keys2 as $key)
            {
                $keyPairs2[$key] = $key;
            }
            foreach ($keys3 as $key)
            {
                $keyPairs3[$key] = $key;
            }

            $srcbucket1 = 'hivideo-video-ects';
            $srcbucket2 = 'hivideo-video-ects';
            $srcbucket3 = 'hivideo-img-ects';
            $destbucket1 = 'hivideo-img';
            $destbucket2 = 'hivideo-video';
            $destbucket3 = 'hivideo-video';
            $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket3,$destbucket1);
            $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket1,$destbucket2);
            $message3 = CloudStorage::copyfile($keyPairs3,$srcbucket2,$destbucket3);
            MakeEffectsFileTemporary::find($id)->delete();
            DB::commit();

            return response()->json(['message'=>'成功',$message1,$message2,$message3],200);
        }catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 清空
     */
    public function clear(Request $request)
    {
        try{
            $id = $request->get('id');
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            DB::beginTransaction();
            MakeEffectsFileTemporary::where('id',$id)->update(['name'=>null,'intro'=>null,'address'=>null,'cover'=>null,'folder_id'=>null,'duration'=>null,'size'=>0,'integral'=>0,'vipfree'=>1,'distinguishability_x'=>0,'distinguishability_y'=>0,'preview_address'=>null]);
            KeywordEffects::where('effectsTemporary_id',$id)->delete();
            DB::commit();
            return response()->json(['message'=>'清空成功']);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 取消发布
     */
    public function cancelIssue(Request $request)
    {
        try{
            $id = $request->get('id');
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            DB::beginTransaction();
            MakeEffectsFileTemporary::where('id',$id)->delete();
            KeywordEffects::where('effectsTemporary_id',$id)->delete();
            DB::commit();
            return response()->json(['message'=>'取消成功']);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 全部
     */
    public function index(Request $request)
    {
        try {
            $name = $request->get('name', null);
            $type = $request->get('type_id', null);
            $integral = $request->get('integral', 0);
            $time = $request->get('time', 0);
            $duration = $request->get('duration', 0);
            $count = $request->get('count', 0);
            $page = $request->get('page', 1);
            $everyPageNum = $request ->get('everypagenum',10);
            $active = 1;
            DB::beginTransaction();
            $mainData = $this->mainData($everyPageNum,$active,$page, $name, $type, $integral, $time, $duration, $count);
            $data = $this->finallyData($mainData, 1);
            DB::commit();
            return $data;
        } catch (ModelNotFoundException $q) {
            DB::rollBack();
            return response()->json(['error' => 'not_found']);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 推荐页
     */
    public function recommend(Request $request)
    {
        try {
            $name = $request->get('name', null);
            $type = $request->get('type_id', null);
            $integral = $request->get('integral', 0);
            $time = $request->get('time', 0);
            $duration = $request->get('duration', 0);
            $count = $request->get('count', 0);
            $page = $request->get('page', 1);
            $active = 1;
            $everyPageNum = $request ->get('everypagenum',10);
            $recommend  = 1;
            DB::beginTransaction();
            $mainData = $this->mainData($everyPageNum,$active,$page, $name, $type, $integral, $time, $duration, $count,$recommend);
            $data = $this->finallyData($mainData, 2);
            DB::commit();
            return $data;
        } catch (ModelNotFoundException $q) {
            DB::rollBack();
            return response()->json(['error' => 'not_found']);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 屏蔽仓
     */
    public function shield(Request $request)
    {
        try {
            $name = $request->get('name', null);
            $type = $request->get('type_id', null);
            $integral = $request->get('integral', 0);
            $time = $request->get('time', 0);
            $duration = $request->get('duration', 0);
            $count = $request->get('count', 0);
            $page = $request->get('page', 1);
            $active = 3;
            $everyPageNum = $request ->get('everypagenum',10);
            DB::beginTransaction();
            $mainData = $this->mainData($everyPageNum,$active,$page, $name, $type, $integral, $time, $duration, $count);
            $data = $this->finallyData($mainData, 3);
            DB::commit();
            return $data;
        } catch (ModelNotFoundException $q) {
            DB::rollBack();
            return response()->json(['error' => 'not_found']);
        }
    }


    private function mainData($everyPageNum,$active,$page,$name=null,$type=null,$integral=0,$time=0,$duration=0,$count=0,$recommend=null)
    {
        try{
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
            $allData = MakeEffectsFile::where('active','=',$active)
            ->where('test_result','=',1)->Name($name)->Type($type)->where('integral','>=',$integral)->Time($time)->Duration($duration)
            ->Counta($count);
            if($recommend == 1){
                $mainData = $allData->where('recommend','=',1)->forPage($page,$everyPageNum)->get();
                $dataNum = $allData->where('recommend','=',1)->get()->count();
            }else{
                $mainData = $allData->forPage($page,$everyPageNum)->get();
                $dataNum = $allData->get()->count();
            }
            $data = [
                $mainData,
                $dataNum
            ];
            return $data;
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    private function finallyData($mainData,$classify)
    {
        if($classify == 1){
            $batchBehavior = [
                'dotype' => '分类',
                'recommend'=>'推荐',
                'cancelrecommend'=>'取消推荐',
                'dosheild'=>'屏蔽'
            ];
        }elseif($classify == 2){
            $batchBehavior = [
                'dotype' => '分类',
                'cancelrecommend'=>'取消推荐',
                'dosheild'=>'屏蔽'
            ];
        }elseif ($classify == 3){
            $batchBehavior = [
                'cancelshield'=>'取消屏蔽',
                'delete'=>'删除',
            ];
        }

        $data = [];

        foreach($mainData[0] as $k => $v)
        {
            $folder = $v->belongsToFolder()->first()?$v->belongsToFolder->name:'';
            $userName = $v->belongsToUser->nickname;
            $cover = $this->protocol.$v->cover;
            $des = $v->name;
            $duration = (($v->duration)/60).':'.(($v->duration)%60);
            $time = date('Y-m-d H:i:s',$v->time_add);
            $count = $v->count;
            $integral = $v->integral;
            if($classify == 1){
                if($v->checker_id) {
                    if ($v->dorecommend_id) {
                        $people = $v->belongsToChecker->name . ',' . $v->belongsToRecommend->name;
                    } else {
                        $people = $v->belongsToChecker->name;
                    }
                }else{
                    $people = '';
                }

                if($v->recommend == 0){
                    $behavior = [
                        'dotype' => '分类',
                        'recommend'=>'推荐',
                        'dosheild'=>'屏蔽'
                    ];
                }elseif($v->recommend == 1){
                    $behavior = [
                        'dotype' => '分类',
                        'cancelrecommend'=>'取消推荐',
                        'dosheild'=>'屏蔽'
                    ];
                }
            }elseif($classify == 2){
                if($v->dorecommend_id){
                    $people = $v->belongsToRecommend->name;
                }else{
                    $people = '';
                }
                $behavior = [
                    'dotype' => '分类',
                    'cancelrecommend'=>'取消推荐',
                    'dosheild'=>'屏蔽'
                ];


            }elseif($classify == 3){
                if($v->doshield_id){
                    $people = $v->belongsToShield->name;
                }else{
                    $people = '';
                }

                $batchBehavior = [
                    'cancelshield'=>'取消屏蔽',
                    'delete'=>'删除',
                ];
            }

            $tempData = [
                'type'=>$folder,
                'Name'=>$userName,
                'cover'=>$cover,
                'duration' => $duration,
                'time_add' => $time,
                'count' => $count,
                'integral' => $integral,
                'operator' => $people,
                'behavior' => $behavior,
            ];

            array_push($data,$tempData);

        }
        $dataNum = $mainData[1];
        $sumnum = MakeEffectsFile::select('id')->where('active','=',1)->where('test_result','=',1)->get()->count();
        $todaynew = MakeEffectsFile::select('id')->where('active','=',1)->where('test_result','=',1)->where('time_add','>',strtotime(date('Y-m-d',time())))->get()->count();
        return response()->json(['dataNum'=>$dataNum,'data'=>$data,'batchBehavior'=>$batchBehavior,'sumnum'=>$sumnum,'todaynew'=>$todaynew]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 混合滤镜分类页面
     */
    public function type(Request $request)
    {
        try{
            $active = $request->get('active',1);
            $page = $request->get('page',1);
            $everyPageNum = $request ->get('everypagenum',10);
            DB::beginTransaction();
            $mainData = MakeEffectsFolder::where('active','=',$active)->orderBy('sort')->forPage($page,$everyPageNum)->get();
            $dataNum = MakeEffectsFolder::select('id')->where('active','=',$active)->orderBy('sort')->get()->count();
            $data = [];
            $space = MakeEffectsFile::get()->sum('size');
            $max = $mainData->max('sort');
            $min = $mainData->min('sort');
            $num = $mainData->count();
            foreach($mainData as $k => $v)
            {
                $operator = $v->operator?$v->belongsToAdministratorOperator->name:'';
                $cover = $v->cover?$this->protocol.$v->cover:'';
                $name = $v->name;
                $time = $v->time_add;
                $creater = $v->belongsToAdministrator->name;
                $count = $v->count;
                $downloadNum = $v->hasManyFiles->count();
                $usageSpace = (int)$v->hasManyFiles->sum('size');
                $stopReason = $v->stop_reason?$v->reason:'';
                if($space == 0){
                    $usageSpaceProportion = 0;
                }else{
                    $usageSpaceProportion = (round($usageSpace/$space,2)*100).'%';
                }
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

                    $tempData = [
                        'cover' => $cover,
                        'time' => $time,
                        'name' => $name,
                        'creater' => $creater,
                        'count' => $count,
                        'downloadNum' => $downloadNum,
                        'usageSpaceAndProportion' => $usageSpace.'/'.$usageSpaceProportion,
                        'behavior' => $behavior
                    ];

                    array_push($data,$tempData);
                }else{
                        $behavior = [
                            'stop'=>'启用',
                            'delete' => '删除',
                        ];

                    $batchBehavior = [
                        'stop'=> '启用',
                        'delete' => '删除',
                    ];

                    $stopTime = $v->stop_time;
                    $tempData = [
                        'cover' => $cover,
                        'time' => $stopTime,
                        'name' => $name,
                        'operator' => $operator,
                        'reason' => $stopReason,
                        'behavior' => $behavior
                    ];

                    array_push($data,$tempData);
                }




            }

            DB::commit();
            return response()->json(['dataNum'=>$dataNum,'data'=>$data,'num'=>$num,'batchBehavior'=>$batchBehavior]);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加新分类
     */
    public function addType(Request $request)
    {
        try{
            $cover = $request->get('cover',null);
            $name = $request->get('name',null);
            $active = $request->get('active',0);
            DB::beginTransaction();
            $admin = Auth::guard('api')->user();
            if(is_null($cover)||is_null($name)){
                return response()->json(['message'=>'名称或图像不能为空']);
            }
            array_push($keys1,$cover);
            $keyPairs1 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            $srcbucket1 = 'hivideo-img-ects';
            $destbucket1 = 'hivideo-img';
            $message = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            if($message == 200){
                $folder = new MakeEffectsFolder;
                $folder -> name = $name;
                if($active == 1){
                    $folder -> active = 1;
                }else{
                    $folder -> active = 0;
                }
                $folder ->sort = (MakeEffectsFolder::max('sort'))+1;
                $folder ->time_add = time();
                $folder ->time_update = time();
                $folder ->create_id = $admin->id;
                $folder ->save();
            }else{
                return response()->json(['message'=>'上传图片失败','status'=>$message],200);
            }
            DB::commit();
            return response()->json(['message'=>'修改成功'],200);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

}
