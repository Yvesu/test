<?php

namespace App\Api\Controllers;


/**
 * 各类协议请求接口
 * Class AgreementController
 * @package App\Api\Controllers
 */
class AgreementController extends BaseController
{

    /**
     * 注册协议接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        return response()->json(['data'=>'http://www.goobird.com/agreement/register'],200);
    }

    /**
     * 举报规范 接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function report()
    {
        return response()->json(['data'=>'http://www.goobird.com/agreement/report'],200);
    }

    /**
     * 发起协议 接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function sponsor()
    {
        return response()->json(['data'=>'http://www.goobird.com/agreement/sponsor'],200);
    }

    /**
     * 投资协议 接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function invest()
    {
        return response()->json(['data'=>'http://www.goobird.com/agreement/invest'],200);
    }

    /**
     * 租赁协议 接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function lease()
    {
        return response()->json(['data'=>'http://www.goobird.com/agreement/lease'],200);
    }

    /**
     * 发布角色协议 接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function role()
    {
        return response()->json(['data'=>'http://www.goobird.com/agreement/role'],200);
    }

    /**
     * 发布赛事活动规则 接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function rule()
    {
        return response()->json(['data'=>'http://www.goobird.com/agreement/rule'],200);
    }
}