<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/3
 * Time: 12:24
 */

namespace App\Api\OAuth;


class QQOAuth extends OAuth
{
    public function verify($open_id, $access_token)
    {
        $response = $this->curl(
            'https://graph.qq.com/oauth2.0/me?access_token=' . $access_token,
            [],
            [],
            'GET'
        );
        $response=preg_replace('/.+?({.+}).+/','$1',$response);
        $response = json_decode($response,true);
        if (! isset($response['openid']) || $response['openid'] != $open_id) {
            return false;
        }
        return 'ok';
    }
}