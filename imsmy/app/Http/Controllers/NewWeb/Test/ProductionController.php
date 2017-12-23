<?php

namespace App\Http\Controllers\NewWeb\Test;

use App\Models\FilmfestsProductions;
use App\Models\Productions;
use App\Models\Test\TestUser;
use App\Models\Tweet;
use App\Models\TweetContent;
use App\Models\TweetStandbyCover;
use function GuzzleHttp\default_ca_bundle;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use CloudStorage;
use Illuminate\Support\Facades\DB;
use Omnipay\Common\Exception\RuntimeExceptionTest;

class ProductionController extends Controller
{
    private $protocol = 'http://';

    private $paginate = 10;

    //

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 主页
     */
    public function index(Request $request)
    {
        try{
            $video = $this->protocol.'v.cdn.hivideo.com/web_bg.mp4';
            return response()->json(['data'=>$video],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }




    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 作品页面
     */
    public function Production(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user();
            $active = $request->get('active',null);
            $page = $request->get('page',1);
            $type = $request->get('type',1);
            $everyPageNum = $request ->get('everypagenum',10);
            switch ($type){
                case 1:
                    $type = 'created_at';
                    break;
                case 2:
                    $type = 'browse_times';
                    break;
                default:
                    $type = 1;
                    break;
            }
            $uod = $request->get('uod',1);
            switch ($uod){
                case 1:
                    $uod = 'desc';
                    break;
                case 2:
                    $uod = 'asc';
                    break;
                default:
                    $uod = 'desc';
            }
            $count = $request->get('count',0);
            $status = $request->get('status',null);
            $dataNum = Tweet::where('user_id','=',$user->id)->where('video','!=',null)
                ->where('type','=',3)
                ->where('active','!=',3)->where('active','!=',5)->where('browse_times','>=',$count)->ActiveProduction($active)->
                StatusProduction($status)->orderBy($type,$uod)->get()->count();
            if($page==0){
                $page =1;
            }elseif ($page > ceil($dataNum/$everyPageNum)){
                $page = ceil($dataNum/$everyPageNum);
            }
            $initialData = Tweet::where('user_id','=',$user->id)
                ->where('type','=',3)
                ->where('active','!=',3)->where('active','!=',5)->where('video','!=',null);
            $allProduction = $initialData->get()->count();
            $failProduction = Tweet::where('user_id','=',$user->id)->where('video','!=',null)
                ->where('type','=',3)
                ->where('active','!=',3)->where('active','!=',5)->ActiveProduction(9)->get()->count();
            $checkingProduction = Tweet::where('user_id','=',$user->id)->where('video','!=',null)
                ->where('type','=',3)
                ->where('active','!=',3)->where('active','!=',5)->ActiveProduction(6)->get()->count();
            $mainData = Tweet::where('user_id','=',$user->id)->where('video','!=',null)
                ->where('type','=',3)
                ->where('active','!=',3)->where('active','!=',5)->where('browse_times','>=',$count)->ActiveProduction($active)->
            StatusProduction($status)->orderBy($type,$uod)->forPage($page,$everyPageNum)->get();
            $data = [];
            foreach ($mainData as $k => $v)
            {
                $cover = $v->screen_shot;
                $name = $v->name;
                $time = $v->created_at;
                $playCount = $v->browse_times;
                if($v->is_priviate == 1){
                    $is_priviate = '、私有';
                }else{
                    $is_priviate = '';
                }
                $status2 = '';
                switch ($v->active){
                    case 0:
                        $status2 = '未审核'.$is_priviate;
                        break;
                    case 1:
                        $status2 = '参赛中'.$is_priviate;
                        break;
                    case 2:
                        $status2 = '未通过'.$is_priviate;
                        break;
                    case 4:
                        $status2 = '待定'.$is_priviate;
                        break;
                    case 6:
                        $status2 = '审核中'.$is_priviate;
                        break;
                    case 7:
                        $status2 = '处理中'.$is_priviate;
                        break;
                    case 8:
                        $status2 = '异常'.$is_priviate;
                        break;
                }

                if($v->active == 1){
                    $behavior =0;
                }else{
                    $behavior =1;
                }
                $duration = floor(($v->duration)/60).':'.($v->duration)%60;
                $tempData = [
                    'id'=>$v->id,
                    'cover'=>is_null($cover)?'':$this->protocol.$cover,
                    'name'=>$name,
                    'time'=>$time,
                    'playnum'=>$playCount,
                    'active'=>$status2,
                    'behavior'=>$behavior,
                    'duration'=>$duration,
                ];
                array_push($data,$tempData);
            }
            $sumsize = TestUser::where('id','=',$user->id)->first()->production->sum('size');
            $sumsize = round($sumsize/1024/1024/1024,2).'GB';

            return response()->json(['data'=>$data,'dataNum'=>$dataNum,'sumsize'=>$sumsize,'allProduction'=>$allProduction,'failProduction'=>$failProduction,'checkingProduction'=>$checkingProduction],200);
        }catch (ModelNotFoundException $q){
            return  response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 播放量条件
     */
    public function getCount()
    {
        try{
            $playCount = [
                [
                    'label'=>0,
                    'des'=>'全部',
                ],
                [
                    'label'=>50,
                    'des'=>'50以上',
                ],
                [
                    'label'=>100,
                    'des'=>'100以上',
                ],
                [
                    'label'=>200,
                    'des'=>'200以上',
                ],
                [
                    'label'=>500,
                    'des'=>'500以上',
                ],
                [
                    'label'=>1000,
                    'des'=>'1000以上',
                ],
                [
                    'label'=>5000,
                    'des'=>'5000以上',
                ],
                [
                    'label'=>10000,
                    'des'=>'1W+',
                ],
            ];
            return response()->json(['data'=>$playCount],200);
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * 状态条件
     */
    public function getStatus()
    {
        try{
            $status = [
                [
                    'label'=>null,
                    'des'=>'全部',
                ],
                [
                    'label'=> 0,
                    'des'=>'未审核'
                ],
                [
                    'label'=> 1,
                    'des'=>'参赛中'
                ],
                [
                    'label'=> 3,
                    'des'=>'未通过'
                ],
                [
                    'label'=> 6,
                    'des'=>'审核中'
                ],
                [
                    'label'=> 7,
                    'des'=>'处理中'
                ],
                [
                    'label'=> 8,
                    'des'=>'异常'
                ],
                [
                    'label'=> 9,
                    'des'=>'私有'
                ]
            ];
            return response()->json(['data'=>$status],200);
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function up(Request $request)
    {
        try{
            DB::beginTransaction();
            $user = \Auth::guard('api')->user();
            $data0 = Tweet::where('video','=',null)->where('type','=',3)->where('user_id',$user->id)->first();
            if($data0){
                $id = $data0->id;
            }else{
                $data = new Tweet;
                $data -> user_id = $user->id;
                $data -> created_at = time();
                $data -> type = 3;
                $data -> updated_at = time();
                $data ->save();
                $id = $data->id;
            }
            DB::commit();

            return response()->json(['id'=>$id,'user_id'=>$user->id],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * 执行上传
     */
    public function doUp(Request $request)
    {
        try{
            $id = $request->get('id');
            $name = $request->get('name',null);
            $des = $request->get('des',null);
            $is_priviate = $request->get('is_priviate',0);
            $is_download = $request->get('is_download',1);
            $is_reply = $request->get('is_reply',1);
            $password = $request->get('password',null);
            $size = $request->get('size',0);
            $address = $request->get('address',null);
            if(is_null($id)||is_null($name)||is_null($address)||is_null($size)){
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
            $newProduction -> password = $password;
            $newProduction -> size = $size;
            $newProduction -> video = $address;
            $newProduction -> created_at = time();
            $newProduction -> updated_at = time();
            $newProduction -> duration = $duration;
            $newProduction -> save();
            $content = new TweetContent;
            $content -> tweet_id = $id;
            $content -> content = $des;
            $content -> created_at = time();
            $content -> updated_at = time();
            $content ->save();

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
                    $cover = CloudStorage::saveCover($address,$newAddress);
                    if($cover){
                        DB::beginTransaction();
                        $bb = 'img.cdn.hivideo.com/'.$address.'vframe-001_'.$cover.'_.jpg';
                        $production = Tweet::find($id);
                        $production -> screen_shot = $bb;
                        $production -> active = 0;
                        $production -> video =$newAddress;
//                        $production -> video ='v.cdn.hivideo.com/'.$newAddress;
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
                    $message = CloudStorage::transcoding($bucket,$key,$width,$height);
                    if($message){
                        $production -> transcoding_video = $address.'.m3u8';
                        $production -> transcoding_id = $message;
                        $production -> video ='v.cdn.hivideo.com/'.$newAddress;
                        $production -> is_transcod = 1;
                        $production -> updated_at = time();
                        $production -> save();
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
                    return response()->json(['message'=>'success'],200);
                }

            }else{
                $production -> active =8;
                $production -> updated_at = time();
                $production -> save();
                $production -> error_reason = '移动失败';
                DB::commit();
                return response()->json(['message'=>'移动失败'],200);
            }
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function index1(Request $request)
    {
        try{
            $key =  $request->get('key');
            $bucket = 'hivideo-video';
            $a = CloudStorage::deleteNew($bucket,$key);
//            $bb = 'z0.5a3b6601b946531900e11481';
//            $a = CloudStorage::searchStatus($bb);
            dd($a);
//            dd($a['items'][0]);
            return response()->json(['data'=>$a],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 删除作品
     */
    public function delete(Request $request)
    {
        try{
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法']);
            }
            $id = explode('|',$id);
            DB::beginTransaction();
            foreach ($id as $k => $v)
            {
                $data = Tweet::find($id);
                if($data){
                    if($data->active != 1){
                        $data->active = 3;
                        $data->time_update = time();
                        $data->save();
                        $data2 = $data->belongsToManyActivity()->first();
                        $data2 ->count = ($data2->work_count)-1;
                        $data2 ->save();

                    }
                }
            }
            DB::commit();
            return response()->json(['message'=>'删除成功'],200);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function publicStataus()
    {
        try{
            $data = [
                [
                    'label'=>'is_reply',
                    'des'=>'禁止评论和评分'
                ],
                [
                    'label'=>'is_download',
                    'des'=>'禁止下载'
                ],
            ];
            return response()->json(['data'=>$data],200);
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
