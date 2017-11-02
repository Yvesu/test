<?php
namespace App\Api\Controllers;

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
            $select = ['id','user_id','name','cover','content','folder_id','count','integral','time_add'];

            // 我的 下载过的
            if(1 === $type){

                // 获取用户信息
                if(!$user = Auth::guard('api') -> user())
                    return response()->json(['error'=>'bad_request'],403);

                // 获取数据
                $whereHas = [['hasManyUserFile',[['user_id',$user->id]]]];

                $audio = MakeFilterFile::selectListPageByWithAndWhereAndWhereHas([], $whereHas, $where, [], $page, $select);

                // 获取指定目录下的滤镜
            } elseif (2 === $type){

                // 普通目录必须要有 folder_id
                if(!$folder_id = (int)$request -> get('folder_id'))
                    return response()->json(['error'=>'bad_request'],403);

                $with = [['belongsToUser',['nickname']]];

                // 获取数据
                $audio = MakeFilterFile::ofType($type,$folder_id)
                    -> selectListPageByWithAndWhereAndWhereHas($with, [], $where, [], $page, $select);

            } else {

                $with = [['belongsToUser',['nickname']],['belongsToFolder',['name']]];

                $audio = MakeFilterFile::ofType($type)
                    -> ofSearch($search)
                    -> selectListPageByWithAndWhereAndWhereHas($with, [], $where, [], $page, $select);
            }

            foreach($audio as $value){
                $value -> cover = CloudStorage::downloadUrl($value -> cover);
            }

            // 调用内部函数，返回数据
            return response() -> json(['data'=>$audio],200);

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

}