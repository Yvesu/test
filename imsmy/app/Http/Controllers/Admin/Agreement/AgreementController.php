<?php
namespace App\Http\Controllers\Admin\Agreement;

use App\Models\Agreement;
use App\Http\Controllers\Admin\BaseSessionController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Auth;

class AgreementController extends BaseSessionController
{
    public $paginate = 20;
    public $type = [1=>'注册协议',2=>'举报规范',3=>'发起需求协议',4=>'投资协议',5=>'租赁协议',6=>'发布角色协议',7=>'活动规则'];

    public function index(Request $request)
    {
        // 搜索条件
        $condition = (int)$request -> get('condition','');
        $search = post_check($request -> get('search'));
        $num = (int)$request->input('num',$this->paginate);

        // 获取集合
        $datas = Agreement::orderBy('id','desc');

        // 是否为搜索
        if($search){

            // 条件
            switch($condition){
                // id
                case 1:
                    $datas = $datas->where('id','like','%'.$search.'%');
                    break;
                // 标题
                case 2:
                    $datas = $datas->where('title','like','%'.$search.'%');
                    break;
                // 类型
                case 3:
                    $datas = $datas->where('type','like','%'.$search.'%');
                    break;
                default:
                    return back();
            }
        }

        // 筛选集合
        $datas = $datas -> paginate($num);

        // 搜索类型
        $cond = [1=>'ID',2=>'标题',3=>'类型'];

        // 登录用户
        $user = Auth::guard('web')->user();

        // 设置返回数组
        $res = [
            'condition' => $condition,
            'num'=>$num,
            'search'=>$search,
        ];

        // 获取类型
        foreach($datas as $key=>$data){
            $data -> type = $this->type[$data -> type];
        }

        // 返回视图
        return view('admin/agreement/index',[
            'user'=>$user,
            'datas'=>$datas,
            'request'=>$res,
            'condition'=>$cond
        ]);
    }

    public function add()
    {

        return view('admin/agreement/add',['type'=>$this->type]);
    }

    public function insert(Request $request)
    {
        try {

            //验证信息是否填写
            $this->validate($request, [
                'title' => 'required',
                'content' => 'required',
                'type' => 'required|numeric',
            ],
                [
                    'title.required' => '标题不能为空',
                    'content.required' => '内容不能为空',
                ]);

            $content = post_check($request->get('content'));

            $time = getTime();

            $newAgreement = [
                'title'         => $request->get('title'),
                'time_add'      => $time,
                'content'       => $content,
                'time_update'   => $time,
                'type'          => $request->get('type'),
            ];

            // 保存
            $data = Agreement::create($newAgreement);

        } catch (ValidationException $e) {

            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

        return redirect('/admin/agreement/details?id='.$data -> id);
    }

    /**
     * 详情页面
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function details(Request $request)
    {
        try {
            // 接收要操作的视频id
            if(!is_numeric($id = $request -> get('id')))
                abort(404);

            // 获取视频集合
            $data = Agreement::findOrFail($id);

            $data -> type = $this->type[$data -> type];

            return view('/admin/agreement/details', ['data' => $data]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 编辑页面
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        try {
            // 接收要操作的视频id
            if(!is_numeric($id = $request -> get('id')))
                abort(404);

            // 获取视频集合
            $data = Agreement::findOrFail($id);

            return view('/admin/agreement/edit', ['data' => $data]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 更改
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {

            // 接收要操作的id
            if(!is_numeric($id = $request -> get('id')))
                abort(404);

            // 获取集合
            $data = Agreement::findOrFail($id);

            $data -> title = post_check($request -> get('title'));
            $data -> content = post_check($request -> get('content'));

            // 保存
            $data -> save();

            // 重定向到首页
            return redirect('admin/agreement/index');

        } catch (ModelNotFoundException $e) {

            // 跳出404页面
            abort(404);
        }
    }

}