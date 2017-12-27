<?php

namespace App\Http\Controllers\NewWeb\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserCommonController extends Controller
{
    //
    /**
     * @return \Illuminate\Http\JsonResponse
     * 总分类
     */
    public function type()
    {
        $data = [
            [
                'label'=>1,
                'des'=>'动态',
            ],
            [
                'label'=>2,
                'des'=>'我的视频',
            ],
            [
                'label'=>3,
                'des'=>'发现',
            ]
        ];
        return response()->json(['data'=>$data],200);
    }


    public function orderby_function()
    {
        $data = [
            [
                'label'=>1,
                'des'=>'日期'
            ],
            [
                'label'=>2,
                'des'=>'播放量',
            ],
            [
                'label'=>3,
                'des'=>'点赞',
            ],
            [
                'label'=>4,
                'des'=>'评论',
            ],
            [
                'label'=>5,
                'des'=>'持续时间'
            ]
        ];
        return response()->json(['data'=>$data],200);
    }

    public function layout()
    {
        $data = [
            [
                'label'=>1,
                'des'=>'布局一',
            ],
            [
                'label'=>2,
                'des'=>'布局二',
            ]
        ];
        return response()->json(['data'=>$data],200);
    }
}
