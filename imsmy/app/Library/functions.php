<?php
/**
 * 字串加密方式  常用于密码
 * @param $str 原始字串
 * @return string
 */
function strEncrypt($str) {
    return md5(md5(PWD_KEY . $str));
}

/**
 * 版主验证token
 * @param $catId
 * @param $uid
 * @return string
 */
function cateWebAdminToken($catId,$uid){
    return md5(md5(PWD_KEY . $catId .'-'.$uid));
}
/**
 * 判断登录用户是否可查看操作界面
 * @param $menuId
 * @return bool
 */
function menuShowCheck($menuId){
    if(empty(session('admin')))
        return  false;

    $admin = session('admin');

    if(1==$admin['id']) return true;

    if(empty($admin['_menu_ids']))
        return false;

    return in_array($menuId,$admin['_menu_ids']);
}



/**
 * 把数组以ID作为  数组key
 * @param   $arr        待处理的数组
 * @return array        处理好的数组
 * 等继续处理 必须是二维数组，且第二维中有key为id
 */
function updateArrIndexForId($arr) {
    $tempArr = [];
    foreach($arr as $v) {
        if(is_array($v)){
            $tempArr[$v['id']] = $v;
        }
    }
    return $tempArr;
}

/**
 * 返回用户头像，设置默认
 * @param  string $img 数据库头像图片地址
 * @return string      拼接完成的图片地址
 */
function getUserHead($img = null) {
    if(empty($img))
        return '/asset/images/user_head_portrait.gif';

    //判断是头像是否存在
    $imgPath = getImg($img);
    if(file_exists(getcwd().'/'.ltrim($imgPath,'/'))){
        return $imgPath;
    }

    return '/asset/images/user_head_portrait.gif';

}

/**
 * 返回upload上传地址
 * @param  [string] $img [数据库中取出图片地址]
 * @return [string]      [拼接完成的地址]
 */
function getImg($img) {
    if(empty($img))
        return null;

    return '/uploads/'.$img;
}

/**
 * 上传函数，判断是否上传 上传失败则返回false,上传成功返回文件名称
 * @param  [object] $request   [上传接收的数据]
 * @param  [string] $fieldName [post接收的文件name]
 * @param  [string] $userOldPicName [原有的上传图片]
 * @return [string]            [返回图片地址]
 */
function uploadFile($request,$fieldName,$userOldPicName = null) {
    //判断是否有上传
    if(!$request->hasFile($fieldName))
        return false;
    //获取上传信息
    $file = $request->file($fieldName);

    //确认上传的文件是否成功
    if($file->isValid()){
        $picname = $file->getClientOriginalName(); //获取上传原文件名
        $ext = $file->getClientOriginalExtension(); //获取上传文件名的后缀名

        // 图片名字
        $head_name = date('YmdHis').'_'.rand(10000,99999).".".$ext;

        //执行移动上传文件
        if($file->move("./uploads/",$head_name)) {
            unset($picname); // 销毁缓存图片文件

            //如果有原图片，删除原图片
            if($userOldPicName)
                deleteUploadFile($userOldPicName);

            if(file_exists("./uploads/".$head_name))
                return $head_name;
        }
    }
    return false;
}
/**
 * 删除图片
 */
function deleteUploadFile($fileName) {
    @unlink('./uploads/'.$fileName);
}

/**
 * 获取时间
 * @return mixed
 */
function getTime(){
    return $_SERVER['REQUEST_TIME'];
}

/**
 * 数据库数据转换Html实体
 * @param $data
 * @return mixed
 */
function dbToHtmlSpecialChars($data,$flag=false){
    if(!$data) return $data;
    foreach($data as $k=>$v){
//        $data[$k] = htmlspecialchars($v);
//        $data[$k] = str_replace(["'",'"','.',])
    }

    return $data;
}


