<?php

namespace App\Http\Controllers\NewWeb\Play;

use App\Models\Tweet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlayController extends Controller
{
    //
    private $protocol = 'http://';

    public function play(Request $request)
    {
        try{
            $id = $request->get('id',null);
            $resolution_ratio = $request->get('resolution_ratio',1);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $data = Tweet::find($id);
            if($data->type != 3){
                $address = $data->video;
            }else{
                if($resolution_ratio == 1)
                {
                    $address = $this->protocol.$data->transcoding_video;
                }elseif ($resolution_ratio == 2){
                    $address = $this->protocol.$data->high_video;
                }elseif ($resolution_ratio == 3){
                    $address = $this->protocol.$data->norm_video;
                }elseif ($resolution_ratio == 4){
                    $address = $this->protocol.$data->video_m3u8;
                }else{
                    $address = $this->protocol.$data->transcoding_video;
                }
            }
            return response()->json(['data'=>$address],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }


    public function resolution_ratio(Request $request)
    {
        try{
            $id = $request->get('id',null);
            if(is_null($id)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $data = Tweet::find($id);
            if($data->high_video){
                $resolution_ratio = [
                    [
                        'label'=>1,
                        'des'=>'自适应',
                    ],
                    [
                        'label'=>2,
                        'des'=>'高清',
                    ],
                    [
                        'label'=>3,
                        'des'=>'标准',
                    ],
                    [
                        'label'=>4,
                        'des'=>'源码',
                    ]

                ];
            }else{
                $resolution_ratio = [
                    [
                        'label'=>1,
                        'des'=>'自适应',
                    ],
                    [
                        'label'=>4,
                        'des'=>'源码',
                    ]

                ];
            }
            return response()->json(['data'=>$resolution_ratio],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],200);
        }
    }
}
