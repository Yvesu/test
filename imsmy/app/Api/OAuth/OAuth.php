<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/6/3
 * Time: 11:34
 */

namespace App\Api\OAuth;


abstract class OAuth
{
    abstract public function verify($open_id, $access_token);

    /**
     * 请求方法封装
     * @param $url
     * @param $options
     * @param $header
     * @param $type
     * @return mixed
     * @throws \Exception
     */
    public function curl($url, $options, $header, $type)
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
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options));//全部数据使用HTTP协议中的"POST"操作来发送。
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



        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        //4.关闭curl资源，并且释放系统资源
        curl_close($ch);

        return $res;
    }
}