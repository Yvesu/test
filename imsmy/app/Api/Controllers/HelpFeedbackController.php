<?php
namespace App\Api\Controllers;

use App\Models\{HelpName,HelpFeedback};
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 用户的帮助与反馈表
 * Class HelpFeedbackController
 * @package App\Api\Controllers
 */
class HelpFeedbackController extends BaseController
{

    /**
     * 帮助标题
     * @return \Illuminate\Http\JsonResponse
     */
    public function helpName()
    {

        return response()->json([
            'data'  => HelpName::active()->get(['id','name','url'])->all()
        ],200);
    }

    /**
     * 反馈 不论有没有正确保存，都默认为保存成功
     * @return \Illuminate\Http\JsonResponse
     */
    public function feedback(Request $request)
    {
        try{
            // 获取用户提交的反馈信息
            if(!$content = removeXSS($request -> get('content')))
                return response()->json(['error'=>'bad_request'],403);

            // 联系方式
            $connect = removeXSS($request -> get('connect',''));

            $time = getTime();

            HelpFeedback::create([
                'content'       => $content,
                'connect'       => $connect,
                'time_add'      => $time,
                'time_update'   => $time,
            ]);

            return response()->json(['status'=>'ok'],201);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }
}