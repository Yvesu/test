<?php

namespace App\Http\Controllers\Admin\UserRole;

use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Role\UserRole;
use App\Models\Role\UserRoleType;
use Illuminate\Http\Request;
use App\Models\FilmMenu;
use App\Http\Requests;
use CloudStorage;
use DB;

/**
 * 用户角色管理
 *
 * Class UserRole
 * @package App\Http\Controllers\Admin\UserRole
 */
class UserRoleController extends BaseSessionController
{
    // 每页条数
    protected $paginate = 20;

    /**
     * 主界面
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        try{
            // 搜索条件
            $condition = $request -> get('condition');
            $search = $request -> get('search');

            if(!is_numeric($active = $request -> get('active',1)))
                abort(404);

            $datas = UserRole::with('hasOneFilm')->where('active',$active);

            if($search){

                switch($condition){
                    // 用户id
                    case 1:
                        if(!is_numeric($search)) return back();
                        $datas = $datas -> where('user_id',$search);
                        break;
                    // 剧名
                    case 2:
                        $datas = $datas -> where('title','like','%'.post_check($search).'%');
                        break;
                    // 影片
                    case 3:
                        // 获取影片 id
                        $film_id = FilmMenu::where('name',post_check($search))->first();
                        $datas = $datas->where('film_id',$film_id->id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> orderBy('id','DESC') -> paginate((int)$request->input('num',$this->paginate));

            // 搜索条件
            $cond = [1=>'用户',2=>'剧名',3=>'影片'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',$this->paginate),
                'search'=>$search,
                'active'=>$active,
            ];

            // 返回视图
            return view('/admin/user_role/release/index',['datas'=>$datas,'request'=>$res,'condition'=>$cond]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 详情页
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function details(Request $request)
    {
        try {
            // 话题基本信息
            $data = UserRole::with('hasOneIntro','hasOneFilm','hasManyDetails.hasOneBiography','hasManyDetails.belongsToType','hasManyAudition')
                ->findOrFail((int)$request->get('id'));

            return view('admin/user_role/release/details',['data'=>$data]);

        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * 修改状态信息
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try{

            // 获取集合
            $data = UserRole::findOrFail((int)$request -> get('id'));

            // 修改状态
            $active = (int)$request->input('active');

            // 如果状态做了修改
            if($data -> active != $active){

                // 修改状态
                $data -> active = $active === 1 ? 1 : 2;

                // 保存
                $data -> save();
            }

            // 判断
            return redirect('/admin/user/role/details?id='.$request -> get('id'))->with('success', '修改成功');

        }catch(\Exception $e){
            abort(404);
        }
    }

    /**
     * 用户角色类型管理
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function type(Request $request)
    {

        try{
            // 搜索条件
            $condition = $request -> get('condition');
            $search = $request -> get('search');

            if(!is_numeric($active = $request -> get('active',1)))
                abort(404);

            $datas = UserRoleType::where('active',$active);

            if($search){

                switch($condition){
                    // 名称
                    case 1:
                        $datas = $datas -> where('name','like','%'.post_check($search).'%');
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> orderBy('sort','DESC') -> paginate((int)$request->input('num',$this->paginate));

            // 搜索条件
            $cond = [1=>'名称'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',$this->paginate),
                'search'=>$search,
                'active'=>$active,
            ];

            // 返回视图
            return view('/admin/user_role/role_type/index',['datas'=>$datas,'request'=>$res,'condition'=>$cond]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 修改角色类型 排序/删除/激活
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sort(Request $request)
    {
        try{

            // 获取集合
            $data = UserRoleType::findOrFail((int)$request -> get('id'));

            // 获取操作类型
            if(!is_numeric($status = $request -> get('status')))
                return response()->json(0);

            switch($status){
                // 上移
                case 1:
                    $id = UserRoleType::where('sort','>',$data -> sort)
                        -> orderBy('sort')
                        -> first();
                    break;
                // 下移
                case 2:
                    $id = UserRoleType::where('sort','<',$data -> sort)
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
            $role_type = UserRoleType::findOrFail($id->id);

            list($data -> sort,$role_type -> sort) = [$role_type -> sort,$data -> sort];

            // 保存
            $data -> save();
            $role_type -> save();

            return response()->json(1);

        }catch(\Exception $e){

            return response()->json(0);
        }
    }

    /**
     * 添加角色类型
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function typeAdd()
    {
        return view('admin/user_role/role_type/add');
    }

    /**
     * 保存角色类型
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function typeInsert(Request $request)
    {
        try{

            $name = post_check($request -> get('name'));

            // 查询是否已经存在
            if(UserRoleType::where('name',$name)->first())
                return back()->with(['error'=>'已经存在']);

            // 获取最大sort
            $sort = UserRoleType::orderBy('sort','DESC')->first();

            $time = getTime();

            // 保存
            UserRoleType::create([
                'name'          => $name,
                'sort'          => ++$sort -> sort,
                'time_add'      => $time,
                'time_update'   => $time
            ]);

            return redirect('/admin/user/role/type');

        }catch(\Exception $e){

            abort(404);
        }
    }

}
