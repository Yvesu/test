<?php
use Illuminate\Support\Facades\Cache;
use App\Models\Topic;
use App\Models\Tweet;
use App\Models\Activity;
use App\Models\User;

/**
 * 全局函数
 * Created by PhpStorm.
 * User: mabiao
 * Date: 2016/4/11
 * Time: 18:13
 */

if (! function_exists('isActiveLabel')) {
    /**
     * APP -> 相机 -> 滤镜 标签选中状态显示
     * @param $key
     * 路径中active(滤镜类型状态)或id(滤镜ID)
     * @param $value
     * 路径中active(滤镜类型状态值)或id(滤镜ID值)
     * @param $active
     * 选中默认式样
     * @param $default
     * 没选中默认式样
     * @return string
     * 标签显示选中或未选中状态
     */
    function isActiveLabel($key,$value,$active = 'label-primary',$default = 'label-default')
    {
        return is_null(\Request::get('active')) && is_null(\Request::get('id')) && is_null(\Request::get('official')) //没有参数时，默认正常选中
            ? ('active' == $key && 1 == $value ? $active : $default) : (\Request::get($key) === (string)$value                           //有参数时，按照参数匹配进行选中设置
                ? $active : $default);
    }
}

// 后台频道广告
if (! function_exists('isStyleLabel')) {
    /**
     * APP -> 相机 -> 滤镜 标签选中状态显示
     * @param $key
     * 路径中active(滤镜类型状态)或id(滤镜ID)
     * @param $value
     * 路径中active(滤镜类型状态值)或id(滤镜ID值)
     * @param $active
     * 选中默认式样
     * @param $default
     * 没选中默认式样
     * @return string
     * 标签显示选中或未选中状态
     */
    function isStyleLabel($key,$value,$active = 'label-primary',$default = 'label-default')
    {
        //没有参数时，默认正常选中  //有参数时，按照参数匹配进行选中设置
        return is_null(\Request::get('style')) ? ('style' == $key && 1 == $value ? $active : $default) : (\Request::get($key) === (string)$value ? $active : $default);
    }
}

if (! function_exists('isActiveMenu')) {
    function isActiveMenu($routeName,$titleBar=true)
    {
        if($routeName == \Request::route()->getName()){
            if(true == $titleBar){
                return 'class=active';
            }else{
                return 'active';
            }
        }elseif(str_is($routeName.'*',\Request::route()->getName())){
            if(true == $titleBar){
                return 'class=open';
            }else{
                return 'open';
            }
        }
    }
}

if (! function_exists('downloadUrl')) {
    function downloadUrl($key)
    {
        return \CloudStorage::downloadUrl($key);
    }
}

if (! function_exists('random_color')) {
    function random_color()
    {
        $color = [
            '#1BBC9D', '#2FCC71', '#3598DC', '#9C59B8', '#34495E',
            '#16A086', '#27AE61', '#2A80B9', '#8F44AD', '#2D3E50',
            '#F1C40F', '#E77E23', '#E84C3D'           , '#96A6A6',
            '#F49C14', '#D55401', '#C1392B'           , '#929BA4',
        ];
        return $color[rand(0,sizeof($color) - 1)];
    }
}

/**
 * 正则匹配@用户
 */
if (! function_exists('regex_at')) {
    function regex_at ($value) {
        $str = explode('//', $value)[0];
        $result = preg_match_all('/(@[\x{4e00}-\x{9fa5}\w_-]+)/u', $str, $output);
        return [$result,$output];
    }
}

/**
 * 正则匹配#话题#
 */
if (! function_exists('regex_topic')) {
    function regex_topic ($value) {
        $str = explode('//', $value)[0];
        $result = preg_match_all('/(#[\x{4e00}-\x{9fa5}\w_-]+#)/u', $str, $output);

        // 返回匹配成功的次数及匹配的结果集的数组
        return [$result,$output];
    }
}

/**
 * 正则判断，只能是中英文、数字、短横杠及下划线
 */
if (! function_exists('regex_name')) {
    function regex_name ($value) {
        return preg_match('/^[\x{4e00}-\x{9fa5}\w-_]+$/ium',$value);
    }
}

/**
 * 正则判断，只能是英文、数字
 */
if (! function_exists('regex_pwd')) {
    function regex_pwd ($value) {
        return preg_match('/^[A-Za-z0-9]{6,16}$/',$value);
    }
}

/**
 * 正则判断，判断是否为规范日期 Ymd
 */
if (! function_exists('regex_date')) {
    function regex_date ($value) {
        return preg_match('/^20\d{2}(0[1-9])|([10-12])[0-3]\d$/',$value);
    }
}

/**
 * 手机号匹配
 */
if (! function_exists('pregTP')) {

    function pregTP($value){
        $rule = '/^1\d{10}$/';
        return preg_match($rule,$value);
    }
}

/**
 * 以下为新增函数
 */
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

    $admin = objectToArray(session('admin'));

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
//        $data[$k] = str_replace(["'",'"','.',]);
    }

    return $data;
}

/** 将存入数据库的内容进行过滤和进行转换
 * @param $post
 * @return mixed|string
 */
function post_check($post)
{
    $post = str_replace("_", "\_", $post); // 把 '_'过滤掉
    $post = str_replace("%", "\%", $post); // 把' % '过滤掉
    $post = nl2br($post); // 回车转换
    $post= htmlspecialchars($post,ENT_QUOTES); // html标记转换
    return $post;
}

/**
 * 防止xss攻击
 * @param $val
 * @return mixed
 */
function removeXSS($val) {
    $post= htmlspecialchars($val,ENT_QUOTES); // html标记转换
    return $post;
}

// 对象转数组
function objectToArray($e){
    $e=(array)$e;
    foreach($e as $k=>$v){
        if( gettype($v)=='resource' ) return;
        if( gettype($v)=='object' || gettype($v)=='array' )
            $e[$k]=(array)objectToArray($v);
    }
    return $e;
}

// 数组转对象
function arrayToObject($e){
    $e=(object)$e;
    foreach($e as $k=>$v){
        if( gettype($v)=='resource' ) return;
        if( gettype($v)=='object' || gettype($v)=='array' )
            $e[$k]=(object)arrayToObject($v);
    }
    return $e;
}

/**
 * 打乱数组
 * @param $array
 * @return bool|void
 */
function array_quake(&$array) {
    if (is_array($array)) {
        $keys = array_keys($array); // We need this to preserve keys
        $temp = $array;
        $array = NULL;
        shuffle($temp); // Array shuffle
        foreach ($temp as $k => $item) {
            $array[$keys[$k]] = $item;
        }
        return;
    }
    return false;
}

/**
 * 正则判断，判断是否有特殊字符，如admin/管理/官方
 */
if (! function_exists('regex_forbid')) {
    function regex_forbid ($value) {
        return preg_match('/(admin)|(\x{7BA1}\x{7406})|(\x{5B98}\x{65B9})/ium',$value);
    }
}

/**
 * 正则判断，判断是否为合规的url
 */
if (! function_exists('regex_url')) {
    function regex_url ($value) {
        $preg="/^(https?:\/\/)?(((www\.)?[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)?\.([a-zA-Z]+))|(([0-1]?[0-9]?[0-9]|2[0-5][0-5])\.([0-1]?[0-9]?[0-9]|2[0-5][0-5])\.([0-1]?[0-9]?[0-9]|2[0-5][0-5])\.([0-1]?[0-9]?[0-9]|2[0-5][0-5]))(\:\d{0,4})?)(\/[\w- .\/?%&=]*)?$/i";

        return preg_match($preg,$value);
    }
}

/**
 *      把秒数转换为 分:秒 的格式,超出60分钟显示 时:分:秒 的格式
 *      @param Int $times 时间，单位 秒
 *      @return String
 */
if (! function_exists('secondsToMinute')) {
    function secondsToMinute($seconds){
        if(intval($seconds) < 60){
            $tt ="00:".sprintf("%02d",intval($seconds%60));
        }elseif(intval($seconds)>=60*60){
            $t= sprintf("%02d",intval($seconds/3600));
            $h =sprintf("%02d",intval($seconds/60)-$t*60);
            $s =sprintf("%02d",intval($seconds%60));
            if($s == 60){
                $s = sprintf("%02d",0);
                ++$h;
            }
            if($h == 60){
                $h = sprintf("%02d",0);
                ++$t;
            }
            if($t){
                $t  = sprintf("%02d",$t);
            }
            $tt= $t.":".$h.":".$s;
        }else{
            $h =sprintf("%02d",intval($seconds/60));
            $s =sprintf("%02d",intval($seconds%60));
            if($s == 60){
                $s = sprintf("%02d",0);
                ++$h;
            }
            $tt= $h.":".$s;
        }

        return  $seconds>0?$tt:'00:00';
    }
}

/**
 * 检测访问的ip是否为规定的允许的ip 有待优化
 * Enter description here ...
 */
function check_ip(){

    // 所允许的IP
//    $ALLOWED_IP=array('111.199.137.72','127.0.0.1');
    $ALLOWED_IP=array('114.254.222.211','127.0.0.1');

    // 获取IP
    $IP=getIP();

    #限制IP
    if(!in_array($IP,$ALLOWED_IP)) {
        header('HTTP/1.1 403 Forbidden');
        abort(404);
    }
}

/**
 * TODO 暂时停用
 * 将缓存中的数据进行 修改
 * @param string $cache_type   要进行自增 +1 的缓存类型
 * @param int $id       类型所属表中的id
 * @param array $field    类型所属表中的要修改字段 一维或二维数组
 * @param int $time     缓存保存的时间
 * @return bool
 */
function cache_change_field($cache_type, $id, $field, $time = 120) {

    // 获取表的单条缓存数据
    $cache = cache_table_id($cache_type, $id, $time);

    foreach($field as $key => $value){

        if(!is_array($value)){
            $cache -> $key = $value;
        } else {
            foreach($field as $k => $v){
                $cache -> $k = $v;
            }
        }
    }

    // 将对应表中的改动记录保存至相关的缓存数据中，待调用定时任务时进行统一处理，减少数据库的写入次数，提高效率
    cache_change_command($cache_type.'_change', $id);

    return $cache;
}

/**
 * 将缓存中的数据进行自动 +1  TODO 暂停使用缓存
 * @param string $cache_type   要进行自增 +1 的缓存类型
 * @param int $id       类型所属表中的id
 * @param string $field    类型所属表中的要修改字段
 * @param int $time     缓存保存的时间
 * @return bool
 */
function cache_change_increment($cache_type, $id, $field, $time = 120) {

    // 获取表的单条缓存数据
    $cache = cache_table_id($cache_type, $id, $time);

    if(!is_array($field)){
        $cache -> increment($field);
    } else {
        foreach($field as $value){
            $cache -> increment($value);
        }
    }

    // 将对应表中的改动记录保存至相关的缓存数据中，待调用定时任务时进行统一处理，减少数据库的写入次数，提高效率
    cache_change_command($cache_type.'_change', $id);

    return true;
}

/**
 * 将缓存中的数据进行自动 -1  TODO 暂停使用缓存
 * @param string $cache_type   要进行自增 +1 的缓存类型
 * @param int $id       类型所属表中的id
 * @param int $field    类型所属表中的要修改字段
 * @param int $time     缓存保存的时间
 * @return bool
 */
function cache_change_decrement($cache_type, $id, $field, $time = 120) {

    // 获取表的单条缓存数据
    $cache = cache_table_id($cache_type, $id, $time);

    $cache -> decrement($field);

    // 将对应表中的改动记录保存至相关的缓存数据中，待调用定时任务时进行统一处理，减少数据库的写入次数，提高效率
    cache_change_command($cache_type.'_change', $id);

    return true;
}

/**
 *  TODO 暂停使用缓存
 * 获取表中的单条数据，命名缓存名为 table_id, for example：topic_id
 * @param string $table 表名简称
 * @param int $id   对应表中的id
 * @param int $time
 * @return mixed
 */
function cache_table_id($table, $id, $time = 120) {

    $cache = Cache::remember($table.'_'.$id, $time, function() use($id, $table) {

        switch($table) {
            case 'topic' :
                return Topic::findOrFail($id);
                break;
            case 'tweet' :
                return Tweet::findOrFail($id);
                break;
            case 'activity' :
                return Activity::findOrFail($id);
                break;
            case 'user' :
                return User::findOrFail($id);
                break;
        }
    });

    return $cache;
}

/**
 *  TODO 暂停使用缓存
 * 将 tweet/topic/activity/user 等表改动的数据id保存至缓存中，待定时任务调用时统一处理，减少数据库写入次数，方便快捷
 * @param string $cache 缓存名称
 * @param int $id   缓存对应表中的id
 * @param int $time 缓存时长，默认是120分钟
 * @return bool
 */
function cache_change_command($cache, $id, $time = 120) {

    // 保存改动的话题id到缓存，待定时任务进行批量修改
    $cache_change = Cache::remember($cache, $time, function(){

        return [];
    });

    $cache_change[$id] = $id;

    Cache::put($cache, $cache_change, 120);

    return true;
}

/**
 * 检测访问的ip是否为规定的允许的ip 可选择网段
 * Enter description here ...
 */
//function check_ip(){
//    $ALLOWED_IP=array('192.168.2.*','127.0.0.1','192.168.2.49');
//    $IP=getIP();
//    $check_ip_arr= explode('.',$IP);//要检测的ip拆分成数组
//    #限制IP
//    if(!in_array($IP,$ALLOWED_IP)) {
//        foreach ($ALLOWED_IP as $val){
//            if(strpos($val,'*')!==false){//发现有*号替代符
//                $arr=array();//
//                $arr=explode('.', $val);
//                $bl=true;//用于记录循环检测中是否有匹配成功的
//                for($i=0;$i<4;$i++){
//                    if($arr[$i]!='*'){//不等于*  就要进来检测，如果为*符号替代符就不检查
//                        if($arr[$i]!=$check_ip_arr[$i]){
//                            $bl=false;
//                            break;//终止检查本个ip 继续检查下一个ip
//                        }
//                    }
//                }//end for
//                if($bl){//如果是true则找到有一个匹配成功的就返回
//                    return;
//                    die;
//                }
//            }
//        }//end foreach
//        header('HTTP/1.1 403 Forbidden');
//        echo "Access forbidden";
//        die;
//    }
//}

/**
 * 获得访问的IP
 * Enter description here ...
 */
function getIP() {
    return isset($_SERVER["HTTP_X_FORWARDED_FOR"])?$_SERVER["HTTP_X_FORWARDED_FOR"]
        :(isset($_SERVER["HTTP_CLIENT_IP"])?$_SERVER["HTTP_CLIENT_IP"]
            :$_SERVER["REMOTE_ADDR"]);
}


/**
 * 舍去法获取浮点数，后两位，自己封装，待优化
 */
if( !function_exists('floorFloat')){

    function floorFloat($bonus,$count){

        return substr(number_format($bonus/$count,3,'.',''),0,-1);
    }
}


//二维数组去掉重复值,并保留键值
function array_unique_fb($array2D){
    foreach ($array2D as $k=>$v){
        $v=join(',',$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        $temp[$k]=$v;
    }
    $temp=array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
    foreach ($temp as $k => $v){
        $array=explode(',',$v); //再将拆开的数组重新组装
        //下面的索引根据自己的情况进行修改即可
        $temp2[$k]['id'] =$array[0];
        $temp2[$k]['title'] =$array[1];
        $temp2[$k]['keywords'] =$array[2];
        $temp2[$k]['content'] =$array[3];
    }
    return $temp2;
}

/**
 * 多维数组去重
 * @param $array
 * @return array
 */

function mult_unique($array)
{
    $return = array();
    foreach($array as $key=>$v)
    {
        if(!in_array($v, $return))
        {
            $return[$key]=$v;
        }
    }
    return $return;
}





