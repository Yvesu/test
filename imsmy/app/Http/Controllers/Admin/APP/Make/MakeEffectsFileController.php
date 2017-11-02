<?php

namespace App\Http\Controllers\Admin\App\Make;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Admin\BaseSessionController;
use Illuminate\Http\Request;
use App\Models\Make\{MakeEffectsFile,MakeEffectsFolder};
use CloudStorage;
use Auth;
use DB;
use Illuminate\Support\Facades\Cache;

class MakeEffectsFileController extends BaseSessionController
{
    protected $paginate = 20;

    /**
     * 效果主页
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            // 搜索条件
            $condition = (int)$request -> get('condition','');
            $search = post_check($request -> get('search'));
            $num = (int)$request->input('num',$this->paginate);

            // 状态
            $active = (int)$request -> get('active',1);

            // 判断是否为搜索目录
            if(4 == $condition)
                $search = MakeEffectsFolder::where('name',$search)->firstOrFail()->id;

            // 判断是否为推荐
            if(3 == $active){

                // 获取集合
                $datas = MakeEffectsFile::whereActive(1)
                    -> where('recommend',1)
                    -> orderBy('sort')
                    -> ofSearch($search,$condition)
                    -> paginate($num);
            } else {

                // 获取集合
                $datas = MakeEffectsFile::whereActive($active)
                    -> ofSearch($search,$condition)
                    -> paginate($num);
            }

            // 搜索类型
            $cond = [1=>'ID',2=>'标题',3=>'简介',4=>'目录'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$num,
                'search'=>$search,
                'active'    => $active,
            ];

            // 返回视图
            return view('admin/app/make/effects/file/index',[
                'datas'     => $datas,
                'request'   => $res,
                'condition' => $cond,
                'folder'    => MakeEffectsFolder::pluck('name','id')
            ]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * 添加效果文件
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        return view('admin/app/make/effects/file/add',[
            'folder'=>MakeEffectsFolder::active()->orderBy('sort')->get(['id','name'])
        ]);
    }

    /**
     * 保存效果文件
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function insert(Request $request)
    {
        try{
            // 检测上传文件
            $this -> validate($request,[
                'name'      => 'required|max:255',
                'intro'     => 'required|max:255',
                'key'       => 'required',
                'preview_key' => 'required',
                'folder_id' => 'required|numeric',
            ],
                [
                    'name.required'     => '名称不能为空',
                    'name.max'          => '名称长度小于255位',
                    'intro.required'    => '介绍不能为空',
                    'intro.max'         => '介绍长度小于255位',
                    'key.required'      => '上传文件不能为空',
                    'preview_key.required' => '上传文件不能为空',
                    'folder_id.required'=> '所属目录不能为空',
                ]);

            $screen_shot = $request->file('video-icon');

            // 获取上传截图的宽高
            $shot_width_height = getimagesize($screen_shot)[0].'*'.getimagesize($screen_shot)[1];

            // 获取所有数据
            $input = $request -> all();

            // 获取上传文件的各类格式信息 时长/大小
            $format = json_decode(file_get_contents(CloudStorage::downloadUrl($input['key']).'?avinfo'))->format;

            $time = getTime();

            // 开启事务
            DB::beginTransaction();

            $file = MakeEffectsFile::create([
                'name'          =>  $input['name'],
                'intro'         =>  $input['intro'],
                'user_id'       =>  session('admin')->user_id,
                'folder_id'     =>  $input['folder_id'],
                'duration'      =>  $format -> duration,
                'size'          =>  $format -> size,
                'address'       =>  $input['key'],
                'preview_address'=>  $input['preview_key'],
                'time_add'      =>  $time,
                'time_update'   =>  $time,
            ]);

            // 新代码 第一个参数：上传到七牛后保存的文件名，第二个参数：要上传文件的本地路径
            $result = CloudStorage::putFile(
                'effects/'.$file->id.'/'.str_random(10).$time.'_'.$shot_width_height.'_.'.$screen_shot->getClientOriginalExtension(),
                $screen_shot
            );

            // 封面
            $file -> cover = $result[0]['key'];

            // 效果视频新名称
            $new_name = 'effects/'.$file->id.'/'.str_random(10).$time.'.'.pathinfo($file->address,PATHINFO_EXTENSION);
            $new_preview_name = 'effects/'.$file->id.'/'.str_random(10).$time.'.'.pathinfo(CloudStorage::downloadUrl($input['preview_key']),PATHINFO_EXTENSION);

            // 判断是否有高清资源
            if($input['high_key']) {
                $new_high_name = 'chartlet/'.$file->id.'/'.str_random(10).$time.'.'.pathinfo(CloudStorage::downloadUrl($input['high_key']),PATHINFO_EXTENSION);
                CloudStorage::rename($input['high_key'],$new_high_name);
                $file -> high_address = $new_high_name;
            }

            // 判断是否有超清资源
            if($input['super_key']) {
                $new_super_name = 'chartlet/'.$file->id.'/'.str_random(10).$time.'.'.pathinfo(CloudStorage::downloadUrl($input['super_key']),PATHINFO_EXTENSION);
                CloudStorage::rename($input['super_key'],$new_super_name);
                $file -> super_address = $new_super_name;
            }

            // 对上传效果进行重命名
            CloudStorage::rename($input['key'],$new_name);
            CloudStorage::rename($input['preview_key'],$new_preview_name);

            $file -> address = $new_name;
            $file -> preview_address = $new_preview_name;
            $file -> save();

            // 事务提交
            DB::commit();

            return redirect('admin/make/effects/file/index?active=0');

        } catch (ModelNotFoundException $e){
            DB::rollback();
            abort(404);
        } catch (\Exception $e){
            DB::rollback();
            abort(404);
        }
    }

    /**
     * 效果文件 删除/激活
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sort(Request $request)
    {
        try{
            // 获取集合
            $data = MakeEffectsFile::findOrFail((int)$request -> get('id'));

            // 获取操作类型
            if(!is_numeric($status = $request -> get('status')))
                return response()->json(0);

            switch($status){
                // 上移 升，sort值越小，排序越靠前
                case 1:
                    $sort_data = MakeEffectsFile::where('sort','<',$data -> sort)
                        -> where('recommend',1)
                        -> orderBy('sort','DESC')
                        -> first();
                    break;
                // 下移 降
                case 2:
                    $sort_data = MakeEffectsFile::where('sort','>',$data -> sort)
                        -> where('recommend',1)
                        -> orderBy('sort')
                        -> first();
                    break;
                // 删除，先删除七牛云文件
                case 3:
                    CloudStorage::delete($data -> address);
                    return response()->json($data -> delete());
                // 激活
                case 4:
                    // 如果在激活状态，则返回错误
                    if(1 === $data->active) return response()->json(0);

                    $folder = MakeEffectsFolder::findOrFail($data->folder_id);

                    // 修改文件夹下文件总数量
                    $folder -> update(['count' => ++$folder->count]);

                    return response()->json($data -> update(['active'=>1,'time_update' => getTime()]));
                // 推荐或取消推荐
                case 5 :
                    $data -> recommend = 0 === $data -> recommend ? 1 : 0;

                    // 如果为推荐，则修改sort值
                    if(1 == $data -> recommend){
                        $sort_data = MakeEffectsFile::orderBy('sort','DESC') -> first();
                        $data -> sort = ++$sort_data -> sort;
                    }
                    $data -> save();
                    return response()->json(1);
                    break;
                default:
                    return response()->json(0);
            }

            list($data -> sort,$sort_data -> sort) = [$sort_data -> sort,$data -> sort];

            // 保存
            $data -> save();
            $sort_data -> save();

            return response()->json(1);

        } catch (ModelNotFoundException $e) {

            return response()->json(0);
        } catch (\Exception $e){

            return response()->json(0);
        }
    }

}