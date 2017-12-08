<?php

namespace App\Http\Controllers\Admin\Cinema;

use App\Models\Discover\Cinema;
use App\Models\UserDemandJob;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Admin\BaseSessionController;
use CloudStorage;
use Auth;
use DB;

/**
 * 发现页面院线管理模块
 * 
 * Class CinemaController
 * @package App\Http\Controllers\Admin\Cinema
 */
class CinemaController extends BaseSessionController
{
    protected $paginate = 20;

    /**
     * 院线列表
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

            // 是否屏蔽
            $active = $request->get('style',1) == 1 ? 1 : 0;

            // 获取集合
            $datas = Cinema::where('active',$active);

            // 是否为搜索
            if($search){

                // 条件
                switch($condition){
                    // id
                    case 1:
                        $datas = $datas -> where('id',(int)$search);
                        break;
                    // 名称
                    case 2:
                        // 名称
                        $datas = $datas -> where('name','like','%'.post_check($search).'%');
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> orderBy('id','DESC') -> paginate((int)$request->input('num',$this->paginate));

            // 搜索条件
            $cond = [1=>'ID',2=>'名称'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',$this->paginate),
                'search'=>$search,
                'style'=>$active,
            ];

            // 加载视图
            return view('/admin/cinema/manage/index', [
                'datas' => $datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 院线添加页面
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        return view('/admin/cinema/manage/add');
    }

    /**
     * 院线详情页
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function details(Request $request)
    {
        try{

            // 获取id
            if(!is_numeric($id = $request -> get('id'))) return back();

            // 筛选集合
            $data = Cinema::findOrFail($id);

            // 加载视图
            return view('/admin/cinema/manage/details', [
                'data' => $data
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 院线添加保存
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function insert(Request $request)
    {
        try{

            //名称,描述 不能为空
            $this->validate($request, [
                'name' => 'required',
                'intro' => 'required',
            ],
                [
                    'name.required' => '名称不能为空',
                    'intro.required' => '介绍不能为空',
                ]
            );

            // 获取名称及介绍信息
            $name = post_check($request->input('name'));
            $intro = post_check($request->input('intro'));

            // 判断名称是否已经存在
            if(Cinema::where('name',$name)->first())
                return back()->with('error','名称已经存在');

            // 提交时间
            $time = getTime();

            // 保存
            $cinema = Cinema::create([
                'name'        => $name,
                'intro'       => $intro,
                'time_add'    => $time,
                'time_update' => $time,
            ]);

            // 背景图上传
            $background = $request -> file('background');

            // 获取图片尺寸
            $size = getimagesize($background)[0].'*'.getimagesize($background)[1];

            // 上传文件，并重新命名
            $result = CloudStorage::putFile(
                'cinema/' . $cinema->id . '/' . str_random() .'_'.$size.'_.'.$background->getClientOriginalExtension(),
                $background);

            $cinema->background_image = $result[0]['key'];
            $cinema->save();

            // 返回
            return redirect('/admin/cinema/index')->with('success','添加成功');
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

            // 获取id
            if(!is_numeric($id = $request -> get('id'))) return back();

            // 返回视图
            return view('/admin/cinema/manage/edit', [
                'data' => Cinema::findOrFail($id),
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

            // 获取id
            if(!is_numeric($id = $request -> get('id'))) return back();

            // 集合
            $data = Cinema::findOrFail($id);

            if($request->get('active')){

                // 如果状态做了修改
                if(1 == $request->input('active')){

                    // 修改状态
                    $data -> active = 1;

                    // 保存
                    $data -> save();
                }else{

                    // 删除
                    $data -> delete();
                }

                // 重定向
                return redirect('/admin/cinema/manage/index');
            }

            //名称,描述 不能为空
            $this->validate($request, [
                'id'=> 'required',
                'name' => 'required',
            ],
                [
                    'id.required' => 'id不能为空',
                    'name.required' => '名称不能为空',
                ]
            );

            // 修改名称
            $name = post_check($request->input('name'));

            // 判断是否编辑
            if($data -> name != $name){

                // 修改
                $data -> name = $name;
            }

            // 修改简介
            $data -> intro = post_check($request->input('intro'));

            // 背景图上传
            $background = $request -> file('background');

            if(isset($background)){

                // 获取旧文件key
                $key = $data -> background_image;

                // 获取新图片尺寸
                $size = getimagesize($background)[0].'*'.getimagesize($background)[1];

                // 上传文件，并重新命名
                $result = CloudStorage::putFile(
                    'cinema/' . $data -> id . '/' . str_random() .'_'.$size.'_.'.$background->getClientOriginalExtension(),
                    $background);

                // 保存
                $data -> background_image = $result[0]['key'];

                // 删除旧文件
                CloudStorage::delete($key);
            }

            // 保存
            $data -> save();

            // 判断
            return redirect('/admin/cinema/manage/details?id='.$data -> id)->with('success', '编辑成功');

        }catch(\Exception $e){
            abort(404);
        }
    }
}
