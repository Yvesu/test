<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/3
 * Time: 17:18
 */

namespace App\Services;

use Cache;

/**
 * 环信 即时通讯封装SDK
 * Class EaseMob
 * @package App\Services
 */
class EaseMob
{
    /**
     * 环信提供的Client ID
     * @var string
     */
    private $client_id;

    /**
     * 环信提供的Client Secret
     * @var string
     */
    private $client_secret;

    /**
     * 环信提供的组织名
     * @var string
     */
    private $org_name;

    /**
     * 环信提供的APP 名称
     * @var string
     */
    private $app_name;

    /**
     * 环信请求的Api基础URL
     * @var string
     */
    private $url;

    /**
     * EaseMob constructor.
     * @param $options
     */
    public function __construct($options)
    {
        $this->client_id     = isset($options['client_id']) ? $options['client_id'] : '';
        $this->client_secret = isset($options['client_secret']) ? $options['client_secret'] : '';
        $this->org_name      = isset($options['org_name']) ? $options['org_name'] : '';
        $this->app_name      = isset($options['app_name']) ? $options['app_name'] : '';
        if(!empty($this->org_name) && !empty($this->app_name)){
            $this->url = 'https://a1.easemob.com/' . $this->org_name . '/' . $this->app_name . '/';
        }
    }

    /**
     * 获取授权Token 本地缓存
     * @return string
     * @throws \Exception
     */
    public function getToken()
    {
        $token = Cache::get('easeMob:token');
        if (! isset($token)) {
            $options = [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret
            ];
            $url  = $this->url . 'token';
            $tokenResult = $this->curl($url,$options,[],'POST');
            // expire time - 1800
            Cache::put('easeMob:token',$tokenResult['access_token'],($tokenResult['expires_in'] - 1800) /60);
            $token = $tokenResult['access_token'];
        }
        return "Authorization:Bearer ".$token;
    }
    //-----------------------用户相关方法-------------------------------------//
    /**
     * @param $username
     * @param $password
     * @param string $nickname
     * @return mixed
     * @throws \Exception
     */
    public function createUser($username, $password,$nickname = null)
    {
        $url = $this->url . 'users';
        $options = [
            'username' => $username,
            'password' => $password,
            'nickname' => isset($nickname) ? $nickname : $username
        ];
        $header=[$this->getToken()];
        $result=$this->curl($url,$options,$header,'POST');
        return $result;
    }

    /**
     * 重置密码
     * @param $username
     * @param $newPassword
     * @return mixed
     * @throws \Exception
     */
    public function resetPassword($username, $newPassword)
    {
        $url = $this->url . 'users/' . $username . '/password';
        $options = ['newpassword' => $newPassword];
        $header = [$this->getToken()];
        $result = $this->curl($url,$options,$header,'PUT');
        return $result;
    }

    /**
     * 编辑Nickname
     * @param $username
     * @param $nickname
     * @return mixed
     * @throws \Exception
     */
    public function editNickname($username, $nickname)
    {
        $url = $this->url . 'users/' .$username;
        $options = ['nickname' => $nickname];
        $header=[$this->getToken()];
        $result = $this->curl($url,$options,$header,'PUT');
        return $result;
    }

    public function getBlacklist($username)
    {
        $url = $this->url . 'users/' . $username . '/blocks/users';
        $header = [$this->getToken()];
        $result = $this->curl($url,[],$header,'GET');
        return $result;
    }

    /**
     * 添加黑名单人员，可以添加多个或一个
     * @param $username
     * @param $blacklist
     * @return mixed
     * @throws \Exception
     */
    public function addUserForBlacklist($username,$blacklist)
    {
        $url = $this->url . 'users/' . $username . '/blocks/users';
        $options = ['usernames' => $blacklist];
        $header = [$this->getToken()];
        $result = $this->curl($url,$options,$header,'POST');
        return $result;
    }

    /**
     * 在黑名单中删除人员，只可以单独删除
     * @param $username
     * @param $blocked_username
     * @return mixed
     * @throws \Exception
     */
    public function deleteUserFromBlacklist($username,$blocked_username)
    {
        $url = $this->url . 'users/' . $username . '/blocks/users/' .$blocked_username;
        $header = [$this->getToken()];
        $result = $this->curl($url,[],$header,'DELETE');
        return $result;
    }
    //-----------------------用户相关方法-------------------------------------//

    //-----------------------群聊相关方法-------------------------------------//
    /**
     * 创建群
     * @param $options
     * @return mixed
     * @throws \Exception
     */
    public function createGroup($options)
    {
        $url = $this->url . 'chatgroups';
        $header = [$this->getToken()];
        $options['public'] = false;
        $options['approval'] = true;
        $result = $this->curl($url,$options,$header,'POST');
        return $result;
    }

    /**
     * 删除群
     * @param $group_id
     * @return mixed
     * @throws \Exception
     */
    public function deleteGroup($group_id)
    {
        $url = $this->url . 'chatgroups/' . $group_id;
        $header = [$this->getToken()];
        $result = $this->curl($url,[],$header,'DELETE');
        return $result;
    }

    public function addGroupMember($group_id,$username)
    {
        $url = $this->url . 'chatgroups/' . $group_id . '/users';
        $options = ['usernames' => $username];
        $header = [$this->getToken(),'Content-Type:application/json'];
        $result = $this->curl($url,$options,$header,'POST');
        return $result;
    }

    public function deleteGroupMember($group_id,$usernames)
    {
        $url = $this->url .'chatgroups/' . $group_id .'/users/' .$usernames;
        $header = [$this->getToken()];
        $result = $this->curl($url,[],$header,'DELETE');
        return $result;
    }

    public function changeGroupOwner($group_id,$newowner)
    {
        $url = $this->url . 'chatgroups/' . $group_id;
        $options = ['newowner' => (string)$newowner];
        $header = [$this->getToken(),'Content-Type:application/json'];
        $result = $this->curl($url,$options,$header,'PUT');
        return $result;
    }

    public function modifyGroupInfo($group_id,$options)
    {
        $url = $this->url . 'chatgroups/' .$group_id;
        $header = [$this->getToken()];
        $result = $this->curl($url,$options,$header,'PUT');
        return $result;
    }
    //-----------------------群聊相关方法-------------------------------------//
    /**
     * 用于穿透消息，例A申请加入群组，服务器向B发送请求验证
     * @param $target_type
     * @param $target
     * @param $action
     * @param $ext
     * @param string $from
     * @return mixed
     * @throws \Exception
     */
    public function sendCmd($target_type,$target,$action,$ext,$from = 'admin')
    {
        $url = $this->url . 'messages';
        $options = [
            'target_type' => $target_type,
            'target'      => $target,
            'msg'         => [
                'type'   => 'cmd',
                'action' => $action
            ],
            'from'        => $from,
            'ext'         => $ext
        ];
        $header = [$this->getToken()];
        $result = $this->curl($url,$options,$header,'POST');
        return $result;
    }

    /**
     * 请求方法封装
     * @param $url
     * @param $options
     * @param $header
     * @param $type
     * @return mixed
     * @throws \Exception
     */
    private function curl($url, $options, $header, $type)
    {
        $body = json_encode($options);
        //1.创建一个curl资源
        $ch = curl_init();
        //2.设置URL和相应的选项
        curl_setopt($ch,CURLOPT_URL,$url);//设置url
        //1)设置请求头
        //array_push($header, 'Accept:application/json');
        //array_push($header,'Content-Type:application/json');
        //array_push($header, 'http:multipart/form-data');
        //设置为false,只会获得响应的正文(true的话会连响应头一并获取到)
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt ( $ch, CURLOPT_TIMEOUT,5); // 设置超时限制防止死循环
        //设置发起连接前的等待时间，如果设置为0，则无限等待。
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
        //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //2)设备请求体
        if (count($body)>0) {
            //$b=json_encode($body,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);//全部数据使用HTTP协议中的"POST"操作来发送。
        }
        //设置请求头
        if(count($header)>0){
            curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        }
        //上传文件相关设置
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);// 从证书中检查SSL加密算

        //3)设置提交方式
        switch($type){
            case "GET":
                curl_setopt($ch,CURLOPT_HTTPGET,true);
                break;
            case "POST":
                curl_setopt($ch,CURLOPT_POST,true);
                break;
            case "PUT"://使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请									                     求。这对于执行"DELETE" 或者其他更隐蔽的HTT
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"PUT");
                break;
            case "DELETE":
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"DELETE");
                break;
        }


        //4)在HTTP请求中包含一个"User-Agent: "头的字符串。-----必设

        curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

        curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' ); // 模拟用户使用的浏览器
        //5)


        //3.抓取URL并把它传递给浏览器
        $res=curl_exec($ch);

        $result=json_decode($res,true);

        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        //4.关闭curl资源，并且释放系统资源
        curl_close($ch);

        if($code < 400){
            return $result;
        } else {
            throw new \Exception($result['error'],$code);
        }

    }
}