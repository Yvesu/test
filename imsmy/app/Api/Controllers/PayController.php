<?php
namespace App\Api\Controllers;

use App\Models\CashGoldConversion;
use App\Models\CashRechargeOrder;
use App\Models\PayType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Omnipay\Omnipay;

class PayController extends BaseController
{
    /**
     * 可用的支付方式
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function payType($id, Request $request)
    {
        $type = PayType::where('active', 1)
                -> where('status', 1)
                -> get(['id', 'pay_type'])
                -> all();

        return response() -> json(['type' => $type], 200);
    }

    /**
     * 订单状态
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status($id, Request $request)
    {
        if(!$order_id = (int)$request -> get('order_id'))
            return response() -> json(['error' => 'null_order_id'], 403);

        // 删除订单
        $status = CashRechargeOrder::where('user_id', $id)
            -> where('order_number', $order_id)
            -> firstOrFail(['status']);

        return response() -> json(['status' => $status -> status], 200);
    }

    /**
     * 跳转支付宝之前要请求，然后返回可用支付方式
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay($id, Request $request)
    {
        try{
            if(!$pay_type = removeXSS($request -> get('pay_type')))
                return response() -> json(['error' => 'none_pay_type_selected'], 403);

            // 验证是否有效
            $pay = PayType::where('active', 1)
                -> where('status', 1)
                -> where('pay_type', $pay_type)
                -> firstOrFail(['id']);

            // 要充值的金币数量
            $gold_num = (int)$request -> get('gold_num');

            // 金币数量兑换成人民币
            $money = CashGoldConversion::where('status', 1)
                -> where('gold_num', $gold_num)
                -> firstOrFail(['money']);

            $time = getTime();

            // 生成订单
            $order = CashRechargeOrder::create([
                'user_id'       => $id,
                'order_number'  => date('YmdHis').mt_rand(1000, 9999),
                'money'         => $money->money,
                'gold_num'      => $gold_num,
                'pay_type'      => $pay_type,
                'time_add'      => $time,
                'time_update'   => $time,
            ]);

            return $this -> $pay_type($order);
        } catch(\Exception $e) {
            Log::error('recharge_error',['error' => json_encode($e->getMessage()).date('Y-m-d H:i:s')]);
            return response()->json(['error'=>'not_found'],404);
        }
    }

    /**
     * 取消支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id, Request $request)
    {
        try{

            if(!$order_id = (int)$request -> get('order_id'))
                return response() -> json(['error' => 'null_order_id'], 403);

            // 删除订单
            CashRechargeOrder::where('user_id', $id)
                -> where('order_number', $order_id)
                -> where('status', 0)
                -> delete();

            return response() -> json(['status' => 'ok'], 201);
        } catch(\Exception $e) {
            return response()->json(['error'=>'not_found'],404);
        }
    }

    // 支付宝支付
    private function alipay($order)
    {
        $gateway = Omnipay::create('Alipay_AopApp');

        $gateway -> setSignType('RSA2'); //RSA/RSA2
        $gateway -> setAppId(config('alipay.app_id'));
        $gateway -> setPrivateKey(config('alipay.private_key'));
        $gateway -> setAlipayPublicKey(config('alipay.public_key'));
        $gateway -> setNotifyUrl(config('app.url').'/alipay/return');


        $request = $gateway->purchase()->setBizContent([
            'subject'      => '金币充值',
            'out_trade_no' => $order -> order_number,
//            'total_amount' => $order -> money,
            'total_amount' => 0.01,
            'product_code' => 'QUICK_MSECURITY_PAY',
//            'it_b_pay' => '12h',    // 订单有效期，方便定时任务清理冗余数据
        ]);

        /**
         * @var AopTradeAppPayResponse $response
         */
//        $response = $request->send();

        $orderString = $request->send()->getOrderString();

        return response() -> json(['data' => $orderString], 200);
    }

    private function wxpay($order)
    {
        $gateway = Omnipay::create('WechatPay_App');
        $gateway->setAppId('wxe3946ac648160cda'); // 微信appId
        $gateway->setMchId(''); // 商铺号
        $gateway->setApiKey(''); // apikey  需要在微信商户平台自行设置
        $order = [
            'body' => 'Hi!Video订单支付',
            'out_trade_no' =>$order -> order_number,
            'total_fee' => 0.01,
            'spbill_create_ip' => '39.106.106.73',
            'fee_type' => 'CNY',
            'notify_url' => url('http://www.hivideo.com'),    // 微信支付的回调地址
        ];

        $request = $gateway->purchase($order);
        $response = $request->send();
        return response()->json(['data' =>  $response->getAppOrderData()],200);
    }

}