<?php

namespace App\Api\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use App\Models\Make\{MakeAudioFile,MakeAudioFolder,MakeAudioDownloadLog,MakeAudioUser};
use Crypt;
use CloudStorage;
use DB;
use Auth;

class MakeAudioController extends BaseController
{
    protected $paginate = 20;

    /**
     * 编辑视频，音频文件详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function file(Request $request)
    {
        try{
            // 搜索条件
            $search = removeXSS($request->get('search'));

            // 获取文件夹id,默认为第一个
            $folder_id = (int)$request -> get('folder_id',MakeAudioFolder::active()->orderBy('sort')->firstOrFail()->id);

            // 获取页码
            $page = (int)$request -> get('page',1);

            // 初始化 用户下载过的收费文件的id数组
            $integral_ids = [];

            // 获取用户下载过的收费文件的id数组
            if($user = Auth::guard('api') -> user()) {
                $integral_ids = MakeAudioFile::whereHas('hasManyDownload', function($q) use($user) {
                    $q -> where('user_id', $user->id);
                }) -> where('integral', '<>', 0)
                    ->where('test_result',1)
                    -> pluck('id')
                    -> all();
            }

            // 获取数据
            $audio = MakeAudioFile::whereHas('belongsToFolder',function($q) use($folder_id) {
                $q -> where('id', $folder_id);
            })
                -> active()
                -> where('test_result',1)
                -> ofSearch($search)
                -> forPage($page, $this->paginate)
                -> get(['id','name','intro','count','audition_address','address','integral','duration']);

            // 调用内部函数，返回数据
            return $this -> handle($audio, $integral_ids);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 编辑视频，音频文件详情 上面调用
     * @param object $audio    集合
     * @param array $integral_ids   登录用户下载过的收费文件的id
     * @return \Illuminate\Http\JsonResponse
     */
    private function handle($audio, $integral_ids=[])
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
     * 编辑视频，音频目录
     * @return \Illuminate\Http\JsonResponse
     */
    public function folder()
    {
        try{
            // 获取文件夹id,默认为第一个
            $folder = MakeAudioFolder::active()->orderBy('sort')->get(['id','name','count']);

            return response() -> json(['data'=>$folder],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 下载音频文件
     * @param $id 用户id，后期会员免费使用
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function download($id,Request $request)
    {
        try{
            // 获取音频的id
            $file_id = Crypt::decrypt($request -> get('file_id'));

            $file = MakeAudioFile::active()->where('test_result',1)->findOrFail($file_id);

            $time = getTime();

            // 存入用户中心
            $file_user = MakeAudioUser::where('user_id',$id)->where('file_id',$file_id)->first();

            // 开启事务
            DB::beginTransaction();

            if(!$file_user)
                MakeAudioUser::create([
                    'file_id'       => $file_id,
                    'user_id'       => $id,
                    'time_add'      => $time,
                    'time_update'   => $time,
                ]);

            // 文件下载次数 +1
            $file -> update([
                'count'         => ++$file -> count,
                'time_update'   => $time
            ]);

            // TODO 金币扣除

            // 存入日志文件
            MakeAudioDownloadLog::create([
                'file_id'   => $file_id,
                'user_id'   => $id,
                'time_add'  => $time,
            ]);

            // 事务提交
            DB::commit();

            return response() -> json([
                'data'=>CloudStorage::privateUrl_zip($file -> address)
            ],200);

        } catch (DecryptException $e) {
            // 事务回滚
            DB::rollback();
            return response()->json(['error'=>'not_found'],404);
        } catch (ModelNotFoundException $e) {
            // 事务回滚
            DB::rollback();
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            // 事务回滚
            DB::rollback();
            return response()->json(['error'=>'not_found'],404);
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

                // 获取数据
                $audio = MakeAudioFile::with(['belongsToFolder'])
                    -> where('test_result',0)
                    -> forPage($page, $this->paginate)
                    -> where('active','!=',2)
                    -> get(['id','name','intro','count','audition_address','address','integral','duration']);

                // 调用内部函数，返回数据
                return $this -> handle($audio);
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
                $res1 = MakeAudioFile::find($id)->update(['test_result'=>$result]);

                $res2 = DB::table('audio_test_result')->insert([
                    'audio_id'   => $id,
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

                    $res = MakeAudioFile::find($v)->update(['test_result'=>$result]);

                    $ress =  DB::table('audio_test_result')->insert([
                        'audio_id'   => $id,
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


}