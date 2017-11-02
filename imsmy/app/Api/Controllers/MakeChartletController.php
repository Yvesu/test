<?php
namespace App\Api\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use App\Models\Make\{MakeChartletFile,MakeChartletFolder,MakeChartletDownloadLog,MakeChartletUser};
use App\Services\GoldTransactionService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Http\Attribute\CommonTrait;
use CloudStorage;
use DB;
use Auth;

class MakeChartletController extends BaseController
{
    use CommonTrait;

    protected $paginate = 20;

    /**
     * 编辑视频，贴图文件详情
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            // 要查询资料的类型 1我的，2正常目录，3最热，4最新，5推荐,6搜索
            $type = (int)$request -> get('type',1);

            // 搜索条件
            $search = removeXSS($request->get('search'));

            // 获取页码
            $page = (int)$request -> get('page',1);

            // 初始化 用户下载过的收费文件的id数组
            $integral_ids = [];

            // 获取用户下载过的收费文件的id数组
            if($user = Auth::guard('api') -> user()) {
                $integral_ids = MakeChartletFile::whereHas('hasManyDownload', function($q) use($user) {
                    $q -> where('user_id', $user->id);
                }) -> where('integral', '<>', 0)
                    -> pluck('id')
                    -> all();
            }

            // 判断是否为请求 我的 下载过的
            if(1 === $type){

                // 获取用户信息
                if(!$user) return response()->json(['error'=>'bad_request'],403);

                // 获取数据
                $audio = MakeChartletFile::selectListPageByWithAndWhereAndWhereHas([], [['hasManyUserFile',[['user_id',$user->id]]]], [['active',1]], [$page, $this->paginate]);

                // 调用内部函数，返回数据
                return $this -> file($audio,1);

            } elseif (2 === $type){

                // 普通目录必须要有 folder_id
                if(!$folder_id = (int)$request -> get('folder_id'))
                    return response()->json(['error'=>'bad_request'],403);

                // 获取数据
                $audio = MakeChartletFile::ofType($type,$folder_id)
                    ->selectListPageByWithAndWhereAndWhereHas([['belongsToUser',['nickname']]], [], [['folder_id', $folder_id],['active',1]], [], [$page, $this->paginate]);
            } else {

                // 获取数据
                $with = [['belongsToUser',['nickname']],['belongsToFolder',['name']]];
                $audio = MakeChartletFile::ofType($type)
                    -> ofSearch($search)
                    -> selectListPageByWithAndWhereAndWhereHas($with, [], [['active',1]], [], [$page, $this->paginate]);
            }

            // 调用内部函数，返回数据
            return $this -> file($audio, 2, $integral_ids);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 编辑视频，贴图文件详情 上面调用
     * @param $audio    集合
     * @param $type   1为自己下载过的，2为其他
     * @param array $integral_ids   自己下载过的收费文件id
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
                    $value -> address = '';
                    $value -> high_address = '';
                    $value -> super_address = '';
                }

                // 预览
                $value -> preview_address = CloudStorage::downloadUrl($value -> preview_address);

                // 封面
                $value -> cover = CloudStorage::downloadUrl($value -> cover);

                // 删除原id
                unset($value -> id);
            }

            return response() -> json(['data'=>$audio],200);

        } catch (ModelNotFoundException $e) {
            Log::error('chartlet',json_encode($e->getMessage()).date('Y-m-d H:i:s'));
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            Log::error('chartlet',json_encode($e->getMessage()).date('Y-m-d H:i:s'));
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 编辑视频，贴图目录
     * @return \Illuminate\Http\JsonResponse
     */
    public function folder()
    {
        try{
            // 获取文件夹id,默认为第一个
            $folder = MakeChartletFolder::selectList([['active',1]],['sort','ASC'],['id','name','count']);

            return response() -> json(['data'=>$folder],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 下载贴图文件
     * @param $id 用户id，后期会员免费使用
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function download($id, Request $request)
    {
        try{
            // 获取贴图的id
            $file_id = Crypt::decrypt($request -> get('file_id'));

            // 获取详情
            $file = MakeChartletFile::active()->findOrFail($file_id);

            // 判断下载是否需要关注对方
            if($file -> attention) {

                // 判断是否关注对方
                $this -> subscription() -> create($id, $file -> user_id);
            }

            // 存入用户中心
            $file_user = MakeChartletUser::where('user_id',$id)->where('file_id',$file_id)->first();

            // 判断是否需要花费金币
            if($file -> integral && !$file_user) {

                $intro = '下载贴图《'.$file->name.'》消费'.$file -> integral.'金币';

                $goldTransaction = new GoldTransactionService();

                // 扣除可用金币
                $result = $goldTransaction -> transaction($file -> user_id, $id, 5, $file->id, $file -> integral, $intro, 1,0);

                // 可用金币不足
                if(2 === $result) return response() -> json(['error'=>'lack_of_integral'],412);

                // 金币扣除失败
                if(0 === $result) return response() -> json(['error'=>'bad_request'],403);
            }

            $time = getTime();

            // 开启事务
            DB::beginTransaction();

            if(!$file_user)
                MakeChartletUser::create([
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

            // 存入日志文件
            MakeChartletDownloadLog::create([
                'file_id'   => $file_id,
                'user_id'   => $id,
                'time_add'  => $time,
            ]);

            // 事务提交
            DB::commit();

            return response() -> json([
                'data' => [
                    'address'       => CloudStorage::downloadUrl($file -> address),
                    'high_address'  => CloudStorage::downloadUrl($file -> high_address),
                    'super_address' => CloudStorage::downloadUrl($file -> super_address),
                ]
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

}