<?php

namespace App\Api\Controllers;

use App\Models\Fragment;
use App\Models\Make\MakeAudioEffectFile;
use App\Models\Make\MakeAudioFile;
use App\Models\Make\MakeEffectsFile;
use App\Models\Make\MakeFilterFile;
use App\Models\Make\MakeTemplateFile;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class PayTypeController extends BaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay(Request $request)
    {
        //类型
        $type = removeXSS( $request -> get('type') );

        //Id
        $id = removeXSS( $request -> get('id') );

        //user_id
        $user_Id = Auth::guard('api')->user()->id;

        //判断
        if(empty($type) && !is_string($type)) return response()->json(['error'=>'bad_request'],403);

        //判断
        if(empty($type) && !is_numeric($id)) return response()->json(['error'=>'bad_request'],403);

        //分发处理
        switch ($type){
            case 'mixture':
               //混合
                return $this->mixturePay($id,$user_Id);
                break;

            case 'filter' :
                //滤镜
                return $this->filter($id,$user_Id);
                break;

            case 'template' :
                //滤镜
                return $this->template($id,$user_Id);
                break;

            case 'audio' :
                //滤镜
                return $this->audio($id,$user_Id);
                break;

            case 'audioeffect' :
                //滤镜
                return $this->audioeffect($id,$user_Id);
                break;

            case 'fragment' :
                //滤镜
                return $this->fragment($id,$user_Id);
                break;

            default :
                //不存在的类型
                return response()->json(['error'=>'invaild_payType'],403);
                break;
        }
    }

    /**
     * @param $id
     * @param $user_Id
     * @return \Illuminate\Http\JsonResponse
     */
    private function mixturePay($id,$user_Id)
    {
        try {
            //获取混合信息
            $mixture_info = MakeEffectsFile::findOrFail($id);

            //判断用户是否为VIP
            $is_vip = User::find($user_Id)->is_vip;

            //会员是否免费
            $vip_free = $mixture_info -> vipfree;

            //用户为会员且资源为用户免费
            if($vip_free && $is_vip) return response()->json(['message'=>'success'],200);

            //混合素材的积分
            $mixture_integral = $mixture_info -> integral;

            //获取用户积分
            $user_integral = User\UserIntegral::where('user_id',$user_Id)->first();

            //判断用户积分
            if(is_null($user_integral) || $mixture_integral > $user_integral->integral_count) return response()->json(['message'=>'Lack of integral'],205);

            //事务开始
            \DB::beginTransaction();

            //支付原因
            $pay_reason = '混合:' . $mixture_info->name;

            //支付类型
            $type = 'mixture';

            //会员免费 但是用户不是会员
            if ($vip_free && !$is_vip) {
               return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

            //会员不免费
            if (!$vip_free){
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

        }catch (\Exception $e){
            \DB::rollBack();
            return response()->json(['message'=>'not_found'],404);
        }

    }

    /**
     * @param $id
     * @param $user_Id
     * @return \Illuminate\Http\JsonResponse
     */
    private function filter($id,$user_Id)
    {
        try {
            //获取混合信息
            $mixture_info = MakeFilterFile::findOrFail($id);

            //判断用户是否为VIP
            $is_vip = User::find($user_Id)->is_vip;

            //会员是否免费
            $vip_free = $mixture_info -> vipfree;

            //用户为会员且资源为用户免费
            if($vip_free && $is_vip) return response()->json(['message'=>'success'],200);

            //混合素材的积分
            $mixture_integral = $mixture_info -> integral;

            //获取用户积分
            $user_integral = User\UserIntegral::where('user_id',$user_Id)->first();

            //判断用户积分
            if(is_null($user_integral) || $mixture_integral > $user_integral->integral_count) return response()->json(['message'=>'Lack of integral'],205);

            //事务开始
            \DB::beginTransaction();

            //支付原因
            $pay_reason = '滤镜:' . $mixture_info->name;

            //支付类型
            $type = 'filter';

            //会员免费 但是用户不是会员
            if ($vip_free && !$is_vip) {
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

            //会员不免费
            if (!$vip_free){
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

        }catch (\Exception $e){
            \DB::rollBack();
            return response()->json(['message'=>'not_found'],404);
        }
    }

    /**
     * @param $id
     * @param $user_Id
     * @return \Illuminate\Http\JsonResponse
     */
    private function template($id,$user_Id)
    {
        try {
            //获取混合信息
            $mixture_info = MakeTemplateFile::findOrFail($id);

            //判断用户是否为VIP
            $is_vip = User::find($user_Id)->is_vip;

            //会员是否免费
            $vip_free = $mixture_info -> vipfree;

            //用户为会员且资源为用户免费
            if($vip_free && $is_vip) return response()->json(['message'=>'success'],200);

            //混合素材的积分
            $mixture_integral = $mixture_info -> integral;

            //获取用户积分
            $user_integral = User\UserIntegral::where('user_id',$user_Id)->first();

            //判断用户积分
            if(is_null($user_integral) || $mixture_integral > $user_integral->integral_count) return response()->json(['message'=>'Lack of integral'],205);

            //事务开始
            \DB::beginTransaction();

            //支付原因
            $pay_reason = '模板:' . $mixture_info->name;

            //支付类型
            $type = 'template';

            //会员免费 但是用户不是会员
            if ($vip_free && !$is_vip) {
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

            //会员不免费
            if (!$vip_free){
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

        }catch (\Exception $e){
            \DB::rollBack();
            return response()->json(['message'=>'not_found'],404);
        }
    }

    /**
     * @param $id
     * @param $user_Id
     * @return \Illuminate\Http\JsonResponse
     */
    private function audio($id,$user_Id)
    {
        try {
            //获取混合信息
            $mixture_info = MakeAudioFile::findOrFail($id);

            //判断用户是否为VIP
            $is_vip = User::find($user_Id)->is_vip;

            //会员是否免费
            $vip_free = $mixture_info -> vipfree;

            //用户为会员且资源为用户免费
            if($vip_free && $is_vip) return response()->json(['message'=>'success'],200);

            //混合素材的积分
            $mixture_integral = $mixture_info -> integral;

            //获取用户积分
            $user_integral = User\UserIntegral::where('user_id',$user_Id)->first();

            //判断用户积分
            if(is_null($user_integral) || $mixture_integral > $user_integral->integral_count) return response()->json(['message'=>'Lack of integral'],205);

            //事务开始
            \DB::beginTransaction();

            //支付原因
            $pay_reason = '音乐:' . $mixture_info->name;

            //支付类型
            $type = 'audio';

            //会员免费 但是用户不是会员
            if ($vip_free && !$is_vip) {
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

            //会员不免费
            if (!$vip_free){
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

        }catch (\Exception $e){
            \DB::rollBack();
            return response()->json(['message'=>'not_found'],404);
        }
    }

    /**
     * @param $id
     * @param $user_Id
     * @return \Illuminate\Http\JsonResponse
     */
    private function audioeffect($id,$user_Id)
    {
        try {
            //获取混合信息
            $mixture_info = MakeAudioEffectFile::findOrFail($id);

            //判断用户是否为VIP
            $is_vip = User::find($user_Id)->is_vip;

            //会员是否免费
            $vip_free = $mixture_info -> vipfree;

            //用户为会员且资源为用户免费
            if($vip_free && $is_vip) return response()->json(['message'=>'success'],200);

            //混合素材的积分
            $mixture_integral = $mixture_info -> integral;

            //获取用户积分
            $user_integral = User\UserIntegral::where('user_id',$user_Id)->first();

            //判断用户积分
            if(is_null($user_integral) || $mixture_integral > $user_integral->integral_count) return response()->json(['message'=>'Lack of integral'],205);

            //事务开始
            \DB::beginTransaction();

            //支付原因
            $pay_reason = '音频:' . $mixture_info->name;

            //支付类型
            $type = 'audioeffect';

            //会员免费 但是用户不是会员
            if ($vip_free && !$is_vip) {
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

            //会员不免费
            if (!$vip_free){
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

        }catch (\Exception $e){
            \DB::rollBack();
            return response()->json(['message'=>'not_found'],404);
        }
    }

    /**
     * @param $id
     * @param $user_Id
     * @return \Illuminate\Http\JsonResponse
     */
    private function fragment($id,$user_Id)
    {
        try {
            //获取混合信息
            $mixture_info = Fragment::findOrFail($id);

            //判断用户是否为VIP
            $is_vip = User::find($user_Id)->is_vip;

            //会员是否免费
            $vip_free = $mixture_info -> vipfree;

            //用户为会员且资源为用户免费
            if(!$vip_free && $is_vip) return response()->json(['message'=>'success'],200);

            //混合素材的积分
            $mixture_integral = $mixture_info -> integral;

            //获取用户积分
            $user_integral = User\UserIntegral::where('user_id',$user_Id)->first();

            //判断用户积分
            if(is_null($user_integral) || $mixture_integral > $user_integral->integral_count) return response()->json(['message'=>'Lack of integral'],205);

            //事务开始
            \DB::beginTransaction();

            //支付原因
            $pay_reason = '片段:' . $mixture_info->name;

            //支付类型
            $type = 'fragment';

            //会员免费 但是用户不是会员
            if (!$vip_free && !$is_vip) {
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

            //会员不免费
            if ($vip_free){
                return $this->doinsert($user_Id,$type,$id,$mixture_integral,$pay_reason);
            }

        }catch (\Exception $e){
            \DB::rollBack();
            return response()->json(['message'=>'not_found'],404);
        }
    }

    /**
     * @param $user_Id
     * @param $type
     * @param $id
     * @param $deduct_integral
     * @param $pay_reason
     * @return \Illuminate\Http\JsonResponse
     */
    private function doinsert($user_Id,$type,$id,$deduct_integral,$pay_reason)
    {
        //判断是否下载过
        $is_download = \DB::table('user_integral_expend_log')->where('user_id',$user_Id)->where('type',$type)->where('type_id',$id)->first();

        if (!is_null($is_download)) return response()->json(['message' => 'success'], 200);

        //扣除积分
        $deduct_result = User\UserIntegral::where('user_id', $user_Id)->decrement('integral_count', $deduct_integral);

        //生成订单号
        $order_number = date('YmdHis') . rand(10000, 99999);

        //时间
        $time = time();

        //记录消费
        $record_result = \DB::table('user_integral_expend_log')->insert([
            'user_id'       => $user_Id,
            'pay_number'    => $order_number,
            'pay_count'     => $deduct_integral,
            'type'          => $type,
            'type_id'       => $id,
            'pay_reason'    => $pay_reason,
            'status'        => 1,
            'create_at'     => $time,
        ]);

        if ($deduct_result && $record_result) {
            //确认操作
            \DB::commit();

            return response()->json(['message' => 'success'], 200);
        }else{

            \DB::table('user_integral_expend_log')->insert([
                'user_id'       => $user_Id,
                'pay_number'    => $order_number,
                'pay_count'     => $deduct_integral,
                'type'          => $type,
                'type_id'       => $id,
                'pay_reason'    => $pay_reason,
                'status'        => 0,
                'create_at'     => $time,
            ]);

            //回滚
            \DB::rollBack();
            return response()->json(['message' => 'failed'], 500);
        }
    }
}
