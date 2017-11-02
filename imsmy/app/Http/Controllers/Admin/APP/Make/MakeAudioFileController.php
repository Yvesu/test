<?php

namespace App\Http\Controllers\Admin\App\Make;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Admin\BaseSessionController;
use Illuminate\Http\Request;
use App\Models\Make\{MakeAudioFile,MakeAudioFolder};
use CloudStorage;
use Auth;
use DB;

class MakeAudioFileController extends BaseSessionController
{
    protected $paginate = 20;

    /**
     * 音频主页
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
            $active = $request -> get('active',1);

            // 判断是否为搜索目录
            if(4 == $condition)
                $search = MakeAudioFolder::where('name',$search)->firstOrFail()->id;

            // 获取集合
            $datas = MakeAudioFile::whereActive($active)->ofSearch($search,$condition) -> paginate($num);

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
            return view('admin/app/make/audio/file/index',[
                'datas'     => $datas,
                'request'   => $res,
                'condition' => $cond,
                'folder'    => MakeAudioFolder::pluck('name','id')
            ]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * 添加音频文件
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        return view('admin/app/make/audio/file/add',[
            'folder'=>MakeAudioFolder::active()->orderBy('sort')->get(['id','name'])
        ]);
    }

    /**
     * 保存音频文件
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
                'folder_id' => 'required|numeric',
            ],
            [
                'name.required'     => '名称不能为空',
                'name.max'          => '名称长度小于255位',
                'intro.required'    => '介绍不能为空',
                'intro.max'         => '介绍长度小于255位',
                'key.required'      => '上传文件不能为空',
                'folder_id.required'=> '所属目录不能为空',
            ]);

            // 获取所有数据
            $input = $request -> all();

            // 获取上传文件的各类格式信息
            $format = json_decode(file_get_contents(CloudStorage::downloadUrl($input['key']).'?avinfo'))->format;

            // 获取音频文件的时长
            $duration = $format -> duration;

            $time = getTime();

            $file = MakeAudioFile::create([
                'name'          =>  $input['name'],
                'intro'         =>  $input['intro'],
                'user_id'       =>  session('admin')->user_id,
                'folder_id'     =>  $input['folder_id'],
                'duration'      =>  $duration,
                'audition_address'       =>  $input['key'],
                'address'       =>  $input['key'],
                'time_add'      =>  $time,
                'time_update'   =>  $time,
            ]);

            // 音频新名称  TODO audition_address试听地址及积分待完善
            $new_name = 'audio/'.$file->id.'/'.str_random(10).$time.'.'.$format->format_name;

            // 对上传音频进行重命名
            CloudStorage::rename($input['key'],$new_name);

            $file -> address = $new_name;
            $file -> audition_address = $new_name;
            $file -> save();

            return redirect('admin/make/audio/file/index');
        } catch (ModelNotFoundException $e){
            DB::rollback();
            abort(404);
        } catch (\Exception $e){
            DB::rollback();
            abort(404);
        }
    }

    /**
     * 音频文件 删除/激活
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sort(Request $request)
    {
        try{

            // 获取集合
            $data = MakeAudioFile::findOrFail((int)$request -> get('id'));

            // 获取操作类型
            if(!is_numeric($status = $request -> get('status')))
                return response()->json(0);

            switch($status){
                // 删除，先删除七牛云文件
                case 3:
                    CloudStorage::delete($data -> address);
                    return response()->json($data -> delete());
                // 激活
                case 4:
                    // 如果在激活状态，则返回错误
                    if(1 === $data->active) return response()->json(0);

                    $folder = MakeAudioFolder::findOrFail($data->folder_id);

                    // 修改文件夹下文件总数量
                    $folder -> update(['count' => ++$folder->count,'time_update' => getTime()]);

                    return response()->json($data -> update(['active'=>1]));
                default:
                    return response()->json(0);
            }

        }catch(\Exception $e){

            return response()->json(0);
        }
    }

}