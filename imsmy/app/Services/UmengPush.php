<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/24 0024
 * Time: 下午 14:50
 */

namespace App\Services;


use App\Models\User;
use Zzl\Umeng\Facades\Umeng;

class UmengPush
{
    /**
     * 单播
     * @param $device_token
     */
    public function androidUnicast($id,$message)
    {
        $user = User::findOrFail($id);
        $device_token = $user->umeng_device_token;
        $predefined = [
            'ticker'    =>  'Hi!Video·'.date('H:i'),
            'title'     =>  $message['title'],
            'text'      =>  $message['text'],
            'after_open'    =>  'go_app',
        ];
        $extraField = [];
        Umeng::android()->sendUnicast($device_token,$predefined,$extraField);
    }

    public function iosUnicast($id,$message)
    {
        $user = User::findOrFail($id);
        $device_token = $user->umeng_device_token;
        $predefined = [
            'alert'     =>  'Hi!Video·'.date('H:i'),
            'title'     =>  $message['title'],
            'subtitle'  =>  $message['subtitle'],
            'body'      =>  $message['body'],
            ];
        $customField = array();
        Umeng::ios()->sendUnicast($device_token,$predefined,$customField); //单播
    }
}