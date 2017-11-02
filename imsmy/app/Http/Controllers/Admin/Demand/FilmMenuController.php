<?php

namespace App\Http\Controllers\Admin\Demand;

use App\Models\FilmMenu;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Admin\BaseSessionController;
use Auth;
use DB;

/**
 * 影片种类管理模块
 * Class DemandMenuController
 * @package App\Http\Controllers\Admin\Demand
 */
class FilmMenuController extends BaseSessionController
{
    // 每页条数
    protected $paginate = 20;

    /**
     * 影片种类列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try{
            // 搜索条件
            $condition = $request -> get('condition');
            $search = $request -> get('search');

            if(!is_numeric($active = $request -> get('active',1)))
                abort(404);

            $datas = FilmMenu::where('active',$active);

            if($search){

                switch($condition){
                    // id
                    case 1:
                        if(!is_numeric($search)) return back();
                        $datas = $datas -> where('id',$search);
                        break;
                    // 名称
                    case 2:
                        $datas = $datas -> where('name','like','%'.post_check($search).'%');
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> orderBy('sort','DESC') -> paginate((int)$request->input('num',$this->paginate));

            // 搜索条件
            $cond = [1=>'用户',2=>'名称'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',$this->paginate),
                'search'=>$search,
                'active'=>$active,
            ];

            // 返回视图
            return view('/admin/film/index',['datas'=>$datas,'request'=>$res,'condition'=>$cond]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     *  影片种类添加
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        return view('/admin/film/add');
    }

    /**
     * 影片种类添加动作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function insert(Request $request)
    {
        try{

            //名称,描述 不能为空
            $this->validate($request, [
                'name' => 'required',
            ],
                [
                    'name.required' => '菜单名称不能为空',
                ]
            );

            $name = $request->input('name');

            // 判断名称是否已经存在
            if(FilmMenu::where('name',$name)->first())
                return back()->with('error','名称已经存在');

            //添加
            FilmMenu::create([
                'name' => $name,
                'time_add'=>getTime(),
                'time_update'=>getTime(),
            ]);

            // 返回
            return redirect('/admin/config/film/index')->with('success','添加成功');
        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 编辑
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        try{

            // 要编辑的id
            if(!$id = (int)$request -> get('id')) return back('error','id不能为空');

            // 返回视图
            return view('/admin/film/edit', [
                'one' => FilmMenu::findOrFail($id),
                'status' => [1=>'生效',0=>'失效'],
            ]);

        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 修改信息
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try{
            //名称,描述 不能为空
            $this->validate($request, [
                'id'=> 'required',
                'name' => 'required',
                'active' => 'required',
            ],
                [
                    'id.required' => 'id不能为空',
                    'name.required' => '名称不能为空',
                    'active.required' => '状态不能为空',
                ]
            );

            // 获取集合
            $data = FilmMenu::findOrFail((int)$request -> get('id'));

            // 修改状态
            $active = (int)$request->input('active');

            // 如果状态做了修改
            if($data -> active != $active){

                // 修改状态
                $data -> active = $active === 1 ? 1 : 0;
            }

            // 修改名称
            $name = post_check($request->input('name'));

            // 判断是否编辑
            if($data -> name != $name){

                // 判断名称是否已经存在
                if(FilmMenu::where('name',$name)->first())
                    return back()->with('error','名称已经存在');

                $data -> name = $name;
            }

            // 保存
            $data -> save();

            // 判断
            return redirect('/admin/config/film/index')->with('success', '编辑成功');

        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 修改影片种类 排序/删除/激活
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sort(Request $request)
    {
        try{

            // 获取集合
            $data = FilmMenu::findOrFail((int)$request -> get('id'));

            // 获取操作类型
            if(!is_numeric($status = $request -> get('status')))
                return response()->json(0);

            switch($status){
                // 上移
                case 1:
                    $id = FilmMenu::where('sort','>',$data -> sort)
                        -> orderBy('sort')
                        -> first();
                    break;
                // 下移
                case 2:
                    $id = FilmMenu::where('sort','<',$data -> sort)
                        -> orderBy('sort','DESC')
                        -> first();
                    break;
                // 删除
                case 3:
                    return response()->json($data -> delete());
                // 激活
                case 4:
                    return response()->json($data -> update(['active'=>1]));
                default:
                    return response()->json(0);
            }

            // 获取要更换顺序的集合
            $role_type = FilmMenu::findOrFail($id->id);

            list($data -> sort,$role_type -> sort) = [$role_type -> sort,$data -> sort];

            // 保存
            $data -> save();
            $role_type -> save();

            return response()->json(1);

        }catch(\Exception $e){

            return response()->json(0);
        }
    }
}
