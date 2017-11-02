<?php
namespace App\Http\Controllers\Admin\Creation;

use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\CreationCover;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use Carbon\Carbon;
use DB;

class CoverController extends BaseSessionController
{
    protected $paginate = 20;

    public function index(Request $request)
    {
        try{
            // 搜索条件
            $condition = (int)$request -> get('condition', '');
            $search = post_check($request -> get('search'));
            $num = (int)$request->input('num', $this->paginate);

            // 状态
            $active = $request -> get('active', 1);

            // 判断是否为推荐
            if(3 == $active){

                // 获取集合
                $datas = CreationCover::whereActive(1)
                    -> where('recommend',1)
                    -> orderBy('id', 'DESC')
                    -> ofSearch($search,$condition)
                    -> paginate($num);

            } else {

                // 获取集合
                $datas = CreationCover::whereActive($active)
                    -> ofSearch($search,$condition)
                    -> orderBy('id', 'DESC')
                    -> paginate($num);
            }

            // 搜索类型
            $cond = [1=>'ID',2=>'名称'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'       =>$num,
                'search'    =>$search,
                'active'    => $active,
            ];

            // 返回视图
            return view('admin/creation/cover/index',[
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

    public function add()
    {
        return view('admin/creation/cover/add');
    }

    /**
     *   保存提交的封面
     */
    public function insert(Request $request)
    {

        // 获取所有输入的
        $input = $request -> all();

        $icon = $request->file('topic-icon');

        $time = getTime();

        // 获取图片尺寸
        $size = getimagesize($icon)[0].'*'.getimagesize($icon)[1];

        // 获取随机数
        $rand = mt_rand(10000,99999);

        // 判断时间是否都存在
        if(!$input['from_date'] || !$input['from_time']) return back()->with(['error'=>'时间不允许为空']);

        # 有效期
        // 起始时间
        $from_time = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['from_date'] . ' ' . $input['from_time']));

        // 终止时间
        $end_time = strtotime(Carbon::createFromFormat('Y-m-d H:i', $input['end_date'] . ' ' . $input['end_time']));

        DB::beginTransaction();

        // 存储数据
        $cover = CreationCover::create([
            'user_id'           => session('admin')->id,
            'name'              => post_check($input['name']),
            'from_time'         => $from_time,
            'end_time'          => $end_time,
            'time_add'          => $time,
            'time_update'       => $time,
        ]);

        // 上传文件，并重新命名
        $result = CloudStorage::putFile(
            'creation/cover/' . $cover->id . '/' . $time . $rand.'_'.$size.'_.'.$icon->getClientOriginalExtension(),
            $icon);

        // 判断是否上传成功，并提交
        if($result[1] !== null){
            DB::rollBack();
        } else {
            $cover->cover = $result[0]['key'];
            $cover->save();
            DB::commit();
        }

        return redirect('/admin/creation/cover/index');
    }

    /**
     * 文件 删除/激活/推荐默认
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sort(Request $request)
    {
        try{

            // 获取集合
            $data = CreationCover::findOrFail((int)$request -> get('id'));

            // 获取操作类型
            if(!is_numeric($status = $request -> get('status')))
                return response()->json(0);

            $time = getTime();

            switch($status){

                // 删除，先删除七牛云文件
                case 3:
                    // 判断为哪一种删除，如果active为1，则改为2，如果为2，则彻底删除
                    if(1 == $data -> active) {
                        return response()->json($data -> update([
                            'active'      => 2,
                            'default'     => 0,
                            'time_update' => $time,
                        ]));
                    } else {
                        CloudStorage::delete($data -> address);
                        return response()->json($data -> delete());
                    }

                // 激活
                case 4:
                    return response()->json($data -> update(['active'=>1]));

                // 设置为默认封面，原有封面则撤换
                case 5:
                    CreationCover::where('default', 1) -> update(['default' => 0]);
                    return response()->json($data -> update([
                        'default'     => 1,
                        'time_update' => $time,
                    ]));
                default:
                    return response()->json(0);
            }

        } catch (\Exception $e) {

            return response()->json(0);
        }
    }

}