<?php

namespace App\Api\Controllers;

use App\Models\PrivateLetter;
use Auth;
use App\Models\Tweet;
use App\Models\Topic;
use App\Models\Activity;
use App\Models\TweetReply;
use App\Models\ZxUserComplains;
use App\Models\User;
use App\Models\ZxUserComplainsCause;
use Illuminate\Http\Request;

/** 举报相关接口
 * Class ComplainController
 * @package App\Api\Controllers
 */
class ComplainController extends BaseController
{

    /** 举报原因
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try{
            // 获取投诉原因表的集合 zx_user_complains_cause
            $cause = ZxUserComplainsCause::active()->get(['id','content'])->all();

            // 判断是否为空
            if(empty($cause)) return response()->json([], 204);

            return response()->json($cause,201);

        }catch(\Exception $e){

            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 创建举报
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try{

            // 获取用户id
            $user = Auth::guard('api') -> user();

            $time = getTime();

            // 获取用户投诉的原因
            $cause_id = (int)$request -> get('cause');

            // 获取用户举报的内容,内容过滤
            $content = removeXSS($request -> get('content'));

            // 举报原因为其他，内容不能为空
            if(11 == $cause_id && !$content)
                return response()->json(['error'=>'content_cannot_empty'],407);

            // 获取用户举报的内容类型 0动态1话题2赛事3评论
            $type = (int)$request -> get('type');

            // 获取用户举报的内容类型的 id
            $type_id = (int)$request -> get('type_id');

            // 判断是否为数字
            if(!$cause_id || !in_array($type,[0,1,2,3,4]) || !$type_id)
                return response()->json(['error'=>'bad_request'],403);

            // 获取投诉原因表的集合 zx_user_complains_cause
            // 判断是否存在
            if(!$cause = ZxUserComplainsCause::active()->find($cause_id))
                return response()->json(['error'=>'bad_request'],403);

            // 判断用户是否已经提交过投诉
            $result = ZxUserComplains::where('user_id',$user->id)
                ->where('type',$type)
                ->where('type_id',$type_id)
                ->first();

            // 判断是否存在
            if($result) return response()->json(['error'=>'cannot_resubmit'],405);

            // 判断举报内容是否存在
            switch($type){
                case 0:
                    $type_data = Tweet::find($type_id);
                    break;
                case 1:
                    $type_data = Topic::find($type_id);
                    break;
                case 2:
                    $type_data = Activity::find($type_id);
                    break;
                case 3:
                    $type_data = TweetReply::find($type_id);
                    break;
                case 4:
                    $type_data = User::findOrFail($type_id);
                    break;
                case 5:
                    $type_data = PrivateLetter::find($type_id);
                    break;
            }

            // 判断举报内容是否存在
            if(!$type_data) return response()->json(['error'=>'not_found'],404);

            // 存入表中  zx_user_complains
            ZxUserComplains::create([
                'cause_id'      => $cause_id,
                'user_id'       => $user->id,
                'type'          => $type,
                'type_id'       => $type_id,
                'content'       => $content,
                'time_add'      => $time,
                'time_update'   => $time
            ]);

            return response()->json(['status'=>'OK'],201);

        }catch(\Exception $e){

            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}