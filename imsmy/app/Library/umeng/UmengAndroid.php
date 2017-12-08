<?php
namespace App\Library\umeng;

/**
 * Class UmengAndroid
 */
class UmengAndroid
{
    private $appkey = '59014cdcaed17957320009ab';

    private $secret = 'rnkv9ymgpcotxzx8qy3mb1pumjbifuou';

    //必填 通知栏提示文字
    private $ticker ;

    //必填 通知标题
    private $title;

    //必填 通知文字描述
    private $text;

    //别名类型
    private $alias_type;

    //别名
    private $alias;

    public function __construct($ticker,$title,$text,$alias_type,$alias)
    {
        $this ->ticker       = $ticker;
        $this->title         = $title;
        $this->text          = $text;
        $this->alias_type    = $alias_type;
        $this->alias         = $alias;
    }

    public function Send()
   {  
      $body = array(  
         'ticker'=> $this -> ticker,
         'title' => $this -> title,
         'text'  => $this -> text,
         'after_open'=>'go_app',
      );  
  
      $payload = array(  
         'display_type'=>'notification',
         'body'=>$body  
      );  

      $para = array(  
         'appkey'=> $this->appkey,
         'timestamp'=>date('U'),  
         'type'=>'customizedcast',
      	 'alias_type'=>$this->alias_type,
      	 'alias'=>$this->alias,
         'payload'=>$payload  
      );  
      $paraJson = json_encode($para);  
      $original_str = 'POST'.'http://msg.umeng.com/api/send'.$paraJson.$this->secret;
      $md5Str = strtolower(md5($original_str));  
      $url = 'http://msg.umeng.com/api/send'.'?sign='.$md5Str;  
      $result = $this->post($url,$paraJson);  
  
      echo $result;  
   }  

   function post($url,$data) {  
      $ch = curl_init($url);  
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));  
      $data = curl_exec($ch);  
      return $data;  
   }  
}

