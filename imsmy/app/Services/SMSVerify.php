<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/25
 * Time: 10:08
 */

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SMSVerify
{
    // 配置项
    private $api = 'https://webapi.sms.mob.com/sms/verify';
    private $appKey = '1208a39deb8c3';


    /**
     * 发起一个post请求到指定接口
     *
     * @param string $api 请求的接口
     * @param array $params post参数
     * @param int $timeout 超时时间
     * @return string 请求结果
     */
    private function postRequest( $api, array $params = array(), $timeout = 30 ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $api );
        // 以返回的形式接收信息
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        // 设置为POST方式
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
        // 不验证https证书
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Accept: application/json',
        ) );
        // 发送数据
        $response = curl_exec( $ch );
        // 不要忘记释放资源
        curl_close( $ch );
        return $response;
    }

    public function verify(array $data)
    {
        $data['appkey'] = $this->appKey;

        // 发送验证码
        $response = json_decode($this->postRequest($this->api,$data));
        if(isset($response) && isset($response->status) && 200 == $response->status){
            Cache::put('SMS'.$data['phone'],true,30);
        }
        return $response;
    }

}