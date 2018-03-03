<?php
/**
 * Description:
 * User: Yy
 * Date: 2018/3/3 0003
 */

namespace App\Services;


use App\Models\PrivateLetter;
use App\Models\User;

class SendPrivateLetter
{
    public static function send($userId,$content,$admin)
    {
        //生成私信通知
        $admin_id = User::where('nickname',$admin)->first();

        $time = time();
        $arr = [
            'from' => $admin_id->id,
            'to'    => $userId,
            'content'   => $content,
            'user_type' => '1',
            'read_from'  => '1',
            'created_at' => $time,
            'updated_at' =>$time,
        ];
        PrivateLetter::create($arr);
    }
}