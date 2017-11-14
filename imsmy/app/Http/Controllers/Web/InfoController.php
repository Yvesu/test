<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Services\UserService;
use App\Services\InfoService;
use App\Services\InfoContentService;
use App\Services\IntegralService;
use App\Services\InfoCateService;
use App\Services\InfoCommentService;

use App\Http\Controllers\Controller;

class InfoController extends Controller
{
    /**
     * 查看贴子的页面
     * @param  Request $request 接收数据
     * @return view跳转到模板
     */
    public function getShowCard(Request $request)
    {

        if (!$id = intval($request->input('id', 0))) {
            return redirect('/');
        }

        // 通过id获取内容，查看用户是否有查看的权限
        $infoService = new InfoService();

        $infoCateService = new InfoCateService();

        //获取正文及发布者相关信息
        if (!$card = $infoService->selectWebCardById($id)) {
            return redirect('/cate/category');
        }

        // 通过card 获取栏目信息
        $cate = $infoCateService->getChildCate($card['cate_id']);

        //本分类向上路径
        $catePath = $infoCateService->getElderPath($cate['path_pid']);

        //获取贴子内容
        // $card = $infoService->selectWebCardById($id);

        //增加阅读量
        $infoService->addNumById($id,['num_pv']);

        $userService = new UserService();
        //获取发布者信息
        $card['_uid_user'] = $userService->selectOneById($card['uid'], '*');

        // 回贴信息
        $infoCommentService = new InfoCommentService();

        //获取本info_id，及分页
        $items = $infoCommentService->selectWebListByInfoId($id, $request->input('num', 10));

        // 设置返回数组
        $request = [
            'num' => $request->input('num', 10),
            'id' => $request->input('id'),
        ];

        //开始楼号
        $floor = ($items->perPage())*($items->currentPage()-1);

        // 获取回帖用户信息
        return view('/web/info/show', [
            'card'=>$card,
            'cate'=>$cate,
            'catePath'=>$catePath,
            'items'=>$items,
            'request'=>$request,
            'floor'=>$floor
        ]);
    }

    /**
     * 帖子发表页面
     */
    public function getAddCard(Request $request)
    {
        $title = '发表网评-';
        $url = '/info/insert-content';
        $type = 3;

        return view('/web/info/addArticle', compact('id', 'catePath', 'title', 'url', 'type', 'cate'));

        if(!$userId = intval(session('user.id','')))
            return redirect('/user/login')->with('msg','请先登录');

        $userService = new UserService();
        if(!$userService->checkUserStatus())
            return redirect('/user/my-info')->with('msg','禁言状态，请联系管理员');


        // 获取用户id
        if (!($cid = $request->get('id')) || $cid==11) return back();
        if (!$id = $userId) return redirect('/user/login');

        //获取本分类信息，及path_pid
        $infoCateService = new InfoCateService();
        $cate = $infoCateService->getChildCate($cid);

        //本分类向上路径
        $catePath = $infoCateService->getElderPath($cate['path_pid']);
        $cateType = $infoCateService->selectWebList([['pid', '<>', 0]]);

        // dd($cate);

        $title = '发表帖子-' . $cate['name'];
        $url = '/info/insert-content';
        $type = 3;

        return view('/web/info/addArticle', compact('id', 'catePath', 'title', 'url', 'type', 'cate'));
    }

    /**
     * 文章发表页面
     */
    public function getAddArticle(Request $request)
    {
        if(!$userId = intval(session('user.id','')))
            return redirect('/user/login')->with('msg','请先登录');

        $userService = new UserService();
        if(!$userService->checkUserStatus())
            return redirect('/user/my-info')->with('msg','禁言状态，请联系管理员');

        // 获取用户id
        $id = $userId;

        $title = '发表文章-求知者';
        $url = '/info/insert-content';
        $type = 1;

        //获取所有分类信息
        $infoCateService = new InfoCateService();
        $cateType = $infoCateService->selectWebList([['pid', '<>', 0]]);

        return view('/web/info/addArticle', compact('id', 'catePath', 'title', 'url', 'type', 'cateType'));
    }

    /**
     * 日志发表页面
     */
    public function getAddLog(Request $request)
    {
        if(!$userId = intval(session('user.id','')))
            return redirect('/user/login')->with('msg','请先登录');

        $userService = new UserService();
        if(!$userService->checkUserStatus())
            return redirect('/user/my-info')->with('msg','禁言状态，请联系管理员');

        // 获取用户id
        $id = $userId;

        $title = '发表日志-求知者';
        $url = '/info/insert-content';
        $type = 2;

        //获取所有分类信息
        $infoCateService = new InfoCateService();
        $cateType = $infoCateService->selectWebList([['pid', '<>', 0]]);

        return view('/web/info/addArticle', compact('id', 'catePath', 'title', 'url', 'type', 'cateType'));
    }

    /**
     * 文章提交页面
     */
    public function postInsertContent(Request $request)
    {
        if(!$userId = intval(session('user.id','')))
            return redirect('/user/login')->with('msg','请先登录');

        $userService = new UserService();
        if(!$userService->checkUserStatus())
            return redirect('/user/my-info')->with('msg','禁言状态，请联系管理员');

        //验证信息是否填写
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
            'cate_id' => 'required',
        ],
            [
                'title.required' => '标题不能为空',
                'content.required' => '内容不能为空',
                'cate_id.required' => '分类不能为空',
            ]);


        $data = $request->all();

        // info 部分数据
        $info = $request->only('channel_id', 'title', 'keywords', 'intro', 'status', 'type');

        $info['time_add'] = $info['time_update'] = getTime();

        $info['uid'] = $userId;

        //判断是否有图片上传
        if ($filename = uploadFile($request, 'images'))
            $info['intro_img_url'] = $filename;

        // rs_info_content表
        $info_content['content'] = $data['content'];

        // 可看权限
        $info_content['show_type'] = $data['show'] = 5;

        // 实例化功能类
        $infoService = new InfoService();

        if (($contentArray = $infoService->addInfoArticle($info, $info_content,$info['type'])) < 0) {
            return back()->with('msg', '发布失败');
        }

        if($contentArray['integralNum']) {
            $message = '发布成功';
        }

        return redirect('/info/show-card?id='.$contentArray['id'])->with('msg', $message);
    }

    /**
     * 文章编辑页面
     */
    public function getEditArticle(Request $request)
    {
        if(!$userId = intval(session('user.id','')))
            return redirect('/user/login')->with('msg','请先登录');

        $userService = new UserService();
        if(!$userService->checkUserStatus())
            return redirect('/user/my-info')->with('msg','禁言状态，请联系管理员');

        // 获取文章id
        $id = $request->input('id');

        $title = '编辑文章-求知者';
        $url = '/info/update-content';
        $type = 1;

        // 实例化功能类
        $infoService = new InfoService();

        //获取所有分类信息
        $infoCateService = new InfoCateService();
        $cate = $infoCateService->selectWebList([['pid', '<>', 0]]);

        // 查询条件
        $where = [['id', '=', $id], ['uid', '=', $userId]];

        // 要查询字段
        $fields = 'id,cate_id,type,content_id,title,keywords,intro,intro_img_url';

        // 从rs_info表中核实身份并获取数据
        if (!$info = $infoService->checkOneByWhere($where, $fields)) return redirect('/');

        // 从rs_content表中获取数据
        $info_content = $infoService->selectContentById($info['content_id'], 'content');

        $info['content'] = $info_content['content'];

        return view('/web/info/editArticle', ['info' => $info, 'cate' => $cate,'title'=>$title,'url'=>$url,'type'=>$type]);

    }

    /**
     * 日志编辑页面
     */
    public function getEditLog(Request $request)
    {
        if(!$userId = intval(session('user.id','')))
            return redirect('/user/login')->with('msg','请先登录');

        $userService = new UserService();
        if(!$userService->checkUserStatus())
            return redirect('/user/my-info')->with('msg','禁言状态，请联系管理员');

        // 获取文章id
        $id = $request->input('id');

        $title = '编辑日志-求知者';
        $url = '/info/update-content';
        $type = 2;

        // 实例化功能类
        $infoService = new InfoService();

        //获取所有分类信息
        $infoCateService = new InfoCateService();
        $cate = $infoCateService->selectWebList([['pid', '<>', 0]]);

        // 查询条件
        $where = [['id', '=', $id], ['uid', '=', $userId]];

        // 要查询字段
        $fields = 'id,cate_id,type,content_id,title,keywords,intro,intro_img_url';

        // 从rs_info表中核实身份并获取数据
        if (!$info = $infoService->checkOneByWhere($where, $fields))
            return redirect('/');

        // 从rs_content表中获取数据
        $info_content = $infoService->selectContentById($info['content_id'], 'content');

        $info['content'] = $info_content['content'];

        return view('/web/info/editArticle', ['info' => $info, 'cate' => $cate,'title'=>$title,'url'=>$url,'type'=>$type]);

    }


    /**
     * 帖子编辑页面
     */
    public function getEditCard(Request $request)
    {
        if(!$userId = intval(session('user.id','')))
            return redirect('/user/login')->with('msg','请先登录');

        $userService = new UserService();
        if(!$userService->checkUserStatus())
            return redirect('/user/my-info')->with('msg','禁言状态，请联系管理员');

        // 获取文章id
        $id = $request->input('id');

        $title = '编辑帖子-求知者';
        $url = '/info/update-content';
        $type = 3;

        // 实例化功能类
        $infoService = new InfoService();

        //获取所有分类信息
        $infoCateService = new InfoCateService();
        // $cate = $infoCateService->selectWebList([['pid', '<>', 0]]);

        // 查询条件
        // $where = [['content_id', '=', $id], ['uid', '=', $userId]];
        $where = [['id', '=', $id], ['uid', '=', $userId]];

        // 要查询字段
        $fields = 'id,cate_id,type,content_id,title,keywords,intro,intro_img_url';

        // 从rs_info表中核实身份并获取数据
        if (!$info = $infoService->checkOneByWhere($where, $fields)) return redirect('/');

        // 从rs_content表中获取数据
        $info_content = $infoService->selectContentById($info['content_id'], 'content');

        $info['content'] = $info_content['content'];

        // 分类id及内容
        $cate = $infoCateService->selectOneById($info['cate_id'],'name');
        $cate['id'] = $info['cate_id'];

        return view('/web/info/editArticle', ['info' => $info, 'cate' => $cate,'title'=>$title,'url'=>$url,'type'=>$type]);

    }

    /**
     * 文章更新提交
     */
    public function postUpdateContent(Request $request)
    {
        if(!$userId = intval(session('user.id','')))
            return redirect('/user/login')->with('msg','请先登录');

        $userService = new UserService();
        if(!$userService->checkUserStatus())
            return redirect('/user/my-info')->with('msg','禁言状态，请联系管理员');

        //验证信息是否填写
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
            'cate_id' => 'required',
        ],
            [
                'title.required' => '标题不能为空',
                'content.required' => '内容不能为空',
                'cate_id.required' => '分类不能为空',
            ]);

        $data = $request->all();

        // rs_info 部分数据
        $info = $request->only('cate_id', 'title', 'keywords', 'intro', 'status', 'type');

        $info['time_update'] = getTime();

        $info['uid'] = $userId;

        //判断是否有图片上传
        if ($filename = uploadFile($request, 'images'))
            $info['intro_img_url'] = $filename;

        // rs_info_content表
        $info_content['content'] = $data['content'];

        // 可看权限
        $info_content['show_type'] = $data['show'] = 5;

        // 实例化功能类
        $infoService = new InfoService();

        // 修改信息主表信息
        $resultOne = $infoService->updateInfoById($data['cid'],$info);

        // 修改信息附表内容表信息
        $resultTwo = $infoService->updateInfoContentById($data['content_id'],$info_content);

        if ($resultOne || $resultTwo) {

            return redirect('/info/show-card?id='.$data['cid'])->with('msg', '修改成功');
        }

        return back()->with('msg', '修改失败');

    }


    /**
     * 帖子搜索页面
     */
    public function getSearch(Request $request)
    {
        // 实例化功能类
        $infoService = new InfoService();

        if($request->input('keywords',''))
            $infoService->createSearchLog($request->input('keywords'));

        // 获取rs_info信息
        $data = $infoService->selectPageSearch($request->input('type',''),$request->input('keywords'));

        // 设置返回数组
        $res = [
            'num' => $request->input('num', 10),
            'keywords' => $request->input('keywords', ''),
            'type' => $request->input('type', ''),
        ];

        return view('/web/info/searchList', [
            'items' => $data,
            'request' => $res
        ]);
    }

    /**
     * ajax获取InfoRelation
     */
    public function getAjaxInfoRelation(Request $request)
    {
        if(!$userId = intval(session('user.id','')))
            return response()->json(9);

        $userId = $request->input('id');

        // 文章类型
        $type = $request->input('type',3);

        // 数量
        $num = $request->input('num',10);

        $infoService = new InfoService();

        // 收藏信息id
        $where = [['uid','=',$userId],['type','=',2],['status','=',1]];
        $collectIds = $infoService -> selectRelationsList($where,[],'info_id');

        foreach ($collectIds as $key => $value) {
            $collectInfoIds[] = $value['info_id'];
        }

        // 返回响应信息
        return response()->json($infoService -> selectListByWhereIn('id',$collectInfoIds,'intro,id,title,num_comment_good'));
    }


}
