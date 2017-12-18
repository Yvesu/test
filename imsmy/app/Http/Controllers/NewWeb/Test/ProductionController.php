<?php

namespace App\Http\Controllers\NewWeb\Test;

use App\Models\FilmfestsProductions;
use App\Models\Productions;
use App\Models\Test\TestUser;
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

    private $paginate = 20;

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
            switch ($type){
                case 1:
                    $type = 'time_add';
                    break;
                case 2:
                    $type = 'count';
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
            }
            $count = $request->get('count',0);
            $status = $request->get('status',null);
            $initialData = Productions::where('id','=',$user->id)->where('active','!=',4);
            $allProduction = $initialData->get()->count();
            $failProduction = $initialData->Active(9)->get()->count();
            $checkingProduction = $initialData->Active(1)->get()->count();
            $mainData = $initialData->where('playnum','>=',$count)->Active($active)->
                Status($status)->orderBy($type,$uod)->forPage($page,$this->paginate)->get();
            $data = [];
            foreach ($mainData as $k => $v)
            {
                $cover = $v->cover;
                $name = $v->name;
                $time = date('Y/m/d H:i',$v->time_up_over);
                $playCount = $v->playnum;
                if($v->is_priviate == 1){
                    $is_priviate = '、私有';
                }else{
                    $is_priviate = '';
                }
                switch ($v->active){
                    case 0:
                        $status = '未审核'.$is_priviate;
                        break;
                    case 1:
                        $status = '审核中'.$is_priviate;
                        break;
                    case 2:
                        $status = '参赛中'.$is_priviate;
                        break;
                    case 3:
                        $status = '未通过'.$is_priviate;
                        break;
                    case 4:
                        $status = '删除'.$is_priviate;
                        break;
                    case 5:
                        $status = '处理中'.$is_priviate;
                        break;
                    case 7:
                        $status = '异常'.$is_priviate;
                        break;
                }

                if($active == 2){
                    $behavior =0;
                }else{
                    $behavior =1;
                }

                $tempData = [
                    'cover'=>$this->protocol.$cover,
                    'name'=>$name,
                    'time'=>$time,
                    'playnum'=>$playCount,
                    'active'=>$status,
                ];
                array_push($data,$tempData);
            }
            $sumsize = TestUser::where('id','=',$user->id)->first()->production->sum('size');
            $sumsize = round($sumsize/1024/1024/1024,2).'GB';
            return response()->json(['data'=>$data,'sumsize'=>$sumsize,'allProduction'=>$allProduction,'failProduction'=>$failProduction,'checkingProduction'=>$checkingProduction],200);
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
                    'label'=> 0,
                    'des'=>'未审核'
                ],
                [
                    'label'=> 1,
                    'des'=>'审核中'
                ],
                [
                    'label'=> 2,
                    'des'=>'参赛中'
                ],
                [
                    'label'=> 3,
                    'des'=>'未通过'
                ],
                [
                    'label'=> 5,
                    'des'=>'处理中'
                ],
                [
                    'label'=> 6,
                    'des'=>'私有'
                ],
                [
                    'label'=> 7,
                    'des'=>'异常'
                ],
            ];
            return response()->json(['data'=>$status],200);
        }catch (\Exception $e) {
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
            $keys = [];
            $id = $request->get('id');
            $film_id = $request->get('film_id',null);
            $name = $request->get('name',null);
            $des = $request->get('des',null);
            $is_priviate = $request->get('is_priviate',0);
            $password = $request->get('password',null);
            $size = $request->get('size',0);
            $address = $request->get('address',null);
            array_push($keys,$address);
            $keyPairs = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            $srcbucket = 'hivideo-video-ects';
            $destbucket = 'hivideo-video';
            $message = CloudStorage::copyfile($keyPairs,$srcbucket,$destbucket);
            DB::beginTransaction();
                $newProduction = Productions::find($id);
                $newProduction -> name = $name;
                $newProduction -> active = 5;
                $newProduction -> des = $des;
                $newProduction -> is_priviate = $is_priviate;
                $newProduction -> password = $password;
                $newProduction -> size = $size;
                $newProduction -> address = $address;
                $newProduction -> time_add = time();
                $newProduction -> time_update = time();
                $newProduction -> save();
                $filmFestProduction = new FilmfestsProductions;
                $filmFestProduction -> filmfests_id = $film_id;
                $filmFestProduction -> productions_id = $id;
                $filmFestProduction -> time_add = time();
                $filmFestProduction -> time_update = time();
                $filmFestProduction ->save();
            DB::commit();
            return redirect('/test/copy/'.$id);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * 移动
     */
    public function copy($id,Request $request)
    {
        try{
            DB::beginTransaction();
            $production = Productions::find($id);
            $address = $production->address;
            array_push($keys,$address);
            $keyPairs = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            $srcbucket = 'hivideo-video-ects';
            $destbucket = 'hivideo-video';
            $message = CloudStorage::copyfile($keyPairs,$srcbucket,$destbucket);
            if($message == 200){
                $production -> address =$address;
                $production -> time_update = time();
                $production -> save();
            }else{
                $production -> active =7;
                $production -> time_update = time();
                $production -> save();
            }
            DB::commit();
            return redirect('/test/transcoding/'.$id);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * 转码
     */
    public function transcoding($id,Request $request)
    {
        try{
            DB::beginTransaction();
            $production = Productions::find($id);
            $address = $production->address;
            $bucket = 'hivideo-video';
            $key = $address;
            $rule = "/(\d{3,4}\*\d{3*4})/";
            preg_match($rule,$address,$widthAndHeight);
            $width = explode('*',$widthAndHeight)[0];
            $height = explode('*',$widthAndHeight)[1];
            $message = CloudStorage::transcoding($bucket,$key,$width,$height);
            if($message){
                $production -> address = $address.'m3u8';
                $production -> time_update = time();
                $production -> save();
            }else{
                $production -> active =7;
                $production -> time_update = time();
                $production -> save();
            }
            DB::commit();
            return redirect('/test/DRM'.$id);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not'],404);
        }
    }


    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 加密
     */
    public function DRM($id,Request $request)
    {
        try{
            DB::beginTransaction();
            $production = Productions::find($id);
            $address = $production->address;
            $bucket = 'hivideo-video';
            $key = $address;
            $message = CloudStorage::DRM($bucket,$key);
            if($message){
                $production -> address = 'v.cdn.hivideo.com/'.$address;
                $production -> time_update = time();
                $production -> active = 0;
                $production -> save();
            }else{
                $production -> active =7;
                $production -> time_update = time();
                $production -> save();
            }
            DB::commit();
            return response()->json(['message'=>'success'],200);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
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
                $data = Productions::find($id);
                if($data){
                    if($data->active != 2){
                        $data->active = 4;
                        $data->time_update = time();
                        $data->save();
                        $data2 = $data->filmfests()->first();
                        $data2 ->count = ($data2->count)-1;
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
}
