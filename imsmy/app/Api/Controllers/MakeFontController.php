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
                    -> where('test_result',1)
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
                        -> where('test_result',1)
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
                            -> where('test_result',1)
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
                    'address' => CloudStorage::privateUrl_zip($value['address'])
                ];
            }

            return response()->json(['data'=>$data],200);

        } catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found',404]);
        } catch (\Exception $e){
            return response()->json(['error'=>'not_found',404]);
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


                // 获取系统自带的系统字体文件
                $files = MakeFontFile::where('test_result',0)
                    -> orderBy('sort')
                    -> where('active','!=',2)
                    -> forPage($page,$this->paginate)
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

            \DB::beginTransaction();

            //0 待检测  1检测通过   2 检测未通过
            if(is_numeric($id)){
                $res1 = MakeFontFile::find($id)->update(['test_result'=>$result]);

                $res2 = \DB::table('font_test_result')->insert([
                    'font_id'   => $id,
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

                    $res = MakeFontFile::find($v)->update(['test_result'=>$result]);

                    $ress =  \DB::table('font_test_result')->insert([
                        'font_id'   => $id,
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
                \DB::commit();
                return response()->json(['message'=>'success'],200);
            }else{
                \DB::rollBack();
                return response()->json(['message'=>'failed'],500);
            }

        }catch (\Exception $e){
            \DB::rollBack();
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }

    }


}