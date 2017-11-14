<?php
namespace App\Http\Controllers\Web;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 用户认证
 * Class UserVerifyController
 * @package App\Api\Controllers
 */
class VerifyController extends Controller
{

    /**
     * 客户端设备版本检查
     * @param $user_id 用户id
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        try{
            // 判断用户是否满足条件
            $app_id = removeXSS($request -> get('token'));

            return response()->json([
                'data'  => 'http://m.miaopai.com/talent?token=ovyM6pDvsaa0gcLD-ngqkKPEZWy6x~p-&unique_id=b8fe4f9b-5966-35ad-b543-42ad3f7a9385&os=android%202,452',
            ],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

}