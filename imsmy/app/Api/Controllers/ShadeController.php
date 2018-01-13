<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ShadeTransformer;
use App\Models\Shade;
use App\Models\ShadeFolder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Auth;
use Illuminate\Support\Facades\DB;

class ShadeController extends Controller
{
    protected $paginate = 20;

    protected $shadeTransformer;

    public function __construct
    (
        ShadeTransformer $shadeTransformer
    )
    {
        $this -> shadeTransformer = $shadeTransformer;
    }

    /**
     * @return mixed
     */
    public function recommend()
    {
            try {
                $recommend_shade = Shade::with(['belongToUser' => function ($q) {
                    $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                }, 'belongToFolder' => function ($q) {
                    $q->select(['id', 'name']);
                }])
                    ->where('recommend', '1')
                    ->orderBy('sort', 'ASC')
                    ->get(['id', 'name', 'video', 'image', 'user_id', 'folder_id', 'integral', 'official', 'down_count', 'watch_count', 'size', 'duration', 'vipfree', 'create_time']);

                return response()->json([
                    'data' => $this->shadeTransformer->transformCollection($recommend_shade->all()),
                ], 200);

            } catch (\Exception $e) {
                return response()->json(['message' => 'bad_request'], 500);
            }
    }

    /**
     * @return mixed
     */
    public function folder()
    {
            try{
                $folder = ShadeFolder::where('active','1')
                    ->orderBy('sort','ASC')
                    ->get(['id','name','count']);

                return response()->json([
                    'data'      =>  $folder,
                ],200);
            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function file($id,Request $request)
    {
        if (!is_numeric($page = $request->get('page',1)))  return response()->json(['message'=>'bad_request'],403);

        $shade = Cache::remember($id.'shade'.$page,'60',function() use($id,$request,$page){
            try{
                $shade = Shade::with(['belongToUser' => function ($q) {
                    $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                }, 'belongToFolder' => function ($q) {
                    $q->select(['id', 'name']);
                }])
                    ->where('folder_id',$id)
                    ->where('active','1')
                    ->where('test_result','1')
                    ->orderBy('sort', 'ASC')
                    ->forPage($page,$this->paginate)
                    ->get(['id', 'name', 'video', 'image', 'user_id', 'folder_id', 'integral', 'official', 'down_count', 'watch_count', 'size', 'duration', 'vipfree', 'create_time']);

                return response()->json([
                    'data' => $this->shadeTransformer->transformCollection($shade->all()),
                ], 200);

            }catch(\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });
        return $shade;
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function downAndUse($id)
    {
        try {
            \DB::beginTransaction();
            $res_1 = Shade::find($id)->increment('down_count');
            $res_2 = Shade::find($id)->increment('watch_count');
            if($res_2 && $res_1){
                \DB::commit();
                return response()->json(['message'=>'success'],200);
            }else{
                \DB::rollBack();
                return response()->json(['message'=>'failed'],403);
            }
        }catch(\Exception $e){
            return response()->json(['message'=>'bad_request'],500);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function test(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if($user && $user->tester) {
                if (!is_numeric($page = $request->get('page', 1))) return response()->json(['message' => 'bad_request'], 403);
                $recommend_shade = Shade::with(['belongToUser' => function ($q) {
                    $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                }, 'belongToFolder' => function ($q) {
                    $q->select(['id', 'name']);
                }])
                    ->where('test_result', '0')
                    ->orderBy('sort', 'ASC')
                    ->forPage($page, $this->paginate)
                    ->get(['id', 'name', 'video', 'image', 'user_id', 'folder_id', 'integral', 'official', 'down_count', 'watch_count', 'size', 'duration', 'vipfree', 'create_time']);

                return response()->json([
                    'data' => $this->shadeTransformer->transformCollection($recommend_shade->all()),
                ], 200);
            }else{
                return response()->json(['message' => 'bad_request'], 403);
            }
        }catch(\Exception $e){
            return response()->json(['message'=>'bad_request'],500);
        }
    }

    public function testresult(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $result = $request ->get('result','1');

            $id = $request->get('id');

            DB::beginTransaction();

            //0 待检测  1检测通过   2 检测未通过
            if(is_numeric($id)){
                $res1 = Shade::find($id)->update(['test_result'=>$result]);

                $res2 = DB::table('shade_test_result')->insert([
                    'shade_id'   => $id,
                    'fail_reason'   => $request->get('reason',''),
                    'tester_id'     => $user->id,
                    'create_time'   => time(),
                    'update_time'   => time(),
                ]);

            }else{
                $obj =  objectToArray(json_decode($id));

                $res_1= [];
                $res_2= [];
                foreach ($obj as $v){

                    $res = Shade::find($v)->update(['test_result'=>$result]);

                    $ress =  DB::table('shade_test_result')->insert([
                        'shade_id'   => $id,
                        'fail_reason'   => $request->get('reason',''),
                        'tester_id'     => $user->id,
                        'create_time'   => time(),
                        'update_time'   => time(),
                    ]);

                    if($res){
                        $res_1[] = 1;
                    }else{
                        $res_1[] = 2;
                    }

                    if ($ress){
                        $res_2[] = 1;
                    }else{
                        $res_2[] = 2;
                    }

                }

                if(in_array(2,$res_1)){
                    $res1 = 0;
                }else{
                    $res1 = 1;
                }

                if(in_array(2,$res_2)){
                    $res2 = 0;
                }else{
                    $res2 = 1;
                }

            }

            if($res1 && $res2){
                DB::commit();
                return response()->json(['message'=>'success'],200);
            }else{
                DB::rollBack();
                return response()->json(['message'=>'failed'],500);
            }

        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(['error'=>$e->getMessage()],$e->getCode());
        }

    }
}
