<?php

namespace App\Http\Controllers\NewWeb\User;

use App\Models\Filmfest\FilmfestCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function matchType()
    {
        try{
            $data = FilmfestCategory::get();
            $type = [
                ['id'=>0,'name'=>'全部']
            ];
            foreach ($data as $k =>$v)
            {
                $tempData =[
                  'name'=>$v->name,
                  'id'=>$v->id,
                ];
                array_push($type,$tempData);
            }
            array_push($type,['id'=>999,'name'=>'我的']);
            return response()->json(['data'=>$type],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }

    public function matchTime()
    {
        //  时间条件   0 全部  1 最新发布 2 即将开始 3 即将结束 4 进行中  5 已结束
        $data = [
            [
                'id' => 0,
                'name'=> '全部',
            ],
            [
                'id' => 1,
                'name'=> '最新发布',
            ],
            [
                'id' => 2,
                'name'=> '即将开始',
            ],[
                'id' => 3,
                'name'=> '即将结束',
            ],
            [
                'id' => 4,
                'name'=> '进行中',
            ],
            [
                'id' => 5,
                'name'=> '已结束',
            ],
        ];
        return response()->json(['data'=>$data],200);
    }

}
