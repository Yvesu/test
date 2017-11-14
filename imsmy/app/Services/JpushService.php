<?php

namespace App\Services;

use JPush\Client as JPush;

/**
 * 发布推送消息的服务
 * Class JpushService
 * @package App\Services
 */
class JpushService
{
    //
    protected $client ;

    public function __construct(
        $app_key = '788a65f61b0a32c1e9cc0c72',
        $master_secret ='1ec15d0710a0fcf961593c30'
    )
    {
        $this->client = new JPush($app_key, $master_secret);
    }

    /**
     * 发布消息至单个用户
     *
     * @param $user 可为字符串，也可为数组
     * @param $content 字符串
     */
    public function sendMessageToRegistrationId($user,$content)
    {
        $push_payload = $this->client->push()
            ->setPlatform('all')
            ->addRegistrationId($user)
            ->message($content);

        // 发送
        $this -> send($push_payload);
    }

    /**
     * 发布消息至所有用户
     *
     * @param $content string
     */
    public function sendNotificationToAllAudience($content)
    {
        $push_payload = $this->client->push()
            ->setPlatform('all')
            ->addAllAudience()
            ->setNotificationAlert($content);

        // 发送
        $this -> send($push_payload);
    }

    /**
     * 发布消息至指定机型用户
     *
     * @param array array('ios', 'android', 'winphone')
     * @param $content
     */
    public function sendNotificationToPlatform(Array $platform,$content)
    {
        $push_payload = $this->client->push()
            ->setPlatform($platform)
            ->addAllAudience()
            ->setNotificationAlert($content);

        // 发送
        $this -> send($push_payload);
    }

    /**
     * 发送消息的剩余部分
     *
     * @param $push_payload
     */
    final public function send($push_payload)
    {
        try {
            $push_payload->send();
            
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            // try something here
            print $e;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            // try something here
            print $e;
        }
    }
}