<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/3
 * Time: 14:22
 */

namespace App\Api\OAuth;


class OAuthManager
{
    private $oauth;

    public function __construct($name)
    {
        switch ($name) {
            case 'qq':
                $this->oauth = new QQOAuth();
                break;
            case 'weixin':
                $this->oauth = new WeChatOAuth();
                break;
            case 'weibo':
                $this->oauth = new WeiboOAuth();
                break;
            default:
                $this->oauth = null;
        }
    }

    /**
     * 认证openid,access_token 是否有效
     * @param $open_id
     * @param $access_token
     * @return bool
     */
    public function verify($open_id, $access_token)
    {
        if ($this->oauth === null) {
            return false;           //目前认为声明类时，传入参数无效时返回false,想到更好的办法再改
        }
        return $this->oauth->verify($open_id,$access_token);
    }
}