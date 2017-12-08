<?php

namespace App\Http\Controllers\NewAdmin\MixResource;

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
                    'address'=>$oldData->address?$this->protocol.$oldData->address:null,
                    'cover'=>$oldData->cover?$this->protocol.$oldData->cover:null,
                    'folder_id'=>$oldData->folder_id,
                    'duration'=>floor(($oldData->duration)/60).':'.(($oldData->duration)%60),
                    'size'=>$oldData->size,
                    'integral'=>$oldData->integral,
                    'vipfree'=>$oldData->vipfree,
                    'distinguishability_x'=>$oldData->distinguishability_x,
                    'distinguishability_y'=>$oldData->distinguishability_y,
                    'preview_address'=>$oldData->preview_address?$this->protocol.$oldData->preview_address:null,
                ];


                foreach( $oldData->keyWord as $k => $keyWord)
                {
                    $data['keyword']['keyword'.$k]=$keyWord->keyword;
                }
            }else{
                $newData = new MakeEffectsFileTemporary;
                $newData->user_id = $user_id;
                $newData->save();
                $data = [
                    'id'=>$newData->id,
                    'user_id'=>$newData->user_id
                ];
            }
            DB::commit();
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }


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
            if(is_null($name)||is_null($duration)||is_null($folder)||is_null($distinguishability_x)||is_null($distinguishability_y)||is_null($cover)||is_null($preview_address)||is_null($address)||is_null($keywords)||is_null($size)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $duration = explode(':',$duration);
            $duration = ($duration[0]*60) + $duration[1];
            DB::beginTransaction();
            $effect = MakeEffectsFileTemporary::find($id);
            $effect->integral = $integral;
            $effect->folder = $folder;
            $effect->duration = $duration;
            $effect->distinguishability_x = $distinguishability_x;
            $effect->distinguishability_y = $distinguishability_y;
            $effect->cover = $cover;
            $effect->preview_address = $preview_address;
            $effect->address = $address;
            $effect->size = $size;
            $effect->vipfree = $vipfree;
            $effect->save();
            $label = '';
            if(!is_null($keywords)){
                $keywords = explode('|',$keywords);
                $keywords = array_unique($keywords);
                KeywordEffects::where('fragmentTemporary_id','=',$effect->id)->delete();
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
                    $keywordFragment -> fragmentTemporary_id = $effect->id;
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
                'integral' => $effect->intergral,'label' => $label,
                'play' => $this->protocol.'video.ects.cdn.hivideo.com/'.$effect->preview_address,
                'size' => round((($effect->size)/1024)/1024,2),
                'distinguishability'=>$effect->distinguishability_x.$effect->distinguishability_y,
                'type'=> $effect->belongsToFolder->name,
                'vipfree'=>($effect->vipfree)==1?'vip免费':'vip不免费',
                'label' =>$label,
            ];
            DB::commit();
            return response()->json(['data'=>$content],200);


        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

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
            $effect->address = 'zip.cdn.hivideo.com/'.$data->address;
            $effect->preview_address = 'v.cdn.hivideo.com/'.$data->preview_address;
            $effect->cover = 'img.cdn.hivideo.com/'.$data->cover;
            $effect->folder = $data->folder;
            $effect->resolution = $data->distinguishability_x.'*'.$data->distinguishability_y;
            $effect->duration = $data->duration;
            $effect->size = $data->size;
            $effect->integral = $data->integral;
            $effect->time_add = time();
            $effect->time_update = time();
            $effect->vipfree = $data->vipfree;
            $effect->save();
            array_push($keys2,$data1->preview_address);
            array_push($keys1,$data1->cover);
            array_push($keys3,$data1->address);
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
            $srcbucket2 = 'hivideo-file-ects';
            $srcbucket3 = 'hivideo-img-ects';
            $destbucket1 = 'hivideo-img';
            $destbucket2 = 'hivideo-video';
            $destbucket3 = 'hivideo-zip';
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
}
