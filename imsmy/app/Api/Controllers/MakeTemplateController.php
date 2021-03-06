<?php

namespace App\Api\Controllers;

use App\Models\Make\MakeTemplateFolder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Api\Transformer\ZxHomeImagesTransformer;
use Illuminate\Http\Request;
use App\Models\Make\MakeTemplateFile;
use App\Models\Make\MakeTemplateDownloadLog;
use App\Models\CreationCover;
use App\Models\CreationCoverUser;
use App\Models\CreationAds;
use App\Api\Transformer\MakeFileTransformer;
use App\Api\Transformer\MakeTemplateFileDetailsTransformer;
use App\Api\Controllers\Traits\UserToAdminGoldManage;
use CloudStorage;
use DB;
use Auth;
use Illuminate\Support\Facades\Cache;
use Log;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Token;

/**
 * 视频制作模板接口管理
 * Class MakeAudioEffectController
 * @package App\Api\Controllers
 */
class MakeTemplateController extends BaseController
{
    use UserToAdminGoldManage;

    private $makeFileTransformer;
    private $zxHomeImagesTransformer;
    private $makeTemplateFileDetailsTransformer;
    protected $paginate = 20;

    public function __construct(
        ZxHomeImagesTransformer $zxHomeImagesTransformer,
        MakeFileTransformer $makeFileTransformer,
        MakeTemplateFileDetailsTransformer $makeTemplateFileDetailsTransformer
    )
    {
        $this->zxHomeImagesTransformer = $zxHomeImagesTransformer;
        $this -> makeFileTransformer = $makeFileTransformer;
        $this -> makeTemplateFileDetailsTransformer = $makeTemplateFileDetailsTransformer;
    }

    /**
     * 获取用户的创作首页的封面
     * @return \Illuminate\Http\JsonResponse
     */
    public function cover()
    {
        $user = Auth::guard('api') -> user();

        $default = 0;

        // 判断用户是否在使用自定义的封面
        if($user && $cover_id = CreationCoverUser::where('user_id', $user -> id) -> first(['cover_id'])){
            if($data = CreationCover::active() -> find($cover_id -> cover_id, ['id', 'cover']))
                $default = 1;
        }

        if(0 == $default) {
            $data = CreationCover::active() -> where('recommend', 1) -> firstOrFail(['id', 'cover']);
        }

        return response() -> json([
            'data'  => ['cover' => CloudStorage::downloadUrl($data -> cover)]
        ], 200);
    }

    /**
     * 获取用户的创作首页的广告
     * @return \Illuminate\Http\JsonResponse
     */
    public function ads()
    {
        $ads = CreationAds::ofRecommend(getTime()) -> first(['type', 'type_id', 'url', 'image']);

        return response() -> json([
            'data'  => $ads ? $this->zxHomeImagesTransformer->transform($ads) : []
        ], 200);
    }

    /**
     * 模板目录
     * @return \Illuminate\Http\JsonResponse
     */
    public function folder()
    {
        try{
            $cache_floder = Cache::remember('template_floder',60,function(){
                // 获取文件夹id,默认为第一个
                $folder = MakeTemplateFolder::selectList([['active',1]],['sort','ASC'],['id','name','count']);

                // 返回数据
                return response() -> json(['data'=>$folder],200);
            });
            return $cache_floder;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 模板列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            // 要查询资料的类型 2正常目录，3最热，4最新，5推荐(默认),6搜索
            $type = (int)$request -> get('type',5);

            // 获取页码
            $page = (int)$request -> get('page',1);

            $folder_id = (int)$request -> get('folder_id');

            $template_index = Cache::remember('template_index'.$type.$page.$folder_id,60,function() use($type,$page,$folder_id){
                // 普通目录必须要有 folder_id
                if (2 === $type && !$folder_id)
                    return response()->json(['error'=>'bad_request'],403);

                // 获取数据
                $data = MakeTemplateFile::with(['belongsToUser'=>function($q){
                    $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
                },'belongsToFolder'=>function($q){
                    $q->select(['id','name']);
                }])
                    -> active()
                    -> where('test_result',1)
                    -> ofNormal()
                    ->ofType($type, $folder_id)
                    -> paginate($this -> paginate, ['id','user_id','time_add','folder_id','duration','preview_address','name','integral','cover','count'], 'page', $page);

                // 调用内部函数，返回数据
                return response() -> json([
                    'data'  => $this -> makeFileTransformer -> transformCollection($data -> all()),
                    'page_count' => $data -> toArray()['last_page']
                ], 200);

            });

            return $template_index;

        } catch (\Exception $e) {
            Log::error('chartlet',json_encode($e->getMessage()).date('Y-m-d H:i:s'));
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 推荐
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommend(Request $request)
    {
        try{
            $folder = $request -> get('folder', 0); // 0为推荐，其他为目录id

            // 获取页码
            $page = (int)$request -> get('page',1);

            $recommend_template = Cache::remember('recommend_template'.$folder.$page,60,function() use ($folder,$page){

                // 获取数据集合
                $data = MakeTemplateFile::with(['belongsToUser'=>function($q){
                    $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
                },'belongsToFolder'=>function($q){
                    $q->select(['id','name']);
                }])
                    -> where('test_result',1)
                    -> ofNormal()
                    -> ofFolder($folder)
                    -> active()
                    -> orderBy('sort')
                    -> paginate($this -> paginate, ['id','user_id','vipfree','storyboard_count','time_add','folder_id','duration','preview_address','name','integral','cover','count'], 'page', $page);

                // 调用内部函数，返回数据
                return response() -> json([
                    'data'  => $this -> makeFileTransformer -> ptransform($data -> all()),
                    'page_count' => $data -> toArray()['last_page']
                ], 200);

            });

            return $recommend_template;

        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }

    }

    /**
     * 模板详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request)
    {
        try{
            // 获取id
            if(!$id = (int)$request -> get('id'))
                return response() -> json(['error'=>'bad_request'], 403);

            $page = (int)$request -> get('page',1);

            $related = $this -> related($id, $page);

            // 大于1，只返回相关
            if($page > 1) {

                return response() -> json([
                    'related'  => $this -> makeFileTransformer -> transformCollection($related -> all()),
                    'page_count' => $related -> toArray()['last_page']
                ], 200);
            }

            // 获取数据集合
            $details = MakeTemplateFile::with(['belongsToUser' => function($q){
                $q -> select('id', 'nickname', 'avatar');
            }])
                -> ofNormal()
                -> active()
                -> where('test_result',1)
                -> findOrFail($id, ['id', 'user_id', 'name','intro','preview_address', 'integral','cover','count','time_add']);

            // 调用内部函数，返回数据
            return response() -> json([
                'details'  => $this -> makeTemplateFileDetailsTransformer -> transform($details),
                'related'  => $this -> makeFileTransformer -> transformCollection($related -> all()),
                'page_count' => $related -> toArray()['last_page']
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 模板的相关其他模板
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function related($id, $page)
    {
        try{

            $folder = MakeTemplateFile::ofNormal()-> where('test_result',1) -> active() -> find($id, ['folder_id']);

            // 获取数据集合
            $data = MakeTemplateFile::with(['belongsToUser'=>function($q){
                $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
            },'belongsToFolder'=>function($q){
                $q->select(['id','name']);
            }])
                -> ofNormal()
                -> where('folder_id', $folder->folder_id)
                -> active()
                -> orderBy('sort')
                -> where('test_result',1)
                -> paginate($this -> paginate, ['id','user_id','time_add','folder_id','duration','preview_address','name','integral','cover','count'], 'page', $page);

            // 返回数据
            return $data;

        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 下载文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function download(Request $request)
    {
        try{
            $user = Auth::guard('api') -> user();

            // 获取id
            $file_id = $request -> get('file_id');

            // 判断用户是否登录
            if($user) {
                $file = MakeTemplateFile::with(['belongsToUser' => function($q){
                    $q -> select('id', 'nickname', 'avatar');
                }])
                    -> ofDownloadLog($user -> id)
                    -> active()
                    -> where('test_result',1)
                    -> findOrFail($file_id);
            } else {
                $file = MakeTemplateFile::with(['belongsToUser' => function($q){
                    $q -> select('id', 'nickname', 'avatar');
                }])
                    -> active() -> where('test_result',1)-> findOrFail($file_id);

                // 非登录状态下如果需要积分则返回错误
                if($file -> integral) return response()->json(['error'=>'bad_request'], 403);
            }

            $time = getTime();

            // 开启事务
            DB::beginTransaction();

            // 文件下载次数 +1
            $file -> increment('count', 1, ['time_update' => $time]);

            // 判断用户是否在登录状态
            if($user) {

                // 如果已经下载过
                if(!$file -> hasManyDownload) {

                    $file -> hasManyDownload -> update([
                        'time_update'   => $time
                    ]);

                } else {

                    // 存入日志文件
                    MakeTemplateDownloadLog::create([
                        'file_id'   => $file_id,
                        'user_id'   => $user -> id,
                        'time_add'  => $time,
                        'time_update'  => $time
                    ]);

                    // 如果需要金币 用户向平台支付金币
                    if($file -> integral) {

                        $result = $this -> expendToPlatform($user -> id, $file -> integral, '下载视频制作模板');

                        if(2 == $result) {
                            return response()->json(['error'=>'gold_not_enough'], 407);
                        } elseif (0 == $result) {
                            return response()->json(['error'=>'bad_request'], 403);
                        }
                    }
                }
            }

            // 事务提交
            DB::commit();

            return response() -> json([
                // TODO 返回下载地址
                'data'=>CloudStorage::downloadUrl($file -> address)
            ],200);

        } catch (\Exception $e) {
            // 事务回滚
            DB::rollback();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 记录登录信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function record(Request $request)
    {
        try {
            // 用户的登录信息
            $user = Auth::guard('api')->user();

            if ($user) {
                DB::table('user_login_log')->insert([
                    'user_id' => $user->id,
                    'login_time' => time(),
                    'way' => $request->get('phone_type') ?: '',
                    'ip' => getIP() ?: null,
                ]);
            } else {
                DB::table('user_login_log')->insert([
                    'login_time' => time(),
                    'way' => $request->get('phone_type') ?: '',
                    'ip' => getIP() ?: null,
                ]);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }
    }

    /**
     * 测试专列
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tester(Request $request)
    {
        try {
            if (!is_numeric($page = $request->get('page', 1))) {
                return response()->json(['error' => 'bad_request'], 403);
            }

            $user = Auth::guard('api')->user();

            if($user->tester === 1){

                $details = MakeTemplateFile::with(['belongsToUser' => function($q){
                    $q -> select('id', 'nickname', 'avatar');
                }])
                    -> where('test_result',0)
                    -> where('active','!=',2)
                    -> get( ['id', 'user_id', 'name','intro','preview_address', 'integral','cover','count','time_add']);

                // 调用内部函数，返回数据
                return response() -> json([
                    'data'  => $this -> makeTemplateFileDetailsTransformer -> transformCollection($details->all()),
                ], 200);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }
    }

    /**
     * 测试操作
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testResult(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $result = $request ->get('result');

            $id = $request->get('id');

            DB::beginTransaction();

            //0 待检测  1检测通过   2 检测未通过
            if(is_numeric($id)){
                $res1 = MakeTemplateFile::find($id)->update(['test_result'=>$result]);

                $res2 = DB::table('effect_test_result')->insert([
                    'effect_id'   => $id,
                    'fail_reason'   => $request->get('reason',''),
                    'tester_id'     => $user->id,
                    'create_time'   => time(),
                    'update_time'   => time(),
                ]);

            }else{
                $obj =  objectToArray(json_decode($id));

                $res_1= [];
                $res_2= [];
                foreach ($obj as $v){

                    $res = MakeTemplateFile::find($v)->update(['test_result'=>$result]);

                    $ress =  DB::table('effect_test_result')->insert([
                        'effect_id'   => $id,
                        'fail_reason'   => $request->get('reason',''),
                        'tester_id'     => $user->id,
                        'create_time'   => time(),
                        'update_time'   => time(),
                    ]);

                    if($res){
                        $res_1[] = 1;
                    }else{
                        $res_1[] = 2;
                    }

                    if ($ress){
                        $res_2[] = 1;
                    }else{
                        $res_2[] = 2;
                    }

                }

                if(in_array(2,$res_1)){
                    $res1 = 0;
                }else{
                    $res1 = 1;
                }

                if(in_array(2,$res_2)){
                    $res2 = 0;
                }else{
                    $res2 = 1;
                }

            }

            if($res1 && $res2){
                DB::commit();
                return response()->json(['message'=>'success'],200);
            }else{
                DB::rollBack();
                return response()->json(['message'=>'failed'],500);
            }

        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function zip_template($id)
    {
        try{
            $addr = MakeTemplateFile::find($id)->address;

            return response()->json(['data'=>CloudStorage::privateUrl_zip($addr)],200);
        }catch (\Exception $e){
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }
    }

}