<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/19
 * Time: 17:42
 */

namespace App\Services;

use App\Models\Cloud\QiniuUrl;
use Qiniu\Auth;
use function Qiniu\base64_urlSafeDecode;
use function Qiniu\base64_urlSafeEncode;
use Qiniu\Processing\PersistentFop;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

/**
 * Class CloudStorage
 * 简单封装七牛SDK
 * @package App\Services
 */
class CloudStorage
{
    /**
     *  七牛 AK秘钥
     */
    const ACCESSKEY = 'NVsOLitxzARKF2ZFcTdmqfPc82I3dRQYN3-CYqJY';

    /**
     *  七牛 SK秘钥
     */
    const SECRETKEY = 'i_Jq4G9ijLS-YWsVJI3Gdfydn372pzgLZTHJ5ZTm';

    /**
     *  存放于七牛空间的名称
     */

    private $bucket;
    /**
     *  默认域名
     */
    private $baseurl;

    private $pipline;

    /**
     * 七牛认证类
     * @var Auth
     */
    private $auth;

    /**
     * 七牛Bucket管理类
     * @var BucketManager
     */
    private $bucketManager;

    /**
     * 初始化常用类
     * auth类都会用到
     * bucketManager 删除用到
     * QiniuStorage constructor.
     */
    public function __construct()
    {
        $this->auth = new Auth(self::ACCESSKEY,self::SECRETKEY);
        $this->bucketManager = new BucketManager($this->auth);
    }

    /**
     * 回调判断，是否是七牛发出的请求
     * @param $url
     * @return bool
     */
    public function verityCallback($url)
    {

        //获取回调的body信息
        $callbackBody = file_get_contents('php://input');

        //回调的contentType
        $contentType = 'application/x-www-form-urlencoded';

        //回调的签名信息，可以验证该回调是否来自七牛
        $authorization = $_SERVER['HTTP_AUTHORIZATION'];

        $isQiniuCallback = $this->auth->verifyCallback($contentType,$authorization,$url,$callbackBody);

        return $isQiniuCallback ? true : false;
    }

    public function getDomain()
    {

        return $this->baseurl . '/';
    }
    /**
     * 生成upload Token
     * @param null $policy
     * @return string
     */

    public function getToken($policy = null,$type,$location)
    {
        $this->bucket = QiniuUrl::where('type','=',$type)->where('location','=',$location)->first()->zone_name;
        return $this->auth->uploadToken($this->bucket,null,3600,$policy);
    }

    /**
     * 查询文件信息
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function status($key)
    {
        list($ret, $error) = $this->bucketManager->stat($this->bucket, $key);
        if ($error !== null) {
            throw new \Exception($error->message(),$error->code());
        } else {
            return $ret;
        }
    }

    
    /**
     * 删除单个文件
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        if (is_array($key)) {
            return '';
        }
        return  $this->bucketManager->delete($this->bucket,$key);
    }

    public function webDeleteVideo($key)
    {

        return  $this->bucketManager->buildBatchDelete('etcs-video',$key);
    }


    /**
     * 上传
     * @param $key
     * @param $filePath
     * @return array
     * @throws \Exception
     */
    public function put($key, $data)
    {
        $token = $this->auth->uploadToken($this->bucket);
        $uploadManager = new UploadManager();
        return $uploadManager->put($token,$key,$data);
    }

    /**
     * 上传单个文件
     * @param $key
     * @param $filePath
     * @return array
     * @throws \Exception
     */
    public function putFile($key, $filePath)
    {
        $token = $this->auth->uploadToken($this->bucket);
        $uploadManager = new UploadManager();
        return $uploadManager->putFile($token,$key,$filePath);
    }

    public function download($key)
    {
        $http = \Config::get('constants.HTTP');

        if(empty($key)){
            return $key;
        }

        $url = $http.$key;
        return $url;
    }

    public function downloadUrl($key)
    {
        $http = \Config::get('constants.HTTP');

        if(empty($key)){
            return $key;
        }

        $arr =[];
        if(is_array($key)){
            foreach ($key as $v){
                $arr[] = $http.$v;
            }

            return $arr;
        }

        $url = $http.$key;
        return $url;
    }

    /**
     * 生成私有空间的URL
     * @param $key
     * @param int $expires
     * @return string
     */
    public function privateDownloadUrl($key,$expires = 3600)
    {
        $url =$this->baseurl .'/'. $key;
        return $this->auth->privateDownloadUrl($url,$expires);
    }

    /**
     * 删除文件夹及子目录内容
     * @param $prefix
     * @throws \Exception
     * @return true
     */
    public function deleteDirectory($prefix)
    {
        list($items, $marker, $error) = $this->bucketManager->listFiles($this->bucket,$prefix);
        if($error !== null){
            throw new \Exception($error->message(),$error->code());
        }
        return $this->buildBatchDelete(array_column($items,'key'));
    }

    public function rename($from_key,$to_key)
    {
        list($ret, $error) = $this->bucketManager->move($this->bucket,$from_key,$this->bucket,$to_key);
        if ($error !== null) {
            throw new \Exception($error->message(),$error->code());
        } else {
            return true;
        }
    }

    /*public function test($key,$filePath)
    {
        $policy = [
            'callbackUrl' => 'http://101.200.75.163/api/users/{id}/avatar?token=token',
            'callbackBody' => 'key=$(key)&hash=$(etag)'
        ];
        $token = $this->getToken($policy);
        $uploadManager = new UploadManager();
        return $uploadManager->putFile($token,$key,$filePath,['x:token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjE2MDE3OSwiaXNzIjoiaHR0cDpcL1wvd3d3Lmdvb2JpcmQuY29tXC9hcGlcL3VzZXJzXC9hdXRoZW50aWNhdGUiLCJpYXQiOjE0NjE3Mzk5ODgsImV4cCI6MTQ2MTc0MzU4OCwibmJmIjoxNDYxNzM5OTg4LCJqdGkiOiIzMjk5MDg2MTAyNzVmNTlkYTc0ODFlNGRjMmZmYTI3OCJ9.7r_wmzlH_rg2yJmvUQgS98k4ltmqbZ6FKMaQovrUwP0']);
    }*/

    public function batchStat($keys)
    {
        $ops = BucketManager::buildBatchStat($this->bucket,$keys);
        list($ret, $error) = $this->bucketManager->batch($ops);
        if($error !== null){
            throw new \Exception($error->message(),$error->code());
        }
        return $ret;
    }

    public function batchRename($key_pairs)
    {
        if (empty($key_pairs)) {
            return true;
        }
        $ops = BucketManager::buildBatchRename($this->bucket,$key_pairs);
        list($ret, $error) = $this->bucketManager->batch($ops);
        if($error !== null){
            throw new \Exception($error->message(),$error->code());
        }
        return $ret;
    }

    public function buildBatchDelete($keys)
    {
        if (empty($keys)) {
            return true;
        }
        $ops = BucketManager::buildBatchDelete($this->bucket,$keys);
        list($ret, $error) = $this->bucketManager->batch($ops);
        if($error !== null){
            throw new \Exception($error->message(),$error->code());
        }
        return $ret;
    }

    public function persistentFop($key,$fops,$notifyUrl)
    {
        $pfop = new PersistentFop($this->auth,$this->bucket,$this->pipline,$notifyUrl);
        list($id,$error) = $pfop->execute($key,$fops);
        if($error !== null) {
            throw new \Exception($error->message(),$error->code());
        }
        return $id;
    }

    public function copyfile($key,$srcbucket,$destbucket)
    {
        $ops = BucketManager::buildBatchCopy($srcbucket,$key,$destbucket,true);
        list($ret,$error) = $this->bucketManager->batch($ops);
        if($error !== null){
            throw new \Exception($error->message(),$error->code());
        }
        return $ret;
    }

    /**
     * @param $a
     * @return string
     */
    public function privateUrl($a)
    {
        $http = \Config::get('constants.HTTP');
        $ak = \Config::get('constants.AK');
        $sk = \Config::get('constants.SK');
        $qi =  new \Qiniu\Auth($ak , $sk);

        $url = $http.$a;

        $urls = $qi->privateDownloadUrl($url);

        return $url;
    }

    /**
     * @param $a
     * @return string
     */
    public function privateUrl_zip($a)
    {
        $http = \Config::get('constants.HTTP');
        $ak = \Config::get('constants.AK');
        $sk = \Config::get('constants.SK');
        $qi =  new \Qiniu\Auth($ak , $sk);

        $url = $http.$a;

        $urls = $qi->privateDownloadUrl($url);

        return $urls;
    }

    /**
     * @param $a
     * @return string
     */
    public function publicImage($a)
    {
        $http = \Config::get('constants.HTTP');

        $url = $http.$a;

        return $url;
    }

    /**
     * @param $image
     * @return string
     */
    public function ImageCheck($image)
    {
        $http = \Config::get('constants.HTTP');

        $url = $http.$image.'?qpulp';

        return $url;
    }

    /**
     * @param $image
     * @return string
     */
    public function qpolitician($image)
    {
        $http = \Config::get('constants.HTTP');

        $url = $http.$image.'?qpolitician';

        return $url;
    }

    public function tupuvideo($video)
    {
        $http = \Config::get('constants.HTTP');

        $url = $http.$video.'?qpolitician';

        return $url;
    }


    public function signAgain($data)
    {
        $http = \Config::get('constants.HTTP');
        $ak = \Config::get('constants.AK');
        $sk = \Config::get('constants.SK');
        $qi =  new \Qiniu\Auth($ak , $sk);

        return $qi->sign($data);
    }

    public function DRM($bucket,$key)
    {
        $config = new \Qiniu\Config();
        $HLSkey = base64_urlSafeEncode('fk9215jslat359cx');
        $key = base64_urlSafeEncode($key);
        $str = rtrim($key,'.m3u8');
        $fop = 'avthumb/m3u8/noDomain/1/vcodec/copy/acodec/copy/hlsKey/';
        $fop .= $HLSkey;
        $fop .= '/hlsKeyUrl/';
        $fop .= 'aaa';
        $fop .= '/hlsMethod/qiniu-protection-10|saveas/';
        $fop .= base64_urlSafeEncode("$bucket:$str");
        $fops = base64_urlSafeEncode($fop);
        $pfop = new PersistentFop($this->auth,$config);
        $pipeline = 'hivideo_drm';
        list($id, $err) = $pfop->execute($bucket, $key, $fops, $pipeline,$force=1);
        if ($err != null) {
            return false;
        } else {

                return true;

        }

    }


    public function transcoding($bucket,$key,$width,$hight)
    {
        $config = new \Qiniu\Config();
        $pfop = new PersistentFop($this->auth,$config);
        $fops ='adapt/m3u8/multiResolution/';
        $fops .= floor($width/4).':'.floor($hight/4);
        $fops .= floor($width/3).':'.floor($hight/3);
        $fops .= floor($width/2).':'.floor($hight/2);
        $fops .= $width.':'.$hight;
        $fops .= '/envBandWidth/200000,800000,1700000,2400000/multiVb/200k,1200k,6500k,8500k/hlstime/10|saveas/';
        $fop .= base64_urlSafeEncode("$bucket:$key");
        $piepeline = 'hivideo_transcode';
        list($id, $err) = $pfop->execute($bucket, $key, $fops, $pipeline,$force=1);
        if ($err != null) {
            return false;
        } else {

                return true;

        }
    }

    public function Mark($file,$mark_url,$id)
    {
        $accessKey = 'NVsOLitxzARKF2ZFcTdmqfPc82I3dRQYN3-CYqJY';
        $secretKey = 'i_Jq4G9ijLS-YWsVJI3Gdfydn372pzgLZTHJ5ZTm';
        $auth = new Auth($accessKey, $secretKey);
        $bucket = 'hivideo-video';
        $url = $this->downloadUrl($file);
        $file_url = ltrim(parse_url($url)['path'],'/') ;
        $pipeline = 'hivideo_drm';
        $notifyUrl = 'http://www.goobird.com/api/notification';
        $pfop = new PersistentFop($auth, $bucket, $pipeline,$notifyUrl);
//        $mark_uri= $this->downloadUrl($mark_url);
        $waterImg = base64_urlSafeEncode('http://101.200.75.163/home/img/logo.png');
        $save = base64_urlSafeEncode($bucket.":__".$id.'___'.$file_url);
        $fops = "avthumb/mp4/s/640x360/vb/1.4m/wmImage/".$waterImg."/wmGravity/NorthWest/wmOffsetX/10/wmOffsetY/10/wmConstant/0|saveas/".$save;

        list($id, $err) = $pfop->execute($file_url, $fops);
        echo "\n====> pfop avthumb result: \n";
        if ($err != null) {
            var_dump($err);
        } else {
            echo "PersistentFop Id: $id\n";
        }


//        $accessKey = 'u0TMXtQ2hI2QXAIwIsI9Qtwq8XDI4NX4lQSK5TJd';
//        $secretKey = 'e0glri6LdL341PcItVSxsrKHttFoA--wujcQj_dC';
//        $auth = new Auth($accessKey, $secretKey);
//        $bucket = 'image';
////        $url = $this->downloadUrl($file);
////        $file_url = ltrim(parse_url($url)['path'],'/') ;
//        $pipeline = 'chijiu';
//        $notifyUrl = 'http://www.goobird.com/api/notification';
//        $pfop = new PersistentFop($auth, $bucket, $pipeline,$notifyUrl);
////        $mark_uri= $this->downloadUrl($mark_url);
//        $waterImg = base64_urlSafeEncode('http://101.200.75.163/home/img/logo.png');
//        $save = base64_urlSafeEncode($bucket.":_".$id.'__test.mp4');
//        $fops = "avthumb/mp4/s/640x360/vb/1.4m/wmImage/".$waterImg."/wmGravity/NorthWest/wmOffsetX/10/wmOffsetY/10/wmConstant/0|saveas/".$save;
//
//        list($id, $err) = $pfop->execute('test.mp4', $fops);
//        echo "\n====> pfop avthumb result: \n";
//        if ($err != null) {
//            var_dump($err);
//        } else {
//            echo "PersistentFop Id: $id\n";
//        }

    }

}