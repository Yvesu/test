<?php
namespace App\Api\Controllers;

use App\Models\GoldAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 用户账户管理
 * Class UserAccountController
 * @package App\Api\Controllers
 */
class UserAccountController extends BaseController
{

    /**
     * 用户资金详情
     * @param $user_id 用户id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($user_id)
    {
        try{

            // 账户金币数量
            $gold = GoldAccount::where('user_id',$user_id)->firstOrFail(['gold_avaiable']);

            return response()->json([
                'account'  => number_format($gold->gold_avaiable/100,2),
                'gold'     => $gold->gold_avaiable,
            ],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error'=>'not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

}