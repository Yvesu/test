<?php
namespace App\Api\Controllers;

use App\Models\Make\{MakeFontFile};
use App\Models\Cloud\CloudStorageFile;
use App\Models\Cloud\CloudStorageFolder;
use App\Models\Cloud\CloudStorageSpace;
use CloudStorage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Auth;

/**
 * 视频制作 字体下载
 * Class MakeFontController
 * @package App\Api\Controllers
 */
class MakeFontController extends BaseController
{
    protected $paginate = 20;

    /**
     * 获取字体文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function file(Request $request)
    {
        try{
            $page = $request -> get('page',1);

            $user = Auth::guard('api')->user();

            // 非登录用户 或者没有开通云空间的用户
            if(!$user || !CloudStorageSpace::where('user_id',$user->id)->where('time_end','>',getTime())->first()) {

                // 获取系统自带的系统字体文件
                $files = MakeFontFile::active()
                    -> orderBy('sort')
                    -> forPage($page,$this->paginate)
                    -> get(['name','cover','address']);

                // 登录用户
            }else{

                // 判断用户的云空间中指定字体文件夹下是否有字体文件
                // 获取用户自己上传的字体文件夹的id
                $folder_id = CloudStorageFolder::where('user_id',$user->id)->where('name','字体')->first();

                $fonts_user = CloudStorageFile::where('user_id',$user->id)
                    -> where('folder_id',$folder_id->id)
                    -> forPage($page,$this->paginate)
                    -> get(['name','address']);

                // 自己字体已全部取完，需从系统获取
                if(!$count = $fonts_user->count()){

                    // 自己字体已全部取完，需从系统获取
                    $slice_from = $count - $page*$this->paginate;

                    // 获取系统自带的系统字体文件
                    $files = MakeFontFile::active()
                        -> orderBy('sort')
                        -> get(['name','cover','address'])
                        -> slice($slice_from,$this->paginate)
                        -> values();

                    $files = $files->toArray();
                } else {

                    // 此次获取全部为自己上传的字体，直接返回数据
                    if($count == $this->paginate){

                        $files = $fonts_user->toArray();
                    } else {

                        // 获取还需要获取的字体数量，从系统字体获取
                        $slice_count = $page*$this->paginate - $count;

                        // 获取系统自带的系统字体文件
                        $files = MakeFontFile::active()
                            -> orderBy('sort')
                            -> get(['name','cover','address'])
                            -> slice(0,$slice_count)
                            -> values();

                        $files = array_merge($fonts_user->toArray(),$files->toArray());
                    }
                }
            }

            $data = [];

            foreach($files as $value){

                $data[] = [
                    'name' => $value['name'],
                    'cover' => isset($value['cover']) ? CloudStorage::downloadUrl($value['cover']) : '',
                    'address' => CloudStorage::downloadUrl($value['address'])
                ];
            }

            return response()->json(['data'=>$data],200);

        } catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found',404]);
        } catch (\Exception $e){
            return response()->json(['error'=>'not_found',404]);
        }
    }
}