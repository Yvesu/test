<?php
namespace App\Api\Controllers;

use App\Models\Friend;
use App\Models\User;

class CommonController extends BaseController
{
    // 用户的隐私设置
    public function personalAllow($from_id,$to_id,$option)
    {
        // 判断是否为朋友
        if(Friend::ofIsFriend($from_id,$to_id)->first()) return true;

        // 判断是否允许陌生人操作
        if(1 === User::findOrFail($to_id)->$option) return true;

        return false;
    }
}