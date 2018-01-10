<?php

namespace App\Api\Controllers;

use App\Api\Transformer\JoinVideoTransformer;
use App\Models\JoinVideo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JoinVideoController extends Controller
{
    protected $paginate = 20;

    private $joinVideoTransformer;

    public function __construct
    (
        JoinVideoTransformer $joinVideoTransformer
    )
    {
        $this -> joinVideoTransformer = $joinVideoTransformer;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            if ( !is_numeric($page = $request->get('page',1))) return response()->json(['message'=>'bad_request'],400);

            $join_videos = JoinVideo::where('active','1')
                ->orderBy('down_count','DESC')
                ->forPage($page,$this->paginate)
                ->get(['id','name','intro','image','duration','weight_height','head_video','tail_video','down_count']);

            return response()->json([
                'data'  =>  $this -> joinVideoTransformer -> transformCollection($join_videos->all()),
            ],200);
        }catch (\Exception $e){
            return response()->json(['message'=>'bad_request'],500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommend(Request $request)
    {
        try{
            if ( !is_numeric($page = $request->get('page',1))) return response()->json(['message'=>'bad_request'],400);
            $join_videos = JoinVideo::where('recommend','1')
                ->orderBy('down_count','DESC')
                ->forPage($page,$this->paginate)
                ->get(['id','name','intro','duration','weight_height','image','head_video','tail_video','down_count']);

            return response()->json([
                'data'  =>  $this -> joinVideoTransformer -> transformCollection($join_videos->all()),
            ],200);
        }catch (\Exception $e){
            return response()->json(['message'=>'bad_request'],500);
        }
    }
}
