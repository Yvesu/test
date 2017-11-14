<?php
namespace App\Http\Controllers\Admin\App\Make;

use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Admin\BaseSessionController;
use Illuminate\Http\Request;
use App\Models\Make\{MakeFontFile};
use CloudStorage;
use Auth;
use DB;

class MakeFontFileController extends BaseSessionController
{
    protected $paginate = 20;

    /**
     * 字体主页
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

            // 获取集合
            $datas = MakeFontFile::whereActive($active)->ofSearch($search,$condition) -> paginate($num);

            // 搜索类型
            $cond = [1=>'ID',2=>'名称'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$num,
                'search'=>$search,
                'active'    => $active,
            ];

            // 返回视图
            return view('admin/app/make/font/file/index',[
                'datas'     => $datas,
                'request'   => $res,
                'condition' => $cond,
            ]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * 添加字体文件
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        return view('admin/app/make/font/file/add');
    }

    /**
     * 保存字体文件
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function insert(Request $request)
    {
        try{

            // 检测上传文件
            $this -> validate($request,[
                'name'      => 'required|max:255',
                'key'       => 'required',
            ],
                [
                    'name.required'     => '名称不能为空',
                    'name.max'          => '名称长度小于255位',
                    'key.required'      => '上传文件不能为空',
                ]);

            $screen_shot = $request->file('font-icon');

            // 获取上传截图的宽高
            $shot_width_height = getimagesize($screen_shot)[0].'*'.getimagesize($screen_shot)[1];

            // 获取所有数据
            $input = $request -> all();

            // 获取上传文件的各类格式信息 时长/大小  TODO 七牛暂时无法查询字体文件信息
//            $format = json_decode(file_get_contents(CloudStorage::downloadUrl($input['key']).'?avinfo'))->format;

            $time = getTime();

            $sort = MakeFontFile::orderBy('sort','DESC')->first();

            // 开启事务
            DB::beginTransaction();

            $file = MakeFontFile::create([
                'name'          =>  $input['name'],
                'user_id'       =>  session('admin')->user_id,
                'size'          =>  0,  // 七牛暂时无法查询字体文件
                'address'       =>  $input['key'],
                'sort'          =>  $sort ? (++$sort->sort) : 1,
                'time_add'      =>  $time,
                'time_update'   =>  $time,
            ]);

            // 新代码 第一个参数：上传到七牛后保存的文件名，第二个参数：要上传文件的本地路径
            $result = CloudStorage::putFile(
                'font/'.$file->id.'/'.str_random(10).$time.'_'.$shot_width_height.'_.'.$screen_shot->getClientOriginalExtension(),
                $screen_shot
            );

            // 封面
            $file -> cover = $result[0]['key'];

            // 字体新名称
            $new_name = 'font/'.$file->id.'/'.str_random(10).$time.'.'.pathinfo($file->address,PATHINFO_EXTENSION);

            // 对上传字体进行重命名
            CloudStorage::rename($input['key'],$new_name);

            $file -> address = $new_name;
            $file -> save();

            // 事务提交
            DB::commit();

            return redirect('admin/make/font/file/index?active=0');

        } catch (ModelNotFoundException $e){
            DB::rollback();
            abort(404);
        } catch (ValidationException $e){
            DB::rollback();
            abort(404);
        }
    }

    /**
     * 字体文件 排序/删除/激活
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sort(Request $request)
    {
        try{

            $id = (int)$request -> get('id');

            // 获取集合
            $data = MakeFontFile::findOrFail($id);

            // 获取操作类型
            if(!is_numeric($status = $request -> get('status')))
                return response()->json(0);

            switch($status){
                // 升序
                case 1:
                    $next = MakeFontFile::where('sort','<',$data->sort)->first();
                    break;
                // 降序
                case 2:
                    $next = MakeFontFile::where('sort','>',$data->sort)->first();
                    break;
                // 删除，先删除七牛云文件
                case 3:
                    CloudStorage::delete($data -> address);
                    return response()->json($data -> delete());
                // 激活
                case 4:
                    // 如果在激活状态，则返回错误
                    if(1 === $data->active) return response()->json(0);

                    return response()->json($data -> update(['active'=>1,'time_update' => getTime()]));
                default:
                    return response()->json(0);
            }

            // 排序 成功
            if($next){
                list($data->sort,$next->sort) = [$next->sort,$data->sort];

                $data -> save();
                $next -> save();

                return response()->json(1);
            }

            return response()->json(0);

        }catch(\Exception $e){

            return response()->json(0);
        }
    }

}