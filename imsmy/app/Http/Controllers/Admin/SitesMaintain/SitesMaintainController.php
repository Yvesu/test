<?php

namespace App\Http\Controllers\Admin\SitesMaintain;

use App\Models\SitesMaintain;
use App\Models\SitesConfigManageLog;
use App\Models\SitesConfig;
use Illuminate\Http\Request;
use App\Models\Admin\Administrator;
use App\Http\Controllers\Admin\BaseSessionController;
use CloudStorage;
use Auth;
use Illuminate\Support\Facades\Cache;
use DB;

/**
 * 网站信息配置
 * Class SitesMaintainController
 * @package App\Http\Controllers\Admin\SitesMaintain
 */
class SitesMaintainController extends BaseSessionController
{

    protected $paginate = 10;

    /**
     * 网站信息配置 主页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request){

        try{
            // 搜索条件
            $search = post_check($request -> get('search',''));
            $num = (int)$request->input('num',$this->paginate);

            // 获取集合
            $datas = SitesConfig::orderBy('active','DESC');

            // 是否为搜索
            if($search){

                // 条件
                switch($request -> get('condition')){

                    // 动态ID
                    case 1:
                        $datas = $datas->where('data_id','like','%'.$search.'%');
                        break;

                    // 标题
                    case 2:
                        $datas = $datas->where('title','like','%'.$search.'%');
                        break;

                    // 关键词
                    case 3:
                        $datas = $datas->where('keywords','like','%'.$search.'%');
                        break;
                    default:
                        return back();
                }
            }

            // 筛选集合
            $datas = $datas -> paginate($num);

            // 遍历集合，从local_user表中取数据
            $datas->each(function($data){

                // 获取提交人的名称
                $data -> user_name = Administrator::find($data->admin_id)->name;
            });

            // 搜索类型
            $cond = [1=>'ID',2=>'标题',3=>'关键词'];

            // 设置返回数组
            $res = [
                'condition' => $request -> get('condition'),
                'num'=>$num,
                'search'=>$search,
            ];

            // 返回视图
            return view('admin/sites_maintain/index',[
                'datas'=>$datas,
                'request'=>$res,
                'condition'=>$cond
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 网站配置信息 添加
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add(){

        try{

            // 返回视图
            return view('admin/sites_maintain/add');

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 网站配置信息 详情
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function details(Request $request)
    {
        try {
            // 获取id
            $id = (int)$request -> get('id');

            // 具体详情
            $data = SitesConfig::findOrFail($id);

            // 返回数据
            return view('/admin/sites_maintain/details',['data'=>$data,'active'=>$data -> active]);

        } catch (\Exception $e) {

            abort(404);
        }
    }

    /**
     * 网站配置信息 编辑
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        try {
            // 获取id
            $id = (int)$request -> get('id');

            // 具体详情
            $data = SitesConfig::findOrFail($id);

            // 返回数据
            return view('/admin/sites_maintain/edit',['data'=>$data]);

        } catch (\Exception $e) {

            abort(404);
        }
    }

    /**
     * 网站配置信息 保存
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function insert(Request $request)
    {
        try{

            // 接收
            $title = post_check($request->get('title'));
            $keywords = post_check($request->get('keywords'));
            $icon = $request->file('icon');

            // 判断是否为空
            if(is_null($title) || is_null($icon) || is_null($keywords)){
                return back()->with('error','信息不能为空');
            }

            // 开启事务
            DB::beginTransaction();

            // 创建集合并保存
            $data = SitesConfig::create([
                'title'         => $title,
                'keywords'      => $keywords,
                'admin_id'      => session('admin')->id,
                'icon'          => 'temp_key',
                'hash_icon'     => 'temp_hash',
                'time_add'      => getTime(),
                'time_update'   => getTime(),
            ]);

            // 获取随机数
            $rand = mt_rand(1000000,9999999);

            // 修改上传图片信息
            $result = CloudStorage::putFile(
                'sites/' . $data->id . '/' . getTime() . $rand . '.' . $icon->getClientOriginalExtension(),
                $icon);

            // 判断上传结果
            if($result[1] !== null){

                // 事务回滚
                DB::rollBack();

                return back()->with('error','图片上传失败');

            } else {

                // 修改集合中的图像信息
                $data->hash_icon = $result[0]['hash'];
                $data->icon = $result[0]['key'];

                // 保存
                $data->save();

                // 提交
                DB::commit();
            }

            // 重定向
            return redirect('/admin/maintain/details?id=' . $data->id);
        } catch (\Exception $e) {

            abort(404);
        }
    }

    /**
     * 网站配置信息 更改
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function update(Request $request)
    {
        try{

            // 接收
            $title = post_check($request->get('title'));
            $keywords = post_check($request->get('keywords'));
            $icon = $request->file('icon');
            $id = (int)$request->get('id');

            // 判断是否为空
            if(is_null($title) || is_null($keywords) || !$id){
                return back()->with('error','信息不能为空');
            }

            // 获取原集合
            $data = SitesConfig::findOrFail($id);

            // 写入日志 管理日志
            $config = SitesConfigManageLog::create([
                'admin_id'      => session('admin') -> id,
                'data_id'       => $id,
                'time_add'      => getTime()
            ]);

            // 判断是否有新图片上传
            if($icon){

                // 获取随机数
                $rand = mt_rand(1000000,9999999);

                // 修改上传图片信息
                $result = CloudStorage::putFile(
                    'sites/' . $data->id . '/' . getTime() . $rand . '.' . $icon->getClientOriginalExtension(),
                    $icon);

                // 判断上传结果
                if($result[1] !== null){

                    // 返回错误信息
                    return back()->with('error','图片上传失败');

                } else {

                    // 修改集合中的图像信息
                    $data -> hash_icon = $result[0]['hash'];
                    $data -> icon = $result[0]['key'];

                    // 保存日志
                    $config -> hash_icon = $result[0]['hash'];
                    $config -> icon = $result[0]['key'];
                }
            }

            // 修改标题
            if($data -> title != $title) {

                $data -> title = $title;

                // 保存日志
                $config -> title = $title;
            }

            // 修改关键词
            if($data -> keywords != $keywords) {

                $data -> keywords = $keywords;

                // 保存日志
                $config -> keywords = $keywords;
            }

            $data -> admin_id = session('admin')->id;
            $data -> time_update = getTime();

            // 保存
            $data->save();

            // 保存日志
            $config -> save();

            // 重定向
            return redirect('/admin/maintain/details?id=' . $data->id);
        } catch (\Exception $e) {

            abort(404);
        }
    }

    /**
     * 网站配置信息 审批处理
     */
    public function apply(Request $request)
    {
        try{

            // 接收要操作的 id
            $id = (int)$request -> input('id');

            // 接收视频状态
            $active = (int)$request -> input('active') === 1 ? 1 : 2;

            // 开启事务
            DB::beginTransaction();

            // 处理
            SitesConfig::find($id)->update(['active'=>$active]);

            // 写入日志 管理日志
            SitesConfigManageLog::create([
                'admin_id'      => session('admin') -> id,
                'data_id'       => $id,
                'active'        => $active,
                'time_add'      => getTime()
            ]);

            // 提交事务
            DB::commit();

            //返回处理操作成功信息
            return response() -> json(1);

        }catch(\Exception $e){

            // 事务回滚
            DB::rollBack();

            // 返回处理操作失败信息
            return response() -> json(2);
        }
    }

    /**
     * 开关 编辑页
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function maintain(){

        try{

            $data = SitesMaintain::first();

            // 返回视图
            return view('admin/sites_maintain/maintain',[
                'data'=>$data,
            ]);

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 开关 保存
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function status(Request $request){

        try{

            // 接收状态
            $status = (int)$request -> get('status');

            // 获取表中集合
            $data = SitesMaintain::first();

            // 判断是否有修改
            if($status === $data -> status) return back('error','未做修改');

            // 修改状态信息
            switch($status){
                case 1:
                    $data -> status = 1;
                    break;
                case 2:
                    $data -> status = 2;
                    break;
                default:
                    return back('error','修改失败');
            }

            // 保存集合
            $data -> save();

            // 修改成功，重定向
            return redirect('admin/maintain/index');

        }catch(\Exception $e){

            abort(404);
        }
    }

    /**
     * 一键清除网站缓存
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cache(){

        // 返回视图
        return view('admin/sites_maintain/cache');
    }

    /**
     * 一键清除网站缓存
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cacheFlush(){

        Cache::flush();

        return response()->json(1);
    }

}
