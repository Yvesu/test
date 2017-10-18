<?php
namespace App\Api\Controllers;

use App\Api\Transformer\Cloud\FolderTransformer;
use App\Api\Transformer\Cloud\FileTransformer;
use App\Models\Cloud\{CloudStorageFolder,CloudStorageSpace,CloudStorageFile};
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests;
use CloudStorage;
use Auth;
use DB;
use Illuminate\Support\Facades\Cache;

/**
 * 用户云相册管理模块
 * Class UserProjectController
 * @package App\Http\Controllers\Admin\Demand
 */
class UserCloudController extends BaseController
{
    // 页码条数
    private $paginate = 60;

    private $folderTransformer;
    private $fileTransformer;

    public function __construct(
        FolderTransformer $folderTransformer,
        FileTransformer $fileTransformer
    ){
        $this -> folderTransformer = $folderTransformer;
        $this -> fileTransformer = $fileTransformer;
    }

    /**
     * 根目录下的文件夹
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function folders($id,Request $request)
    {
        try{
            // 获取下一页
            $page = (int)$request -> get('page',1);

            // 获取根目录下文件夹名称
            $folders = CloudStorageFolder::with(['hasManyFiles'=>function($query){
                $query -> active() -> orderBy('id','desc') -> ofPicture() -> get(['address','type']);
            }])  -> where('user_id', $id)
                -> active()
                -> forPage($page,$this->paginate)
                -> get();

            return response()->json($this -> folderTransformer -> transformCollection($folders -> all()), 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户空间信息情况
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function space($id){
        try{

            // 返回
            return response()->json([
                'space' => CloudStorageSpace::where('user_id',$id) -> firstOrFail(['total_space', 'used_space', 'free_space']),
            ], 200);

        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 文件夹下的文件
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function files($id,Request $request)
    {
        try{

            // 获取请求的页码
            $page = (int)$request -> get('page',1);

            // 文件夹id
            $folder_id = $request -> get('folder_id');

            // 文件类型,类型作为键进行查询，速度更快  0视频，1图片，2其他，3全部，4共享
            $type = array(0=>1,1=>1,2=>1,3=>1,4=>1);

            // 判断 根目录下文件夹名称 是否存在
            if(!isset($type[$request -> get('type')]) || !is_numeric($folder_id) || !$folder = CloudStorageFolder::where('user_id',$id)->active()->find($folder_id))
                return response()->json(['error' => 'bad_request'], 403);

            // 获取根目录下文件集合
            $files = CloudStorageFile::where('user_id', $id)
                -> where('folder_id',$folder_id)
                -> ofFileType($request -> get('type'))
                -> ofFileStatus($folder -> name)    // 判断是否为回收站的文件
                -> forPage($page,$this->paginate)
                -> orderBy('id','DESC')
                -> get(['id','name','address','screenshot','type','format','size','date','time_add']);

            // 截图
            foreach($files as $value){

                // 图片截图
                if(1 == $value -> format){
                    $value -> screenshot = CloudStorage::downloadUrl($value -> address . '?imageView2/1/w/183/h/183');
                // 视频截图
                }elseif(2 == $value -> format){
                    $value -> screenshot = CloudStorage::downloadUrl($value -> screenshot . '?imageView2/1/w/183/h/183');
                }

                $value -> address = CloudStorage::downloadUrl($value -> address);
            }

            // 初始化
            $new_array = [];

            // 判断是否为空,为空返回空数组
            if($files -> count()) {

                $files_array = $files -> groupBy('date')->all();

                // 图片按日期排序
                if(1 == $request -> get('type')){

                    foreach ($files_array as $key => $value) {
                        $new_array[] = ['date' => $key, 'data' => $value];
                    };
                }else{

                    $new_array[] = ['date' => '', 'data' => $files];
                }
            }

            // 返回
            return response()->json($new_array, 200);

        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户上传云相册所请求数据 根目录下文件夹名称
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function add($id)
    {
        try{

            // 获取根目录下文件夹名称
            $folders = CloudStorageFolder::where('user_id',$id)->active()->get(['id','name'])->all();

            return response()->json(['folders'=>$folders],200);

        }catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户上传文件保存
     *
     * @param $id
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function insertFile($id,Request $request)
    {
        try{

            // 获取用户提交的所有数据
            $data = $request -> all();

            // 获取现在时间
            $time = getTime();

            // 文件类型,类型作为键进行查询，速度更快
            $type = array(0=>1,1=>1,2=>1);

            // 判断 根目录下文件夹名称 是否存在
            if(!is_numeric($data['folder_id']) || !$folder = CloudStorageFolder::where('user_id',$id)->active()->find($data['folder_id']))
                return response()->json(['error' => 'bad_request'], 403);

            // 对上传文件 address/type/size/name 进行解析
            $info = json_decode($data['info'],true);

            // 初始化一个数组，接收要修改的地址信息
            $qiniu = [];

            // 初始化新增文件数量及总使用空间
            $count = 0;
            $file_space = 0;

            // 获取用户云空间容量的集合
            $space = CloudStorageSpace::where('user_id',$id)->firstOrFail();

            // 开启事务
            DB::beginTransaction();

            // 遍历
            foreach($info as $value){

                // 判断接收数据是否符合规范
                if(!isset($value['address']))
                    return response()->json(['error' => 'bad_request'], 403);

                // 判断该文件名是否已经存在,存在则跳过
                if(CloudStorageFile::where('name',removeXSS($value['name']))->first())
                    $value['name'] .= '(1)';

                // 获取文件在七牛上的详情
                $file_info = json_decode(file_get_contents(CloudStorage::downloadUrl($value['address']).'?stat'),true);

                // 获取文件类型
                switch(explode('/',$file_info['mimeType'])[0]){

                    case 'image':
                        $file_type = 1;
                        $format = 1;
                        break;
                    case 'video':
                        $file_type = 0;
                        $format = 2;
                        break;
                    case 'audio':
                        $file_type = 3;
                        $format = 3;
                        break;
                    case 'application': // ppt、doc等办公格式
                        $file_type = 2;
                        $format = 4;
                        break;
                    default :           // 其他
                        $file_type = 2;
                        $format = 5;
                        break;
                }

                // 获取文件的扩展名
                $extension = pathinfo(CloudStorage::downloadUrl($value['address']),PATHINFO_EXTENSION);

                // 判断文件大小是否超出剩余可用空间
                $space -> free_space -= $file_info['uploaded'];
                if($space -> free_space < 0)
                    return response()->json(['error'=>'out_of_space'],456);

                // 空间增加
                $file_space += $file_info['uploaded'];

                // 保存用户提交信息
                $file = CloudStorageFile::create([
                    'user_id'       => $id,
                    'name'          => removeXSS($value['name']),
                    'folder_id'     => $data['folder_id'],
                    'type'          => $file_type,
                    'format'        => $format,
                    'extension'     => $extension,
                    'size'          => $file_info['uploaded'],
                    'date'          => date('Ymd'),
                    'time_add'      => $time,
                    'time_update'   => $time
                ]);

                // 对上传文件进行重命名
                if (isset($value['address'])) {

                    // 文件为视频类型
                    if(2 === $format) {

                        // 保存截屏，第一帧，通过回调保存 回调的路由在 /routes/web.php
                        $pfop = CloudStorage::persistentFop($value['address'],'vframe/jpg/offset/1','http://101.200.75.163/qiniu/screenshot');

                        // 将处理的id保存至缓存,供回调时使用
                        Cache::put($pfop, $file->id, 30);
                    }

                    // 重命名
                    $arr = explode('/',$value['address']);

                    // 拼接新 key
                    $new_key = 'folder/' . $data['folder_id'] . '/' . $file -> id . '/' . str_random(10) . $arr[sizeof($arr) - 1];

                    // 要修改的七牛地址
                    $qiniu[$value['address']] = $new_key;

                    // 修改地址
                    $file -> address = $new_key;
                } else {

                    return response()->json(['error' => 'bad_request'], 403);
                }

                // 对项目进行保存
                $file->save();

                // 相应文件夹下总数量 +1
                $count++;
            }

            // 将存在七牛云上的内容进行重命名
            CloudStorage::batchRename($qiniu);

            // 空间信息保存
            $space -> used_space += $file_space;
            $space -> time_update = $time;
            $space -> save();

            // 相应文件夹下总数量
            $folder -> count += $count;
            $folder -> save();

            // 事务提交
            DB::commit();

            return response()->json(['status'=>'ok'],201);

        }catch (\Exception $e) {

            // 事务回滚
            DB::rollback();

            return response()->json(['error' => 'bad_request'], 403);
        }
    }

    /**
     * 用户创建根目录下的文件夹名称
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insertFolder($id,Request $request)
    {
        try{

            // 获取要添加的文件夹名称
            $name = removeXSS($request -> get('name'));

            // 判断此文件夹名称是否已经存在
            $folder = CloudStorageFolder::where('user_id',$id)
                        ->where('name',$name)
                        ->active()
                        ->first();

            // 已存在，返回错误
            if($folder) return response()->json(['error' => 'already_exists'], 403);

            // 获取现在时间
            $time = getTime();

            // 保存
            $data = CloudStorageFolder::create([
                'user_id'       => $id,
                'name'          => $name,
                'time_add'      => $time,
                'time_update'   => $time
            ]);

            // 返回新创建的id
            return response()->json(['id'=>$data->id],201);

        }catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户删除根目录下的文件夹
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFolder($id,Request $request)
    {
        try{

            // 判断 根目录下文件夹名称 是否存在
            if(!is_numeric($folder_id = $request->get('folder_id')))
                return response()->json(['error' => 'bad_request'], 403);

            // 获取该文件夹是否存在
            if(!$folder = CloudStorageFolder::where('user_id',$id)->find($folder_id))
                return response()->json(['error' => 'not_found'], 404);

            // 获取现在时间
            $time = getTime();

            // 判断删除方式，是初步删除还是从回收站彻底删除
            if ($folder -> active === 1) {

                DB::beginTransaction();

                // 修改文件状态为删除状态，七天后正式删除
                $folder -> update(['active' => 0,'time_update' => $time]);

                // 修改该文件夹下面文件的状态为删除
                CloudStorageFile::where('folder_id',$folder_id)
                    -> where('active',1)
                    -> update(['active' => 0,'time_update' => $time]);

                DB::commit();

            } else {

                DB::beginTransaction();

                # 彻底删除
                // 删除七牛上的资源信息
                CloudStorage::deleteDirectory('folder/'.$folder_id);

                // 获取该文件夹下所有的文件集合
                CloudStorageFile::where('folder_id',$folder_id) -> delete();

                // 删除目录
                CloudStorageFolder::where('id',$folder_id) -> delete();

                DB::commit();
            }

            // 返回新创建的id
            return response()->json(['status'=>'ok'],201);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户删除单个文件
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFile($id,Request $request)
    {
        try{

            # 判断该文件是否存在
            $file_id = $request->get('file_id');
            if(!is_numeric($file_id))
                return response()->json(['error' => 'bad_request'], 403);

            $file = CloudStorageFile::where('user_id',$id)->findOrFail($file_id);

            DB::beginTransaction();

            // 判断删除方式，是初步删除还是从回收站彻底删除
            if($file -> active !== 1){

                # 彻底删除
                // 删除七牛上的资源信息
                CloudStorage::delete($file -> address);

                // 判断是否有 screenshot
                if($file -> screenshot) CloudStorage::delete($file -> screenshot);

                // 从数据表中删除
                $file -> delete();

            }else{

                // 修改文件状态为删除状态，七天后正式删除
                $file -> update(['active' => 0,'time_update' => getTime()]);

                // 获取该文件所属的文件夹
                $folder = CloudStorageFolder::where('user_id',$id)
                    -> where('id',$file->folder_id)
                    -> active()
                    -> firstOrFail();

                // 将相应文件数量-1
                $folder -> count --;

                $folder -> save();
            }

            DB::commit();

            // 返回
            return response()->json(['status'=>'ok'],201);

        }catch(ModelNotFoundException $e){
            // 事务回滚
            DB::rollback();
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            // 事务回滚
            DB::rollback();
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户对文件夹进行重命名
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function renameFolder($id,Request $request)
    {
        try{

            // 获取文件夹id
            if(!is_numeric($folder_id = $request->get('folder_id')))
                return response()->json(['error' => 'not_found'], 404);

            // 获取该文件夹是否存在
            if(!$folder = CloudStorageFolder::where('user_id',$id)->find($folder_id))
                return response()->json(['error' => 'not_found'], 404);

            // 获取新的文件夹名称
            $name = removeXSS($request -> get('name'));

            // 判断此文件夹名称是否已经存在
            $data = CloudStorageFolder::where('user_id',$id)
                -> where('name',$name)
                -> active()
                -> first();

            // 已存在，返回错误
            if($data) return response()->json(['error' => 'already_exists'], 403);

            // 修改
            $folder -> name = $name;
            $folder -> time_update = getTime();

            // 保存
            $folder -> save();

            // 返回新创建的id
            return response()->json(['status'=>'ok'],201);

        }catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户对单个文件进行重命名
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function renameFile($id,Request $request)
    {
        try{

            // 获取文件id
            if(!is_numeric($file_id = $request->get('file_id')))
                return response()->json(['error' => 'not_found'], 404);

            // 获取该文件是否存在
            if(!$file = CloudStorageFile::where('user_id',$id)->find($file_id))
                return response()->json(['error' => 'not_found'], 404);

            // 获取新的文件名称
            $name = removeXSS($request -> get('name'));

            // 判断此文件名称是否已经存在
            $data = CloudStorageFile::where('user_id',$id)
                -> where('folder_id',$file->folder_id)
                -> where('name',$name)
                -> active()
                -> first();

            // 已存在，返回错误
            if($data) return response()->json(['error' => 'already_exists'], 403);

            // 修改
            $file -> name = $name;
            $file -> time_update = getTime();

            // 保存
            $file -> save();

            // 返回新创建的id
            return response()->json(['status'=>'ok'],201);

        }catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户对单个文件进行移动
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFile($id,Request $request)
    {
        try{

            // 获取文件id
            if(!is_numeric($file_id = $request->get('file_id')))
                return response()->json(['error' => 'not_found'], 404);

            // 获取该文件是否存在
            if(!$file = CloudStorageFile::where('user_id',$id)->find($file_id))
                return response()->json(['error' => 'not_found'], 404);

            // 判断 根目录下文件夹名称 是否存在
            if(!is_numeric($folder_id = $request->get('folder_id')))
                return response()->json(['error' => 'not_found'], 404);

            // 获取该文件夹是否存在
            if(!$folder = CloudStorageFolder::where('user_id',$id)->find($folder_id))
                return response()->json(['error' => 'not_found'], 404);

            // 判断新文件夹下该文件名是否已存在
            if(CloudStorageFile::where('user_id',$id)->where('folder_id',$folder_id)->where('name',$file->name)->active()->first())
                return response()->json(['error' => 'already_exists'], 403);

            // 修改
            $file -> folder_id = $folder_id;
            $file -> address = preg_replace('folder/'.$file -> folder_id,'folder/'.$folder_id,$file -> address);

            // 判断 screenshot 是否有值
            if($file -> screenshot)
                $file -> screenshot = preg_replace('folder/'.$file -> folder_id,'folder/'.$folder_id,$file -> screenshot);

            $file -> time_update = getTime();

            // 保存
            $file -> save();

            // 返回新创建的id
            return response()->json(['status'=>'ok'],201);

        }catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 用户云空间所有的图片和mov/mp4格式的视频
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function effect($id,Request $request)
    {
        try{

            // 获取请求的页码
            $page = (int)$request -> get('page',1);

            // 获取根目录下文件集合
            $files = CloudStorageFile::active()
                -> where('user_id', $id)
                -> where('type',1)
                -> orWhere('extension','mov')
                -> orWhere('extension','mp4')
                -> forPage($page,$this->paginate)
                -> orderBy('id','DESC')
                -> get(['id','name','address','screenshot','type','format','extension','size','date','time_add']);

            // 截图
            foreach($files as $value){

                // 图片截图为原图
                if(1 == $value -> format){
                    $value -> screenshot = CloudStorage::downloadUrl($value -> address);
                    // 视频截图
                } elseif (2 == $value -> format){
                    $value -> screenshot = CloudStorage::downloadUrl($value -> screenshot);
                }

                $value -> address = CloudStorage::downloadUrl($value -> address);
            }

            // 返回
            return response()->json(['data' => $files], 200);

        } catch(ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

}
