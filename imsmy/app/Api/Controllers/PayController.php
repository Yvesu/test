<?php
namespace App\Api\Controllers;

use App\Api\Transformer\GoldNumTransformer;
use App\Api\Transformer\OrderHistoryTransformer;
use App\Models\CashGoldConversion;
use App\Models\CashRechargeOrder;
use App\Models\NoExitWord;
use App\Models\PayType;
use App\Models\PrivateLetter;
use App\Models\User;
use App\Models\WechatOrderNotice;
use App\Services\SendPrivateLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Omnipay\Omnipay;

class PayController extends BaseController
{
    protected $goldNumTransformer;
    protected $orderHistoryTransformer;

    public function __construct
    (
        GoldNumTransformer $goldNumTransformer,
        OrderHistoryTransformer $orderHistoryTransformer
    )
    {
        $this->goldNumTransformer = $goldNumTransformer;
        $this->orderHistoryTransformer = $orderHistoryTransformer;
    }

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
                'order_number'  => createOrder(),
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

    /**
     * @param $order
     * @return \Illuminate\Http\JsonResponse
     */
    private function wechat($order)
    {
        $gateway = Omnipay::create('WechatPay_App');
        $gateway->setAppId(config('wechat.app_id'));
        $gateway->setMchId(config('wechat.mch_id'));
        $gateway->setApiKey(config('wechat.api_key'));
        $ip = getIP();
        $order = [
            'body' => config('wechat.body'),
            'out_trade_no' =>$order -> order_number,
            'total_fee' => $order->money,
            'spbill_create_ip' => $ip,
            'fee_type' => 'CNY',
            'notify_url' => config('wechat.notify_url'),
        ];

        $request = $gateway->purchase($order);
        $response = $request->send();
        return response()->json(['data' =>$response->getAppOrderData()],200);
    }

    public function wechatnotice()
    {
        $gateway    = Omnipay::create('WechatPay');
        $gateway->setAppId(config('wechat.app_id'));
        $gateway->setMchId(config('wechat.mch_id'));
        $gateway->setApiKey(config('wechat.api_key'));

        $response = $gateway->completePurchase([
            'request_params' =>file_get_contents('php://input')
        ])->send();

        if ($response->isPaid()) {
            //转为数组
            $payData = $response->getRequestData();
            //查询到订单
            $order = CashRechargeOrder::where('order_number',$payData['out_trade_no'])->first();
            //如果订单不存在
            if (empty($order)) return;

            //记录微信的返回消息
            $wechatOrderNotice = $payData;
            $wechatOrderNotice['order_id'] = $order->id;
            WechatOrderNotice::create($wechatOrderNotice);

            //支付成功
            if ($payData['result_code'] === 'SUCCESS' && $order->status === 0 ){
                \DB::beginTransaction();
                $gold_num = CashGoldConversion::where('money', (int)$payData['total_fee'])-> first();
                $order->status = 1;
                $order->money = (int)$payData['total_fee'];
                $order->gold_num =  $gold_num ->gold_num;
                $result = $order->save();                   //修改订单状态
                if ($result){
                    \DB::commit();

                    //查询更新后的数据
                    $order_server = CashRechargeOrder::find($order->id);

                    //私信通知
                    $date = date('Y-m-d H:i:s');
                    $content = "您于".$date."成功充值". $gold_num ->gold_num ."金币。";
                    SendPrivateLetter::send($order_server->user_id,$content,'Hi!Video-财务');

                    //创建用户积分收入记录
                    User\UserIntegralInCome::create([
                            'user_id'=>$order_server->user_id,
                            'up_number' =>$order_server->order_number,
                            'up_count'  => $order_server->gold_num,
                            'up_type'   => 'wechat',
                            'status'    => 1,
                            'create_at' => time(),
                    ]);

                    //更新用户的积分
                    $user_integral = User\UserIntegral::where('user_id',$order_server->user_id)->first();
                    //用户充值过积分
                    if ($user_integral){
                        User\UserIntegral::where('user_id',$order_server->user_id)->increment('integral_count',$gold_num->gold_num);
                        $user_integral->update_at = time();
                        $user_integral->save();

                    }else{                      //没有用户积分的数据
                        \DB::beginTransaction();
                        User\UserIntegral::create([
                            'user_id'           => $order_server->user_id,
                            'integral_count'    => $gold_num->gold_num,
                            'create_at'         => time(),
                            'update_at'         => time(),
                        ]);
                    }
                }else{
                    \DB::rollBack();
                    Log::info(json_encode($payData));
                    CashRechargeOrder::where('order_number',$payData['out_trade_no'])->update(['status'=>3]);
                }
            }
        }else{
            //pay fail
            $payData = $response->getRequestData();
            $order = CashRechargeOrder::where('order_number',$payData['out_trade_no'])->first();
            if (empty($order)) return;
            if ($payData['return_code'] === 'FAIL' && $order->status === 0 ){
                $order->status = 3;
                $order->save();
            }
        }
    }

    public function goldnum()
    {
        $gold_num = CashGoldConversion::where('status',1)
            ->get();

        return response()->json([
            'data'=>$this->goldNumTransformer->transformCollection($gold_num->all())
        ],200);
    }

    /**
     * 我的金币数量
     * @return \Illuminate\Http\JsonResponse
     */
    public function mygold()
    {
        try{
            $user = \Auth::guard('api')->user();

            $user_info = User\UserIntegral::where('user_id',$user->id)->first();

            if ($user_info){
                $gold_num = $user_info->integral_count;
            }else{
                $gold_num = 0;
            }

            return response()->json(['data'=>$gold_num],200);

        }catch (\Exception $e){
            return response()->json(['message'=>'bad request'],500);
        }
    }

    /**
     * 交易记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderHistory(Request $request)
    {
        try{
            $user = \Auth::guard('api')->user();

            $page = (int)$request->get('page',1);

            $data = CashRechargeOrder::where('user_id',$user->id)
                ->where('active','1')
                ->orderBy('time_add','DESC')
                ->forPage($page,20)
                ->get();

            return response()->json([
                'data'=> $this -> orderHistoryTransformer->transformCollection($data->all()),
            ],200);

        }catch (\Exception $e){
            return response()->json(['message'=>'bad request'],500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function operateOrder(Request $request)
    {
        try{
           if (is_null($id = (int)$request->get('order_id')) ||  is_null($type = $request->get('type'))) return response()->json(['message'=>'bad request'],403);

           // 0 关闭  1 删除  2继续支付
            switch ($type){
                case '0' :
                     return $this->closed($id);
                case '1' :
                    return $this->deleted($id);
                case '2' :
                    return $this->wechatAgain($id);
            }
        }catch (\Exception $e){
            return response()->json(['message'=>'bad request'],500);
        }
    }

    private function closed($id)
    {
        $order = CashRechargeOrder::find($id);

        //订单不存在
        if (!$order) return response()->json(['message'=>'bad request'],404);

        //状态不正常
        if ($order->status !== 0) return response()->json(['message'=>'bad request'],403);

        //时间差
        $mistiming = time() - $order->time_add;

        if ($mistiming <= 300) return response()->json(['message'=>'bad request'],406);

        //关闭微信端订单
        $gateway    = Omnipay::create('WechatPay');
        $gateway->setAppId(config('wechat.app_id'));
        $gateway->setMchId(config('wechat.mch_id'));
        $gateway->setApiKey(config('wechat.api_key'));

        $result = $order ->update(['status'=> 2]);

        $response = $gateway->close([
            'out_trade_no' => $order->order_number,
        ])->send();

        if ($result && $response->isSuccessful()){
            return response()->json(['message'=>'success'],201);
        }else{
            return response()->json(['message'=>'failed'],500);
        }

    }

    private function deleted($id)
    {
        $order = CashRechargeOrder::where('active','1')->find($id);

        if (!$order) return response()->json(['message'=>'bad request'],404);

        $result_delete = $order ->update(['active'=> '0']);

        if ($result_delete){
            return response()->json(['message'=>'success'],201);
        }else{
            return response()->json(['message'=>'failed'],500);
        }
    }

    private function wechatAgain($id)
    {

        $order_server = CashRechargeOrder::where('status',0)->find($id);

        return $this->wechat($order_server);
    }
}