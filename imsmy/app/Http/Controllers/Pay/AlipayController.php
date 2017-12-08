<?php
namespace App\Http\Controllers\Pay;

use App\Models\CashRechargeAlipay;
use App\Models\CashRechargeOrder;
use App\Models\GoldAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use DB;

class AlipayController extends Controller
{
    public $gateway;
    public function __construct()
    {
        $gateway = Omnipay::create('Alipay_AopApp');
        $gateway->setSignType('RSA2'); //RSA/RSA2
        $gateway->setAppId(config('app_id'));
        $gateway->setPrivateKey(config('alipay.private_key'));
        $gateway->setAlipayPublicKey(config('alipay.public_key'));
        $gateway->setNotifyUrl(config('app.url').'/alipay/return');
    }

    // 异步
    public function gateway(Request $req)
    {
        $request = $this -> gateway->completePurchase();
        $request->setParams($req -> all());//Optional

        try {
            $response = $request->send();
            $resData = $response->getData();

            if($response->isPaid()){
                
                /**
                 * Payment is successful
                 */
                $time = getTime();
                
                $order_number = $resData['out_trade_no'];
                $order = CashRechargeOrder::where('status',0)->where('order_number',$order_number)->firstOrFail();

                // 用户的金币信息
                $gold_account = GoldAccount::where('user_id', $order->user_id)->firstOrFail();

                // 保存支付宝返回订单信息
                $data = [
                    "body" => $resData['body'],
                    "buyer_email" => $resData['buyer_email'],
                    "buyer_id" => $resData['buyer_id'],
                    "notify_id" => $resData['notify_id'],
                    "notify_time" => strtotime($resData['notify_time']),
                    "notify_type" => $resData['notify_type'],
                    "out_trade_no" => $resData['out_trade_no'],
                    "seller_id" => $resData['seller_id'],
                    "subject" => $resData['subject'],
                    "total_fee" => $resData['total_fee'],
                    "trade_no" => $resData['trade_no'],
                    "trade_status" => $resData['trade_status'],
                    "sign" => $resData['sign'],
                    "sign_type" => $resData['sign_type'],
                    "time_add" => $time,
                    "time_update" => $time,
                ];

                // 开启事务
                DB::beginTransaction();

                // 修改订单状态
                $order->update(['pay_type'=>1, 'status'=>1, 'time_update'=>$time]);

                // 保存支付宝返回订单信息
                CashRechargeAlipay::create($data);

                // 更新用户的金币数量
                $gold_account -> update([
                    'gold_total'    => $gold_account->gold_total + $order->gold_num,
                    'gold_avaiable' => $gold_account->gold_avaiable + $order->gold_num,
                    'time_update'   => $time,
                ]);

                DB::commit();

                Log::info('alipay.log',json_encode($resData));
                
                die('success'); //The response should be 'success' only
            }else{
                /**
                 * Payment is not successful
                 */
                DB::rollback();
                Log::error('alipay.log',json_encode($resData));
                die('fail');
            }
        } catch (\Exception $e) {
            /**
             * Payment is not successful
             */
            DB::rollback();
            Log::error('recharge_failed',json_encode($e->getMessage()).date('Y-m-d H:i:s'));

            die('fail');
        }
    }
}