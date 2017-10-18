<?php
namespace App\Api\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 用户认证
 * Class UserVerifyController
 * @package App\Api\Controllers
 */
class UserVerifyController extends BaseController
{

    /**
     * 用户认证
     * @param $user_id 用户id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($user_id)
    {
        try{
            // 判断用户是否满足条件


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