<?php

namespace App\Http\Controllers\Admin\Cinema;

use App\Models\Discover\Cinema;
use App\Models\Discover\CinemaPicture;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Admin\BaseSessionController;
use CloudStorage;
use Auth;
use DB;

/**
 * 发现页面院线附图管理模块
 * 
 * Class CinemaPictureController
 * @package App\Http\Controllers\Admin\Cinema
 */
class CinemaPictureController extends BaseSessionController
{
    protected $paginate = 20;

    /**
     * 院线附图列表
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
            $datas = CinemaPicture::with('hasOneCinema')->where('active',$active);

            // 是否为搜索
            if($search){

                // 条件
                switch($condition){
                    // id
                    case 1:
                        $datas = $datas -> where('id',(int)$search);
                        break;
                    // 院线名称
                    case 2:
                        // 院线id
                        $cinema = Cinema::where('name','like','%'.post_check($search).'%')->first();
                        $cinema && $datas = $datas -> where('film_id',$cinema->id);
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> orderBy('id','DESC') -> paginate((int)$request->input('num',$this->paginate));

            // 搜索条件
            $cond = [1=>'ID',2=>'院线名称'];

            // 设置返回数组
            $res = [
                'condition' => $condition,
                'num'=>$request->input('num',$this->paginate),
                'search'=>$search,
                'style'=>$active,
            ];

            // 加载视图
            return view('/admin/cinema/picture/index', [
                'datas' => $datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 院线附图添加页面
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        // 获取影院类型
        $cinema = Cinema::active()->get(['id','name']);

        return view('/admin/cinema/picture/add',['datas'=>$cinema]);
    }

    /**
     * 院线附图详情页
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
            $data = CinemaPicture::with('hasOneCinema')->findOrFail($id);

            // 加载视图
            return view('/admin/cinema/picture/details', [
                'data' => $data
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 院线附图添加保存
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function insert(Request $request)
    {
        try{

            //名称,描述 不能为空
            $this->validate($request, [
                'cinema' => 'required',
            ],
                [
                    'cinema.required' => '影院类型为空',
                ]
            );

            // 获取名称及介绍信息
            if(!is_numeric($film_id = $request->input('cinema'))) return back();

            // 验证院线是否存在
            Cinema::findOrFail($film_id);

            // 提交时间
            $time = getTime();

            // 保存
            $cinema = CinemaPicture::create([
                'film_id'     => $film_id,
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

            $cinema -> picture = $result[0]['key'];
            $cinema->save();

            // 返回
            return redirect('/admin/cinema/picture/index')->with('success','添加成功');
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
            return view('/admin/cinema/picture/edit', [
                'data' => CinemaPicture::findOrFail($id),
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
            $data = CinemaPicture::findOrFail($id);

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
                return redirect('/admin/cinema/picture/index');
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
            return redirect('/admin/cinema/picture/details?id='.$data -> id)->with('success', '编辑成功');

        }catch(\Exception $e){
            abort(404);
        }
    }
}
