<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/3
 * Time: 11:36
 */

namespace App\Api\OAuth;


class WeChatOAuth extends OAuth
{
    public function verify($open_id, $access_token)
    {
        $response = $this->curl(
            'https://api.weixin.qq.com/sns/auth?access_token=' . $access_token . '&' . 'openid=' . $open_id ,
            [],
            [],
            'GET'
        );
        $response = json_decode($response,true);

        if (!isset($response['errcode']) || $response['errcode'] != 0) {

            return 0;
        }

        // 原代码
//        return true;

        // 修改后的代码
        return 'ok';
    }
}