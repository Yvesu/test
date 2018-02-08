<?php

namespace App\Api\Controllers;

use App\Api\Transformer\UsersTransformer;
use App\Api\Transformer\VisitTransformer;
use App\Models\User;
use App\Models\VisitHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VisitController extends Controller
{
    protected $paginate = 20;

    protected $usersTransformer;

    protected $visitTransformer;

    public function __construct
    (
        UsersTransformer $usersTransformer,
        VisitTransformer $visitTransformer
    )
    {
        $this -> usersTransformer = $usersTransformer;
        $this->visitTransformer = $visitTransformer;
    }

    /**
     * @param $from
     * @param $to
     */
    public function add($from,$to)
    {
        if ( !is_null( $visit_history = VisitHistory::where('from',$from)->where('to',$to)->first()) ){
            $str=   $visit_history->created_at;
            $str = date('Y-m-d H:i:s',$str);
            $p = '/\d{4}-\d{1,2}-\d{1,2}/';
            preg_match($p,$str,$arr);
            if ( date('Y-m-d') !== $arr[0] && $from !== $visit_history->from) {
                VisitHistory::create([
                    'from' => $from,
                    'to' => $to,
                    'created_at'=>time(),
                    'updated_at'=>time(),
                    'class_time' =>strtotime( date("Y-m-d") ),
                ]);
            }
        }else{
            VisitHistory::create([
                'from' => $from,
                'to' => $to,
                'created_at'=>time(),
                'updated_at'=>time(),
                'class_time' =>strtotime( date("Y-m-d") ),
            ]);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request,$id)
    {
        $page = $request -> get('page','1');

        $times = VisitHistory::where('to',$id)->orderBy('class_time','DESC')->distinct()->forPage($page,$this->paginate)->pluck('class_time');

        //统计
        $count = VisitHistory::where('to',$id)->count();

       $data =   array_map(function ($item) use ($id){

        $user = VisitHistory::with(['belongToUser'=>function($q){
            $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
        }])
            ->where('status','1')
            ->where('to',$id)
            ->where('class_time',$item)
            ->orderBy('created_at','DESC')
            ->get();

            $arr['date'] = $item;
            $arr['info']  =  $this->visitTransformer->transformCollection( $user->all() );

        return $arr;
        },$times->toArray());

        return response()->json([
            'data' =>$data,
            'count' =>$count,
        ],200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $id = (int)$request->get('visit_id');

        $result = VisitHistory::find($id)->update(['status'=>'0']);

        if ($result){
            return response()->json(['message'=>'success'],201);
        }else{
            return response()->json(['message'=>'failed'],500);
        }
    }

}
