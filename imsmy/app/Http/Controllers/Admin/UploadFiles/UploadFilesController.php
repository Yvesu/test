<?php

namespace App\Http\Controllers\Admin\UploadFiles;

use App\Http\Controllers\Admin\BaseSessionController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\UploadFiles;
use CloudStorage;
use DB;

/**
 * 上传文件管理
 */
class UploadFilesController extends BaseSessionController
{

    protected $paginate = 20;

    /**
     * 文件主页
     */
    public function index(Request $request)
    {

        try {

            // 搜索条件
            $condition = (int)$request->get('condition', '');
            $search = $request->get('search', '');

            // 获取动态
            $data = UploadFiles::ofSearch($request->get('search'))
                -> orderBy('id')
                -> paginate((int)$request->input('num', $this->paginate));

            // 搜索条件
            $cond = [1 => '名称'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num' => $request->input('num', $this->paginate),
                'search' => $search,
            ];

            return view('admin/file/index')
                ->with([
                    'data' => $data,
                    'request' => $res,
                    'condition' => $cond,
                ]);

        } catch (\Exception $e) {

            // 404报错
            abort(404);
        }
    }

    /**
     * 上传文件
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        return view('admin/file/add');
    }

    /**
     * 保存发布的文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function insert(Request $request)
    {
        try {

            // 验证输入信息
            $this->validate($request,[
                'name'=>'required',
                'key'=>'required',
            ],[
                'name' => '名称不能为空',
                'key.required' => '未上传文件',
            ]);


            // 验证用户登录状态
            if(!session('admin'))
                abort(404);

            $time = getTime();

            DB::beginTransaction();

            $file = UploadFiles::create([
                'name'          => $request -> get('name'),
                'url'           => $request -> get('key'),
                'time_add'      => $time,
                'time_update'   => $time,
            ]);

            // 新名字
            $new_key = 'file/' . $file->id . '/' . $time.mt_rand(100000,999999).'.'.pathinfo($file->url,PATHINFO_EXTENSION);

            CloudStorage::rename($file->url, $new_key);

            // 修改地址
            $file -> url = $new_key;

            $file -> save();

            DB::commit();

            return redirect('admin/file/index');

        } catch (\Exception $e) {
            DB::rollBack();
            abort(404);
        }
    }

    /**
     * 文件操作 删除
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        try{
            if(!is_numeric($id = $request -> get('id')))
                return response()->json(0);

            // 获取集合
            $data = UploadFiles::findOrFail($id);

            // 获取操作类型
            if(!is_numeric($status = $request -> get('status')))
                return response()->json(0);

            switch($status){
                // 删除
                case 3:
                    CloudStorage::delete($data->url);
                    return response()->json($data -> delete());
                default:
                    return response()->json(0);
            }

        }catch(\Exception $e){

            return response()->json(0);
        }
    }
}
