<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/3
 * Time: 11:48
 */

namespace App\Api\OAuth;


class WeiboOAuth extends OAuth
{
    public function verify($open_id, $access_token)
    {
        $options = [
            'access_token' => $access_token
        ];

        $response = $this->curl(
            'https://api.weibo.com/oauth2/get_token_info',
            $options,
            [],
            'POST'
        );
        $response = json_decode($response,true);
        if (! isset($response['uid']) || $response['uid'] != $open_id || $response['expire_in'] <= 0) {
            return false;
        }
        return 'ok';
    }
}