<?php
namespace App\Api\Controllers;

use App\Api\Transformer\MakeFiterTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use App\Models\Make\{MakeFilterFile,MakeFilterFolder,MakeFilterDownloadLog};
use App\Models\Make\MakeFilterUser;
use Illuminate\Support\Facades\Crypt;
use CloudStorage;
use DB;
use Auth;

/**
 * 滤镜接口
 * Class MakeFilterController
 * @package App\Api\Controllers
 */
class MakeFilterController extends BaseController
{
    protected $paginate = 20;

    private $makeFiterTransformer;

    public function __construct
    (
        MakeFiterTransformer $makeFiterTransformer
    )
    {
        $this -> makeFiterTransformer = $makeFiterTransformer;
    }


    /**
     * 编辑视频，滤镜
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            // 要查询资料的类型 1我的，2正常目录，3最热，4最新，5推荐
            $type = (int)$request -> get('type',1);

            // 搜索条件
            $search = removeXSS($request->get('search'));
            $where = [['active',1]];
            $page = [(int)$request -> get('page',1), $this->paginate];
            $select = ['id','user_id','name','cover','content','count','integral','time_add'];

            // 我的 下载过的
            if(1 === $type){

                // 获取用户信息
                if(!$user = Auth::guard('api') -> user())
                    return response()->json(['error'=>'bad_request'],403);

                // 获取数据
                $whereHas = [['hasManyUserFile',[['user_id',$user->id]]]];


                $audio = MakeFilterFile::where('test_result',1)->selectListPageByWithAndWhereAndWhereHas([], $whereHas, $where, [], $page, $select);

                // 获取指定目录下的滤镜
            } elseif (2 === $type){

                // 普通目录必须要有 folder_id
                if(!$folder_id = (int)$request -> get('folder_id'))
                    return response()->json(['error'=>'bad_request'],403);

                // yy  修改
                $audio = MakeFilterFile::WhereHas('belongsToManyFolder',function ($q) use ($folder_id){
                    $q->where('folder_id','=',$folder_id);
                })
                    ->ofSearch($search)
                    ->with(['belongsToUser'=>function($q){
                        $q->select(['id','nickname']);
                    },'belongsToManyFolder'=>function($q){
                        $q->select(['name']);
                    },'belongsToTextureMixType'])
                    ->forpage($request->get('page',1),$this->paginate)
                    ->where('active',1)
                    ->where('test_result',1)
                    ->get(['id','user_id','name','cover','content','count','integral','time_add','texture','texture_mix_type_id']);

            } else {

              //  2017 11 27 修改
                $audio = MakeFilterFile::ofType($type)
                    -> ofSearch($search)
                    ->with(['belongsToUser'=>function($q){
                        $q->select(['id','nickname']);
                    },'belongsToManyFolder'=>function($q){
                        $q->select(['name']);
                    },'belongsToTextureMixType'])
                    ->forpage($request->get('page',1),$this->paginate)
                    ->where('active',1)
                    ->get(['id','user_id','name','cover','content','count','integral','time_add','texture','texture_mix_type_id']);
            }


            foreach($audio as $value){
                $value -> cover = $value -> cover;
            }

            // 调用内部函数，返回数据
            return response() -> json(['data'=>$this ->makeFiterTransformer->transformCollection($audio->toArray())],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 编辑视频，滤镜目录
     * @return \Illuminate\Http\JsonResponse
     */
    public function folder()
    {
        try{
            // 获取文件夹id,默认为第一个
            $folder = MakeFilterFolder::selectList([['active',1]],['sort','ASC'],['id','name','count']);

            return response() -> json(['data'=>$folder],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommend(Request $request)
    {
        try{
            $filter = MakeFilterFile::with(['belongsToManyFolder','belongsToUser'=>function($q){
                    $q->select(['id','nickname']);
                },'belongsToManyFolder'=>function($q){
                    $q->select(['name']);
                }])
                ->where('recommend',1)
                ->where('active',1)
                ->where('test_result',1)
                ->forpage($request->get('page',1),$this->paginate)
                ->get(['id','user_id','name','cover','content','count','integral','time_add','texture','texture_mix_type_id']);

            return response() -> json(['data'=>$this ->makeFiterTransformer->transformCollection($filter->toArray())],200);
        }catch (\Exception $e){
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }
    }

    /**
     * 滤镜压缩包地址
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterurl($id)
    {
        try{
            $filter = MakeFilterFile::find($id);

            return response() -> json([
                'content'=> CloudStorage::privateUrl_zip($filter->content),
            ],200);

        }catch(\Exception $e){
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

                //  2017 11 27 修改
                $audio = MakeFilterFile::with(['belongsToUser'=>function($q){
                        $q->select(['id','nickname']);
                    },'belongsToManyFolder'=>function($q){
                        $q->select(['name']);
                    },'belongsToTextureMixType'])
                    ->forpage($request->get('page',1),$this->paginate)
                    ->where('test_result',0)
                    ->where('active','!=',2)
                    ->get(['id','user_id','name','cover','content','count','integral','time_add','texture','texture_mix_type_id']);


            foreach($audio as $value){
                $value -> cover = $value -> cover;
            }

            // 调用内部函数，返回数据
            return response() -> json(['data'=>$this ->makeFiterTransformer->transformCollection($audio->toArray())],200);
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
                $res1 = MakeFilterFile::find($id)->update(['test_result'=>$result]);

                $res2 = DB::table('filter_test_result')->insert([
                    'filter_id'   => $id,
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

                    $res = MakeFilterFile::find($v)->update(['test_result'=>$result]);

                    $ress =  DB::table('filter_test_result')->insert([
                        'filter_id'   => $id,
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

    public function pay(Request $request)
    {
        //支付的类型
        $type = $request->get('type');

        if(empty($type)) return response()->json(['error'=>'bad_request'],403);

        //获取用户信息
        $user = Auth::guard('api')->user();

        if($type === 'mixture') {
            dd(1);
        }
    }

}