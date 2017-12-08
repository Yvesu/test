<?php

namespace App\Http\Controllers\Admin\App\Make;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Admin\BaseSessionController;
use Illuminate\Http\Request;
use App\Models\Make\{MakeFilterFile,MakeFilterFolder};
use CloudStorage;
use Auth;
use DB;

class MakeFilterFileController extends BaseSessionController
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
                $search = MakeFilterFolder::where('name',$search)->firstOrFail()->id;

            // 判断是否为推荐
            if(3 == $active){

                // 获取集合
                $datas = MakeFilterFile::whereActive(1)
                    -> where('recommend',1)
                    -> orderBy('sort')
                    -> ofSearch($search,$condition)
                    -> paginate($num);
            } else {

                // 获取集合
                $datas = MakeFilterFile::whereActive($active)
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
            return view('admin/app/make/filter/file/index',[
                'datas'     => $datas,
                'request'   => $res,
                'condition' => $cond,
                'folder'    => MakeFilterFolder::pluck('name','id')
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
        return view('admin/app/make/filter/file/add',[
            'folder'=>MakeFilterFolder::active()->orderBy('sort')->get(['id','name'])
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
                'content'   => 'required',
                'folder_id' => 'required|numeric',
            ],
                [
                    'name.required'         => '名称不能为空',
                    'name.max'              => '名称长度小于255位',
                    'content.required'      => '参数不能为空',
                    'folder_id.required'    => '所属目录不能为空',
                ]);

            $screen_shot = $request->file('video-icon');

            // 获取上传截图的宽高
            $shot_width_height = getimagesize($screen_shot)[0].'*'.getimagesize($screen_shot)[1];

            // 获取所有数据
            $input = $request -> all();

            $time = getTime();

            // 开启事务
            DB::beginTransaction();

            $file = MakeFilterFile::create([
                'name'          =>  $input['name'],
                'content'       =>  $input['content'],
                'user_id'       =>  session('admin')->user_id,
                'folder_id'     =>  $input['folder_id'],
                'time_add'      =>  $time,
                'time_update'   =>  $time,
            ]);

            // 新代码 第一个参数：上传到七牛后保存的文件名，第二个参数：要上传文件的本地路径
            $result = CloudStorage::putFile(
                'filter/'.$file->id.'/'.str_random(10).$time.'_'.$shot_width_height.'_.'.$screen_shot->getClientOriginalExtension(),
                $screen_shot
            );

            // 封面
            $file -> cover = $result[0]['key'];

            $file -> save();

            // 事务提交
            DB::commit();

            return redirect('admin/make/filter/file/index?active=0');

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
            $data = MakeFilterFile::findOrFail((int)$request -> get('id'));

            // 获取操作类型
            if(!is_numeric($status = $request -> get('status')))
                return response()->json(0);

            switch($status){
                // 上移 升，sort值越小，排序越靠前
                case 1:
                    $sort_data = MakeFilterFile::where('sort','<',$data -> sort)
                        -> where('recommend',1)
                        -> orderBy('sort','DESC')
                        -> first();
                    break;
                // 下移 降
                case 2:
                    $sort_data = MakeFilterFile::where('sort','>',$data -> sort)
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

                    $folder = MakeFilterFolder::findOrFail($data->folder_id);

                    // 修改文件夹下文件总数量
                    $folder -> update(['count' => ++$folder->count]);

                    return response()->json($data -> update(['active'=>1,'time_update' => getTime()]));
                // 推荐或取消推荐
                case 5 :
                    $data -> recommend = 0 === $data -> recommend ? 1 : 0;

                    // 如果为推荐，则修改sort值
                    if(1 == $data -> recommend){
                        $sort_data = MakeFilterFile::orderBy('sort','DESC') -> first();
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