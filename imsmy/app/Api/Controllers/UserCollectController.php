<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ChannelTweetsTransformer;
use App\Api\Transformer\Discover\HotActivityTransformer;
use App\Api\Transformer\FragCollectTransformer;
use App\Api\Transformer\MakeFiterTransformer;
use App\Api\Transformer\MakeTemplateFileDetailsTransformer;
use App\Facades\CloudStorage;
use App\Models\Activity;
use App\Models\Fragment;
use App\Models\Make\MakeAudioEffectFile;
use App\Models\Make\MakeAudioFile;
use App\Models\Make\MakeEffectsFile;
use App\Models\Make\MakeFilterFile;
use App\Models\Make\MakeFontFile;
use App\Models\Make\MakeTemplateFile;
use App\Models\Tweet;
use App\Models\User;
use App\Models\UserCollect;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Crypt;

class UserCollectController extends Controller
{
    //默认数据条数
    protected $paginate = 20;

    protected $makeTemplateFileDetailsTransformer;

    protected $makeFiterTransformer;

    protected $channelTweetsTransformer;

    protected $fragCollectTransformer;

    protected $hotActivityTransformer;

    public function __construct
    (
        MakeTemplateFileDetailsTransformer $makeTemplateFileDetailsTransformer,
        MakeFiterTransformer $makeFiterTransformer,
        ChannelTweetsTransformer $channelTweetsTransformer,
        FragCollectTransformer $fragCollectTransformer,
        HotActivityTransformer $hotActivityTransformer

    )
    {
        $this -> makeTemplateFileDetailsTransformer     = $makeTemplateFileDetailsTransformer;
        $this -> makeFiterTransformer                   = $makeFiterTransformer;
        $this -> channelTweetsTransformer               = $channelTweetsTransformer;
        $this -> fragCollectTransformer                 = $fragCollectTransformer;
        $this -> hotActivityTransformer                 = $hotActivityTransformer;
    }

    /**
     * 创建收藏
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCollect(Request $request)
    {
        try {

            if (!is_numeric($request->get('id')) || !is_numeric($request->get('type'))) return response()->json(['error' => 'bad_request'], 403);

            //接收数据
            $id = $request->get('id');

            $type = $request->get('type');

            //获取用户数据
            $user = Auth::guard('api')->user();

            $user_id = $user->id;

            $already = UserCollect::where('user_id',$user_id)->where('type',$type)->where('type_id',$id)->first();

            if(!is_null($already)) return response()->json(['message'=>'Have been collected'],202);

            //当前时间
            $time = time();

            //事务开始
            \DB::beginTransaction();

            $arr = [
                'user_id' => $user_id,
                'type' => $type,
                'type_id' => $id,
                'status'    => '1',
                'create_time' => $time,
            ];

            //添加
            $res = UserCollect::create($arr);

            if ($res) {
                \DB::commit();

                return response()->json(['message' => 'success'], 200);
            } else {
                \DB::rollBack();
                return response()->json(['message'=>'failed'],403);
            }
        }catch (\Exception $e){
            \DB::rollBack();
            return response()->json(['message'=>'failed'],500);
        }
    }

    /**
     * 取消收藏
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCollect(Request $request)
    {
        try {
            if (!is_numeric($request->get('id'))) return response()->json(['error' => 'bad_request'], 403);

            //接收数据
            $id = $request->get('id');

            //事务开始
            \DB::beginTransaction();

            $res = UserCollect::find($id)->update(['status'=>'2']);

            if ($res) {
                \DB::commit();

                return response()->json(['message' => 'success'], 200);
            } else {
                \DB::rollBack();
                return response()->json(['message'=>'failed'],403);
            }
        }catch (\Exception $e){
            \DB::rollBack();
            return response()->json(['message'=>'failed'],500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showCollect(Request $request)
    {
        try{
            //过滤
            if (!is_numeric($request->get('page',1)) || !is_numeric($request->get('type'))) return response()->json(['error' => 'bad_request'], 403);

            //页码
            $page = $request-> get('page',1);

            //类型
            $type = $request -> get('type');

            //获取用户信息
            $user = Auth::guard('api')->user();

            //用户ID
            $user_id = $user->id;

            switch ($type){
                case 1:
                    return $this->template($page,$user_id);
                case 2:
                    return $this->filter($page,$user_id);
                case 3:
                    return $this->mixture($page,$user_id);
                case 4:
                    return $this->tweet($page,$user_id);
                case 5:
                    return $this->fragment($page,$user_id);
                case 6:
                    return $this->activity($page,$user_id);
                case 7:
                    return $this->audio($page,$user_id);
                case 8:
                    return $this->audioeffect($page,$user_id);
                case 9:
                    return $this->font($page,$user_id);
                default :
                    return response()->json(['message'=>'There is no such type'],403);
            }

        }catch (\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

    /**
     * @param $page
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    private function template($page,$user_id)
    {
        try{
                //获取收藏的id
                $ids =UserCollect::Ofuser($user_id)
                    -> Oftype(1)
                    -> Ofstatus(1)
                    -> forPage($page,$this->paginate)
                    -> pluck('type_id')
                    -> all();

                if ($ids){
                    //获取数据
                    $details = MakeTemplateFile::with(['belongsToUser' => function($q){
                        $q -> select('id', 'nickname', 'avatar');
                    }])
                        -> whereIn('id',$ids)
                        -> where('test_result',1)
                        -> where('active','!=',2)
                        -> get( ['id', 'user_id', 'name','intro','preview_address', 'integral','cover','count','time_add']);

                    // 调用内部函数，返回数据
                    return response() -> json([
                        'data'  => $this -> makeTemplateFileDetailsTransformer -> transformCollection($details->all()),
                    ], 200);
                }else{
                    return response() -> json([
                        'data'  => [],
                    ], 200);
                }

        } catch(\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

    /**
     * @param $page
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    private function filter($page,$user_id)
    {
        try{
            //获取收藏的id
            $ids =UserCollect::Ofuser($user_id)
                -> Oftype(2)
                -> Ofstatus(1)
                -> forPage($page,$this->paginate)
                -> pluck('type_id')
                -> all();

            if ($ids){

                //  2017 11 27 修改
                $audio = MakeFilterFile::with(['belongsToUser'=>function($q){
                    $q->select(['id','nickname']);
                },'belongsToManyFolder'=>function($q){
                    $q->select(['name']);
                },'belongsToTextureMixType'])
                    ->where('test_result',1)
                    ->whereIn('id',$ids)
                    ->where('active','!=',2)
                    ->get(['id','user_id','name','cover','content','count','integral','time_add','texture','texture_mix_type_id']);


                foreach($audio as $value){
                    $value -> cover = $value -> cover;
                }

                // 调用内部函数，返回数据
                return response() -> json(['data'=>$this ->makeFiterTransformer->transformCollection($audio->toArray())],200);

            }else{
                return response() -> json([
                    'data'  => [],
                ], 200);
            }

        } catch(\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

    /**
     * @param $page
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    private function mixture($page,$user_id)
    {
        try{
            //获取收藏的id
            $ids =UserCollect::Ofuser($user_id)
                -> Oftype(3)
                -> Ofstatus(1)
                -> forPage($page,$this->paginate)
                -> pluck('type_id')
                -> all();

            if ($ids){

                // 获取数据
                $with = [['belongsToUser',['nickname']],['belongsToFolder',['name']]];

                $audio = MakeEffectsFile::where('test_result',1)
                    -> whereIn('id',$ids)
                    -> selectListPageByWithAndWhereAndWhereHas($with, [], [], [], [$page, $this->paginate]);

                return $this -> file($audio, 2);
            }else{
                return response() -> json([
                    'data'  => [],
                ], 200);
            }

        } catch(\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

    /**
     * @param $audio
     * @param $type
     * @param array $integral_ids
     * @return \Illuminate\Http\JsonResponse
     */
    private function file($audio, $type, $integral_ids=[])
    {
        try{
            // 拼接地址
            foreach($audio as $key => $value){

                $value -> file_id = Crypt::encrypt($value->id);

                // 免费的文件和自己已经下载过的会有下载地址，收费的下载地址为空
                if(0 == $value->integral
                    || 1 == $type
                    || in_array($value->id, $integral_ids)){


                    $value -> address = CloudStorage::downloadUrl($value -> address);
                    $value -> high_address = CloudStorage::downloadUrl($value -> high_address);
                    $value -> super_address = CloudStorage::downloadUrl($value -> super_address);
                    $value -> integral = 0; // 已经下载过的则将下载所需金币变为0
                } else {

                    $value -> address = CloudStorage::downloadUrl($value -> address);
                    $value -> high_address = CloudStorage::downloadUrl($value -> high_address);
                    $value -> super_address = CloudStorage::downloadUrl($value -> super_address);

//                    $value -> address = '';
//                    $value -> high_address = '';
//                    $value -> super_address = '';
                }

                // 效果预览
//                $value -> address = CloudStorage::privateUrl_zip($value -> address);

                $value -> preview_address = CloudStorage::downloadUrl($value -> preview_address);

                // 效果封面
                $value -> cover = CloudStorage::downloadUrl($value -> cover);

                // 删除原id
                unset($value -> id);
            }

            return response() -> json(['data'=>$audio],200);

        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param $page
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    private function tweet($page,$user_id)
    {
        try{
            //获取收藏的id
            $ids =UserCollect::Ofuser($user_id)
                -> Oftype(4)
                -> Ofstatus(1)
                -> forPage($page,$this->paginate)
                -> pluck('type_id')
                -> all();

            if ($ids){
                //获取数据

                $tweets = Tweet::with(['belongsToUser'=>function($q){
                        $q->select(['id','nickname','avatar','verify','cover','verify_info','signature']);
                    },'hasOneContent'=>function($q){
                        $q->select(['id','tweet_id','content']);
                    }])
                    ->where('visible',0)
                    ->where('active',1)
                    ->whereNotIn('active',[2,5])
                    ->whereIn('id',$ids)
                    ->orderBy('browse_times','DESC')
                    ->get(['id', 'type', 'user_id', 'location','browse_times','user_top', 'photo', 'screen_shot', 'video', 'created_at']);

                // 过滤
                $tweets_data = $this->channelTweetsTransformer->transformCollection($tweets->all());

                return response() -> json([
                    'data'  => $tweets_data,
                ], 200);

            }else{
                return response() -> json([
                    'data'  => [],
                ], 200);
            }

        } catch(\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

    /**
     * @param $page
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    private function fragment($page,$user_id)
    {
        try{
            //获取收藏的id
            $ids =UserCollect::Ofuser($user_id)
                -> Oftype(5)
                -> Ofstatus(1)
                -> forPage($page,$this->paginate)
                -> pluck('type_id')
                -> all();

            if ($ids){
                //获取数据
                $rand_fragment = Fragment::with(['belongsToManyFragmentType'=>function($q){
                    $q->select('name');
                },'belongsToUser'])
                    ->where('active',1)
                    ->where('test_results',1)
                    ->whereIn('id',$ids)
                    ->orderBy('watch_count','DESC')
                    ->get();

                return response() -> json([
                    'data' =>  $this->fragCollectTransformer->transform($rand_fragment->toArray()),
                ], 200);
            }else{
                return response() -> json([
                    'data'  => [],
                ], 200);
            }
        } catch(\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

    /**
     * @param $page
     * @param $user_id
     * @return array|\Illuminate\Http\JsonResponse
     */
    private function activity($page,$user_id)
    {
        try{
            //获取收藏的id
            $ids =UserCollect::Ofuser($user_id)
                -> Oftype(6)
                -> Ofstatus(1)
                -> forPage($page,$this->paginate)
                -> pluck('type_id')
                -> all();

            if ($ids){

                $data = Activity::with(['belongsToUser' => function($q){
                    $q -> select('id','nickname','avatar','cover','verify','signature','verify_info');
                }, 'hasManyTweets'])
                    -> ofExpires()
                    -> active()
                    -> whereIn('id',$ids)
                    -> get(['id','user_id','bonus','comment','expires','time_add','icon','work_count']);

                return [
                    'data' => $this -> hotActivityTransformer->transformCollection($data->all()),
                ];
            }else{
                return response() -> json([
                    'data'  => [],
                ], 200);
            }

        } catch(\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

    /**
     * @param $page
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    private function audio($page,$user_id)
    {
        try{
            //获取收藏的id
            $ids =UserCollect::Ofuser($user_id)
                -> Oftype(7)
                -> Ofstatus(1)
                -> forPage($page,$this->paginate)
                -> pluck('type_id')
                -> all();

            if ($ids){
                //获取数据
                $audio = MakeAudioFile::with(['belongsToFolder'])
                    -> where('test_result',1)
                    -> where('active',1)
                    -> whereIn('id',$ids)
                    -> get(['id','name','intro','count','audition_address','address','integral','duration']);

                // 调用内部函数，返回数据
                return $this -> audiohandle($audio);
            }else{
                return response() -> json([
                    'data'  => [],
                ], 200);
            }

        } catch(\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

    /**
     * 编辑视频，音频文件详情 上面调用
     * @param object $audio    集合
     * @param array $integral_ids   登录用户下载过的收费文件的id
     * @return \Illuminate\Http\JsonResponse
     */
    private function audiohandle($audio, $integral_ids=[])
    {
        try{

            // 拼接地址
            foreach($audio as $key => $value) {

                // 对id进行加密
                $value -> file_id = Crypt::encrypt($value->id);

                $value -> audition_address = CloudStorage::privateUrl_zip($value -> audition_address);


                // 免费的文件和自己已经下载过的会有下载地址，收费的下载地址为空
                if(0 == $value->integral || in_array($value->id, $integral_ids)){


                    $value -> address = CloudStorage::privateUrl_zip($value -> address);

                    $value -> integral = 0; // 已经下载过的则将下载所需金币变为0
                } else {
                    $value -> address = '';
                }

                // 删除原id
                unset($value -> id);
            }

            return response() -> json(['data'=>$audio],200);

        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    private function audioeffect($page,$user_id)
    {
        try{
            //获取收藏的id
            $ids =UserCollect::Ofuser($user_id)
                -> Oftype(8)
                -> Ofstatus(1)
                -> forPage($page,$this->paginate)
                -> pluck('type_id')
                -> all();

            if ($ids){
                // 获取数据
                $audio = MakeAudioEffectFile::with(['belongsToFolder'])
                    ->where('test_result',1)
                    -> where('active',1)
                    -> whereIn('id',$ids)
                    -> get(['id','name','intro','count','audition_address','address','integral','duration']);

                // 调用内部函数，返回数据
                return $this -> audioeffecthandle($audio);
            }else{
                return response() -> json([
                    'data'  => [],
                ], 200);
            }

        } catch(\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

    /**
     * 编辑视频，音频文件详情 上面调用
     * @param object $audio    集合
     * @param array $integral_ids   登录用户下载过的收费文件的id
     * @return \Illuminate\Http\JsonResponse
     */
    private function audioeffecthandle($audio, $integral_ids=[])
    {
        try{

            // 拼接地址
            foreach($audio as $key => $value) {

                // 对id进行加密
                $value -> file_id = Crypt::encrypt($value->id);

                $value -> audition_address = CloudStorage::privateUrl_zip($value -> audition_address);

                // 免费的文件和自己已经下载过的会有下载地址，收费的下载地址为空
                if(0 == $value->integral || in_array($value->id, $integral_ids)){

                    $value -> address = CloudStorage::privateUrl_zip($value -> address);

                    $value -> integral = 0; // 已经下载过的则将下载所需金币变为0
                } else {
                    $value -> address = '';
                }

                // 删除原id
                unset($value -> id);
            }

            return response() -> json(['data'=>$audio],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * @param $page
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    private function font($page,$user_id)
    {
        try{
            //获取收藏的id
            $ids =UserCollect::Ofuser($user_id)
                -> Oftype(9)
                -> Ofstatus(1)
                -> forPage($page,$this->paginate)
                -> pluck('type_id')
                -> all();

            if ($ids){
                // 获取系统自带的系统字体文件

                $files = MakeFontFile::where('test_result',1)
                    -> orderBy('sort')
                    -> where('active',1)
                    -> get(['name','cover','address']);

                $files = $files->toArray();

                $data = [];

                foreach($files as $value){

                    $data[] = [
                        'name' => $value['name'],
                        'cover' => isset($value['cover']) ? CloudStorage::downloadUrl($value['cover']) : '',
                        'address' => CloudStorage::privateUrl_zip($value['address'])
                    ];
                }

                return response()->json(['data'=>$data],200);
            }else{
                return response() -> json([
                    'data'  => [],
                ], 200);
            }

        } catch(\Exception $e){
            return response()->json(['message'=>'not_found'],403);
        }
    }

}
