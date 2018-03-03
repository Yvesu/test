<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/19
 * Time: 17:42
 */

namespace App\Services;

use App\Models\Cloud\QiniuUrl;
use App\Models\FilmfestsProductions;
use App\Models\JoinVideo;
use App\Models\Tweet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function deleteNew($bucket,$key)
    {
        return  $this->bucketManager->delete($bucket,$key);
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

    public function oneBucketCopyFile($key,$srcbucket,$destbucket,$desctKey)
    {
        $ops = BucketManager::copy($srcbucket,$key,$destbucket,$desctKey,true);
    }

    public function copyFile2($key,$srcbucket,$destbucket,$destkey)
    {
        $ops = BucketManager::copy($srcbucket,$key,$destbucket,$destkey,true);
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
     * @param $bucket
     * @param $key
     * @param $width
     * @param $height
     * @param $choice
     * @return bool
     */
    public function transcoding_tweet($tid,$bucket,$key,$width,$height,$choice,$notice)
    {
        $pipeline = config('constants.pipeline_transcoding');
        $pfop = new PersistentFop($this->auth,$bucket,$pipeline,$notice);
        $ex = pathinfo($key, PATHINFO_EXTENSION);
        $fileKey = str_replace($ex,'m3u8',$key);
        $fileKey = 'adapt/&'.$tid.'&&'.$fileKey;
        $fops ='adapt/m3u8/multiResolution/';
        $fops .= (int)($width/3).':'.(int)($height/3).',';
        $fops .= (int)($width/2).':'.(int)($height/2).',';
        $fops .= $width.':'.$height.'/';
        $fops .= 'envBandWidth/200000,500000,2400000/multiVb/200k,500k,8500k/hlstime/10|saveas/';
        $fops .= base64_urlSafeEncode("$bucket:$fileKey");
        list($id, $err) = $pfop->execute($key, $fops);
        $ex = pathinfo($key, PATHINFO_EXTENSION);
        $key3 = str_replace($ex,'m3u8',$key);
        $fileKey3 = 'original/&'.$tid.'&&'.$key3;
        $fops3 = 'avthumb/m3u8/noDomain/1/segtime/5|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey3);
        list($id, $err2) = $pfop->execute($key,$fops3);
        if($choice == 1){
            $key1 = str_replace($ex,'m3u8',$key);
            $fileKey1 = 'high/&'.$tid.'&&'.$key1;
            $fileKey2 = 'norm/&'.$tid.'&&'.$key1;
            $fops1 = 'avthumb/m3u8/noDomain/1/segtime/5/s/'.(int)($width/2).'x'.(int)($height/2).'|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey1);
            $fops2 = 'avthumb/m3u8/noDomain/1/segtime/5/s/'.(int)($width/3).'x'.(int)($height/3).'|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey2);
            list($id, $err1) = $pfop->execute($key,$fops2);
            list($id, $err2) = $pfop->execute($key,$fops1);
            if($err != null || $err1 != null || $err2 != null){
                return false;
            }else{
                return true;
            }
        }else{
            if ($err != null) {
                return false;
            } else {
                return $id;
            }
        }

    }

    public function join_transcoding($bucket,$key,$width,$height,$choice,$notice)
    {
        $pipeline = 'hivideo_alternative';
        $pfop = new PersistentFop($this->auth,$bucket,$pipeline,$notice);
        $ex = pathinfo($key, PATHINFO_EXTENSION);
        $fileKey = str_replace($ex,'m3u8',$key);
        $fileKey = 'adapt/'.$fileKey;
        $fops ='adapt/m3u8/multiResolution/';
        $fops .= (int)($width/3).':'.(int)($height/3).',';
        $fops .= (int)($width/2).':'.(int)($height/2).',';
        $fops .= $width.':'.$height.'/';
        $fops .= 'envBandWidth/200000,500000,2400000/multiVb/200k,500k,8500k/hlstime/10|saveas/';
        $fops .= base64_urlSafeEncode("$bucket:$fileKey");
        list($id, $err) = $pfop->execute($key, $fops);
        $ex = pathinfo($key, PATHINFO_EXTENSION);
        $key3 = str_replace($ex,'m3u8',$key);
        $fileKey3 = 'original/'.$key3;
        $fops3 = 'avthumb/m3u8/noDomain/1/segtime/5|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey3);
        list($id, $err2) = $pfop->execute($key,$fops3);
        if($choice == 1){
            $key1 = str_replace($ex,'m3u8',$key);
            $fileKey1 = 'high/'.$key1;
            $fileKey2 = 'norm/'.$key1;
            $fops1 = 'avthumb/m3u8/noDomain/1/segtime/5/s/'.(int)($width/2).'x'.(int)($height/2).'|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey1);
            $fops2 = 'avthumb/m3u8/noDomain/1/segtime/5/s/'.(int)($width/3).'x'.(int)($height/3).'|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey2);
            list($id, $err1) = $pfop->execute($key,$fops2);
            list($id, $err2) = $pfop->execute($key,$fops1);
            if($err != null || $err1 != null || $err2 != null){
                return false;
            }else{
                return true;
            }
        }else{
            if ($err != null) {
                return false;
            } else {
                return $id;
            }
        }

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

    /**
     * @param $key
     * @return string
     * 获得视频分辨率
     */
    public function getWidthAndHeight($key)
    {

        $url = "http://video.ects.cdn.hivideo.com/".$key.'?avinfo';
        $ex = pathinfo($key, PATHINFO_EXTENSION);
        $html = file_get_contents($url);
        $rule1 = "/\"width\":.*?,/";
        $rule2 = "/\"height\":.*?,/";
        preg_match($rule1,$html,$width);
        preg_match($rule2,$html,$height);
        $width =rtrim( explode(' ',$width[0])[1],',');
        $height = rtrim(explode(' ',$height[0])[1],',');
        return $width.'*'.$height;
    }

    /**
     * @param $srcbucket
     * @param $key
     * @param $destbucket
     * @param $destKey
     * @return bool
     * @throws \Exception
     * 重命名
     */
    public function reNameFile($srcbucket,$key,$destbucket,$destKey)
    {
        list($ret, $error) = $this->bucketManager->move($srcbucket,$key,$destbucket,$destKey,true);
        if ($error !== null) {
            throw new \Exception($error->message(),$error->code());
        } else {
            return true;
        }
    }

    /**
     * @param $key
     * @param $newAddress
     * @param $width
     * @param $height
     * @return bool|string
     * 截图
     */
    public function saveCover($key,$newAddress,$width,$height)
    {
//        $bucket = 'hivideo-video';
        $bucket = 'test-video';
//        $fileBucket = 'hivideo-img';
        $fileBucket = 'test-img';
        $pipeline = 'goobird-dev';
        $pfop = new PersistentFop($this->auth,$bucket,$pipeline);
        $pipeline = 'hivideo_drm';
        $fileKey1 = $key.'vframe-001_'.$width.'*'.$height.'_.jpg';
        $fops = 'vframe/jpg/offset/7/w/'.$width.'/h/'.$height.'|saveas/';
        $fops .= base64_urlSafeEncode("$fileBucket:$fileKey1");
        list($id,$err) = $pfop->execute($newAddress,$fops,$pipeline,$force=true);
        if ($err != null) {
            return false;
        } else {
            return $width.'*'.$height;
        }
    }

    /**
     * @param $key
     * @param int $time
     * @return bool|string
     * 修改图片时用户自定义截图
     */
    public function userSaveCover($key,$time=0)
    {
        $bucket = 'hivideo-video';
        $fileBucket = 'hivideo-img';
        $config = new \Qiniu\Config();
        $url = "http://video.ects.cdn.hivideo.com/".$key.'?avinfo';
        $html = file_get_contents($url);
        $rule1 = "/\"width\":.*?,/";
        $rule2 = "/\"height\":.*?,/";
        preg_match($rule1,$html,$width);
        preg_match($rule2,$html,$height);
        $width =rtrim( explode(' ',$width[0])[1],',');
        $height = rtrim(explode(' ',$height[0])[1],',');
        $pfop = new PersistentFop($this->auth,$bucket);
        $pipeline = 'hivideo_drm';
        $this->bucketManager->buildBatchDelete('hivideo-img',$key.'vframe-user-save_'.$width.'*'.$height.'_.jpg');
        $fileKey1 = $key.'vframe-user-save_'.$width.'*'.$height.'_.jpg';
        $fops = 'vframe/jpg/offset/'.$time.'/w/'.$width.'/h/'.$height.'|saveas/';
        $fops .= base64_urlSafeEncode("$fileBucket:$fileKey1");
        list($id,$err) = $pfop->execute($key,$fops,$pipeline,$force=true);

        if ($err != null || $err2 != null || $err3 != null) {
            return false;
        } else {
            return $fileKey1;
        }
    }

    //  暂时无用
    public function DRM($bucket,$key)
    {
        $kk = \Qiniu\base64_urlSafeEncode('www.goobird.com');
        $hlsKey = base64_urlSafeEncode('1234567890123456');
        $fops = "avthumb/m3u8/noDomain/1/vcodec/copy/acodec/copy/hlsKey/$hlsKey/hlsKeyUrl/$kk/hlsMethod/qiniu-protection-10|saveas/".\Qiniu\base64_urlSafeEncode($bucket.":".$key."m3u8");
        $pipeline = 'hivideo_drm';
        $pfop = new PersistentFop($this->auth,$bucket,$pipeline);
        list($id, $err) = $pfop->execute($key,$fops);
        if ($err != null) {
            return false;
        } else {
            return true;
        }
    }

    //  暂时无用
    public function DRM3($bucket,$id)
    {
        \DB::beginTransaction();
        $production = Tweet::find($id);
        if($production->is_transcod == 1){
            $accessKey = 'NVsOLitxzARKF2ZFcTdmqfPc82I3dRQYN3-CYqJY';
            $secretKey = 'i_Jq4G9ijLS-YWsVJI3Gdfydn372pzgLZTHJ5ZTm';
            $auth = new Auth($accessKey, $secretKey);
            $bucket1 = 'hivideo-video';
            $pfop1 = new PersistentFop($auth,$bucket1);
            list($ret, $err1) = $pfop1->status($production->transcoding_id);
            if($ret['code']==0 && $ret['desc']=="The fop was completed successfully" && isset($ret['items'][0]['key'])){
                $key = $production->transcoding_video;
                $key = str_replace('v.cdn.hivideo.com/','',$key);
                $kk = \Qiniu\base64_urlSafeEncode('www.goobird.com');
                $hlsKey = base64_urlSafeEncode('1234567890123456');
                $fops = "avthumb/m3u8/noDomain/1/vcodec/copy/acodec/copy/hlsKey/$hlsKey/hlsKeyUrl/$kk/hlsMethod/qiniu-protection-10|saveas/".\Qiniu\base64_urlSafeEncode($bucket.":drm/".$key);
                $pipeline = 'hivideo_drm';
                $pfop = new PersistentFop($this->auth,$bucket,$pipeline);
                list($id, $err) = $pfop->execute($key,$fops);
                if ($err != null) {
                    $production -> active = 8;
                    $production -> error_reason = '加密失败';
                    DB::commit();
                    return false;
                } else {
                    $production -> time_up_over = time();
                    $production -> active = 0;
                    $production -> is_drm = 1;
                    $production -> drm_address = 'v.cdn.hivideo.com'.$key;
                    $production -> save();
                    DB::commit();
                    return true;
                }
            }else{
                return false;
            }
        }else{
            $key = $production->video;
            $key = str_replace('v.cdn.hivideo.com/','',$key);
            $HLSkey = base64_urlSafeEncode('456daf8742acc485');
            $kk = \Qiniu\base64_urlSafeEncode('www.goobird.com');
            $hlsKey = base64_urlSafeEncode('1234567890123456');
            $fops = "avthumb/m3u8/noDomain/1/vcodec/copy/acodec/copy/hlsKey/$hlsKey/hlsKeyUrl/$kk/hlsMethod/qiniu-protection-10|saveas/".\Qiniu\base64_urlSafeEncode($bucket.":drm/".$key);
            $pipeline = 'hivideo_drm';
            $pfop = new PersistentFop($this->auth,$bucket,$pipeline);
            list($id, $err) = $pfop->execute($key,$fops);
            if ($err != null) {
                $production -> active = 8;
                $production -> error_reason = '加密失败';
                DB::commit();
                return false;
            } else {
                $production -> time_up_over = time();
                $production -> active = 0;
                $production -> is_drm = 1;
                $production -> drm_address = 'v.cdn.hivideo.com'.$key;
                $production -> save();
                DB::commit();
                return true;
            }
        }
    }

    //  暂时无用
    public function DRM2()
    {

        $bucket = 'hivideo-video-ects';
        $key ='3zh5Cm7179.mp4';
        $kk = \Qiniu\base64_urlSafeEncode('www.qiniu.com');
        $hlsKey = base64_urlSafeEncode('456daf8742acc485');
        $fops = "avthumb/m3u8/noDomain/1/vcodec/copy/acodec/copy/hlsKey/$hlsKey/hlsKeyUrl/$kk/hlsMethod/qiniu-protection-10|saveas/".\Qiniu\base64_urlSafeEncode($bucket.":hls.m3u8");
        $pipeline = 'hivideo_drm';
        $pfop = new PersistentFop($this->auth,$bucket,$pipeline);
        list($id, $err) = $pfop->execute($key,$fops);
        if ($err != null) {
            return false;
        } else {
            return true;
        }

    }

    // 暂时无用
    public function transcoding1($bucket,$key,$width,$height)
    {
        $pipeline = 'hivideo_alternative';
        $pfop = new PersistentFop($this->auth,$bucket,$pipeline);
        $fileKey = $key.'.m3u8';
        $hlsKey = base64_urlSafeEncode('1234567890123456');
        $kk = \Qiniu\base64_urlSafeEncode('www.qiniu.com');
        $fops ='adapt/m3u8/multiResolution/';
        $fops .= (int)($width/3).':'.(int)($height/3).',';
        $fops .= (int)($width/2).':'.(int)($height/2).',';
        $fops .= $width.':'.$height.'/';
        $fops .= 'envBandWidth/200000,500000,2400000/multiVb/200k,500k,8500k/hlstime/10/noDomain/1/vcodec/copy/acodec/copy/hlsKey/'.$hlsKey.'/hlsKeyUrl/'.$kk.'/hlsMethod/qiniu-protection-10|saveas/'.\Qiniu\base64_urlSafeEncode($bucket.':'.$fileKey);

        list($id, $err) = $pfop->execute($key, $fops);
        if ($err != null) {
            return false;
        } else {
            return $id;
        }
    }

    /**
     * @param $bucket
     * @param $key
     * @param $width
     * @param $height
     * @param $choice
     * @return bool
     * 转码
     */
    public function transcoding($bucket,$key,$width,$height,$choice)
    {
        $pipeline = 'hivideo_alternative';
        $pfop = new PersistentFop($this->auth,$bucket,$pipeline);
        $ex = pathinfo($key, PATHINFO_EXTENSION);
//        $fileKey = $key.'.m3u8';
        $fileKey = str_replace('.'.$ex,'_'.$ex.'.m3u8',$key);
        $fops ='adapt/m3u8/multiResolution/';
        $fops .= (int)($width/3).':'.(int)($height/3).',';
        $fops .= (int)($width/2).':'.(int)($height/2).',';
        $fops .= $width.':'.$height.'/';
        $fops .= 'envBandWidth/200000,500000,2400000/multiVb/200k,500k,8500k/hlstime/10|saveas/';
        $fops .= base64_urlSafeEncode("$bucket:$fileKey");
        list($id1, $err) = $pfop->execute($key, $fops);
        $key3 = str_replace($ex,'m3u8',$key);
        $fileKey3 = $key3;
        $fops3 = 'avthumb/m3u8/noDomain/1/segtime/5|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey3);
        list($id, $err2) = $pfop->execute($key,$fops3);
        if($choice == 1){
            $key1 = str_replace($ex,'m3u8',$key);
            $fileKey1 = 'high/'.$key1;
            $fileKey2 = 'norm/'.$key1;
            $fops1 = 'avthumb/m3u8/noDomain/1/segtime/5/s/'.(int)($width/2).'x'.(int)($height/2).'|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey1);
            $fops2 = 'avthumb/m3u8/noDomain/1/segtime/5/s/'.(int)($width/3).'x'.(int)($height/3).'|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey2);
            list($id, $err1) = $pfop->execute($key,$fops2);
            list($id, $err2) = $pfop->execute($key,$fops1);
            if($err != null || $err1 != null || $err2 != null){
                return false;
            }else{
                return $id1;
            }
        }else{
            if ($err != null) {
                return false;
            } else {
                return $id1;
            }
        }

    }

    public function videoMark($file,$imgUrl,$place)
    {
        $pipeline = 'hivideo_transcode';
        $accessKey = 'NVsOLitxzARKF2ZFcTdmqfPc82I3dRQYN3-CYqJY';
        $secretKey = 'i_Jq4G9ijLS-YWsVJI3Gdfydn372pzgLZTHJ5ZTm';
        $auth = new Auth($accessKey, $secretKey);
        $bucket = 'hivideo-video';
        $url = $this->downloadUrl($file);
        $file_url = ltrim(parse_url($url)['path'], '/');
        $pfop = new PersistentFop($auth, $bucket, $pipeline,null,true);
        $waterImg = base64_urlSafeEncode("$imgUrl");
        $save = base64_urlSafeEncode("$bucket".":"."$file_url");
        $fops = 'avthumb/mp4/wmImage/'.$waterImg.'/wmGravity/'.$place.'/wmConstant/0|saveas/'.$save;
        list($id, $err) = $pfop->execute($file_url, $fops);
    }

    public function imgWaterMark($bucket,$key,$markContent)
    {
        $pipeline = 'hivideo_transcode';
        $pfop = new PersistentFop($this->auth,$bucket,$pipeline,null,true);
        $fops = 'watermark/2/text/';
        $fops .= base64_urlSafeEncode($markContent);
        $fops .= '/font/';
        $fops .= base64_urlSafeEncode('微软雅黑');
        $fops .= '/fontsize/400/fill/';
        $fops .= base64_urlSafeEncode('#C0C0C0');
        $fops .= '/dissolve/100/gravity/SouthEast/dx/20/dy/20|saveas/';
        $fops .= base64_urlSafeEncode("$bucket:$key");
        list($id, $err) = $pfop->execute($key, $fops);
    }

    public function searchStatus($key)
    {
        $bucket = 'hivideo-video';
        $pfop = new PersistentFop($this->auth,$bucket);
        list($ret, $err1) = $pfop->status($key);
        if ($err1 != null) {
            return $err1;
        } else {
            return $ret;
        }
    }

     public function Mark($file,$mark_url,$id,$noti,$nickname)
     {
         $accessKey = config('constants.AK');
         $secretKey = config('constants.SK');
         $auth = new Auth($accessKey, $secretKey);
         $bucket = config('constants.video_bucket');
         $url = $this->downloadUrl($file);
         $file_url = ltrim(parse_url($url)['path'], '/');
         $pipeline = config('constants.pipeline_mark');
         $not = $noti;
         $pfop = new PersistentFop($auth, $bucket, $pipeline, $not);
//        $mark_uri= $this->downloadUrl($mark_url);
         $waterImg = base64_urlSafeEncode('http://39.106.106.73/home/img/logo.png');
         $save = base64_urlSafeEncode($bucket . ":&" . $id . '&&' . $file_url);
         $wxl = str_replace('*', 'x', getNeedBetween($file_url, '_', '.'));
         $text = base64_urlSafeEncode('@' . $nickname);
         $fond = base64_urlSafeEncode('微软雅黑');
         $font_color = base64_urlSafeEncode('#FFFFFF');
         $font_size = 20;
         $fops = "avthumb/mp4/s/" . $wxl . "/vb/1.4m/wmImage/" . $waterImg . "/wmText/" . $text . "/wmGravityText/NorthWest/wmFont/" . $fond . "/wmFontColor/" . $font_color . "/wmFontSize/" . $font_size . "/wmGravity/NorthWest/wmOffsetX/10/wmOffsetY/20/wmConstant/0|saveas/" . $save;
         list($id, $err) = $pfop->execute($file_url, $fops);
     }

     public function ccss($bucket,$key,$width,$height,$choice)
     {
         $pipeline = 'hivideo_alternative';
         $pfop = new PersistentFop($this->auth,$bucket,$pipeline);
         $ex = pathinfo($key, PATHINFO_EXTENSION);
         $key2 = str_replace($ex,'.m3u8',$key);
         $fileKey2 = 'high/'.$key2;
         $fops2 = 'avthumb/m3u8/noDomain/1/segtime/5/s/'.(int)($width/2).'x'.(int)($height/2).'|saveas/'.base64_urlSafeEncode($bucket.':'.$fileKey2);
         list($id, $err1) = $pfop->execute($key,$fops2);
             if ($err1 != null) {
                 return false;
             } else {
                 return true;
             }
     }

     public function deleteFile($bucket,$key)
     {
         $bucketMgr = new BucketManager($this->auth);

         $err = $bucketMgr->delete($bucket, $key);
//         echo "\n====> delete $key : \n";
         if ($err !== null) {
             Log::info(serialize($err));
         } else {
             return 'success';
         }
     }

    public function joint($id,$join_id,$notice = null,$status=1)
    {
       $tweet = Tweet::find($id);
       if ( !$tweet )    return response()->json(['message'=>'bad_request'],403);
       $url = $this->downloadUrl($tweet->video);
        $file_url = ltrim(parse_url($url)['path'], '/');
        switch ($status){
           case 1:
               $join_video = JoinVideo::find($join_id);
               $head_url = $this->downloadUrl(config('constants.video_bucket_url_test')).ltrim(parse_url($this -> downloadUrl($join_video->head_video))['path'], '/');
               $tail_url = $this->downloadUrl(config('constants.video_bucket_url_test')).ltrim(parse_url($this -> downloadUrl($join_video->tail_video))['path'], '/');
               break;
           case 2:
               $join_video = JoinVideo::find($join_id);
               $head_url = false;
               $tail_url = $this->downloadUrl(config('constants.video_bucket_url_test')).ltrim(parse_url($this -> downloadUrl($join_video->tail_video))['path'], '/');
               break;
           case 3:
               $join_video = JoinVideo::find($join_id);
               $head_url = $this->downloadUrl(config('constants.video_bucket_url_test')).ltrim(parse_url($this -> downloadUrl($join_video->head_video))['path'], '/');
               $tail_url = false;
               break;
           default:
               $join_video = JoinVideo::find($join_id);
               $head_url = $this->downloadUrl(config('constants.video_bucket_url_test')).ltrim(parse_url($this -> downloadUrl($join_video->head_video))['path'], '/');
               $tail_url = $this->downloadUrl(config('constants.video_bucket_url_test')).ltrim(parse_url($this -> downloadUrl($join_video->tail_video))['path'], '/');
               break;
       }
       $bucket = config('constants.video_bucket');
        $pipeline = config('constants.pipeline_transcoding');
        $pfop = new PersistentFop($this->auth, $bucket,$pipeline,$notice);
       $encodedUrl1 = base64_urlSafeEncode( $head_url );
       $encodedUrl2 = base64_urlSafeEncode( $tail_url );
       $save = base64_urlSafeEncode($bucket .":&" . $id . '&&' . $file_url);
       $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."/".$encodedUrl2."|saveas/".$save;
       if (!$tail_url){
           $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."|saveas/".$save;
       }elseif(!$head_url){
           $fops = "avconcat/2/format/mp4/index/1/".$encodedUrl2."|saveas/".$save;
       }
        list($id, $err) = $pfop->execute($file_url, $fops);
     }

    public function joint_tranding($id,$join_id,$notice = null,$status=1,$width,$height,$choice,$filmfest_id)
    {
        $tweet = Tweet::find($id);
        $production_id = $tweet->tweetProduction()->first()->id;

        if ( !$tweet )    return response()->json(['message'=>'bad_request'],403);

        $file_url = str_replace('v.cdn.hivideo.com/','',$tweet->video);

        $join_video = JoinVideo::find($join_id);
        $bucket = 'hivideo-video';
        $pipeline = 'hivideo_alternative';
        $pfop = new PersistentFop($this->auth, $bucket,$pipeline,$notice);
        set_time_limit(0);
        $save = base64_urlSafeEncode($bucket .":&" . $id . '&&' . $file_url);
        switch ($status) {
            case 1:
                $head = ltrim(parse_url($this->downloadUrl($join_video->head_video))['path'], '/');
                $tail = ltrim(parse_url($this->downloadUrl($join_video->tail_video))['path'], '/');
                $tempHead = 'tempvideo/' . $head;
                $tempTail = 'tempvideo/' . $tail;
                $fops1 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempHead);
                $fops2 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempTail);
                list($id1, $err2) = $pfop->execute($head, $fops1);
                list($id2, $err2) = $pfop->execute($tail, $fops2);
                $this->listenTranscoding($id1,$bucket);
                $this->listenTranscoding($id2,$bucket);
                $head_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempHead;
                $tail_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempTail;
                $encodedUrl1 = base64_urlSafeEncode($head_url);
                $encodedUrl2 = base64_urlSafeEncode($tail_url);
                $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."/".$encodedUrl2."|saveas/".$save;
                list($id, $err) = $pfop->execute($file_url, $fops);
                set_time_limit(0);
                do{
                    list($ret, $err1) = $pfop->status($id2);
                    if($ret['code']==3 && $ret['des']=='The fop is failed'){
                        $filmfestProduction = FilmfestsProductions::where('filmfests_id',$filmfest_id)
                            ->where('tweet_productions_id',$production_id)->first();
                        $filmfestProduction -> videoStatus = 0;
                        $filmfestProduction -> save();
                        return false;
                    }
                    if($ret['code']==0){
                        $filmfestProduction = FilmfestsProductions::where('filmfests_id',$filmfest_id)
                            ->where('tweet_productions_id',$production_id)->first();
                        $filmfestProduction -> videoStatus = 2;
                        $filmfestProduction -> save();
                    }
                }while($ret['code']==0);
                break;
            case 2:

                $tail = ltrim(parse_url($this->downloadUrl($join_video->tail_video))['path'], '/');
                $tempTail = 'tempvideo/' . $tail;
                $fops2 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempTail);
                list($id, $err2) = $pfop->execute($tail, $fops2);
                $message = $this->listenTranscoding($id,$bucket);
                $head_url = false;
                $tail_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempTail;
                $encodedUrl2 = base64_urlSafeEncode($tail_url);
                $fops = "avconcat/2/format/mp4/index/1/".$encodedUrl2."|saveas/".$save;
                list($id, $err) = $pfop->execute($file_url, $fops);
                set_time_limit(0);
                do{
                    list($ret, $err1) = $pfop->status($id2);
                    if($ret['code']==3 && $ret['des']=='The fop is failed'){
                        $filmfestProduction = FilmfestsProductions::where('filmfests_id',$filmfest_id)
                            ->where('tweet_productions_id',$production_id)->first();
                        $filmfestProduction -> videoStatus = 0;
                        $filmfestProduction -> save();
                        return false;
                    }
                    if($ret['code']==0){
                        $filmfestProduction = FilmfestsProductions::where('filmfests_id',$filmfest_id)
                            ->where('tweet_productions_id',$production_id)->first();
                        $filmfestProduction -> videoStatus = 2;
                        $filmfestProduction -> save();
                    }
                }while($ret['code']==0);
                break;
            case 3:
//                $head = ltrim(parse_url($this->downloadUrl($join_video->head_video))['path'], '/');
//                $head = str_replace('v.cdn.hivideo.com','',$join_video->head_video);
                $head = 'filmfestival/bcsff/titles/bcsff_titles.mp4';
                $tempHead = 'tempvideo/'.$id.$head;
                $fops1 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempHead);
                list($id1, $err2) = $pfop->execute($head, $fops1);
                $message = $this->listenTranscoding($id1,$bucket);
                $head_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempHead;
                $tail_url = false;
                $encodedUrl1 = base64_urlSafeEncode($head_url);
                $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."|saveas/".$save;
                list($id2, $err) = $pfop->execute($file_url, $fops);
                set_time_limit(0);
                do{
                    list($ret, $err1) = $pfop->status($id2);
                    if($ret['code']==3 && $ret['des']=='The fop is failed'){
                        $filmfestProduction = FilmfestsProductions::where('filmfests_id',$filmfest_id)
                            ->where('tweet_productions_id',$production_id)->first();
                        $filmfestProduction -> videoStatus = 0;
                        $filmfestProduction -> save();
                        return false;
                    }
                    if($ret['code']==0){
                        $filmfestProduction = FilmfestsProductions::where('filmfests_id',$filmfest_id)
                            ->where('tweet_productions_id',$production_id)->first();
                        $filmfestProduction -> videoStatus = 2;
                        $filmfestProduction -> save();
                    }
                }while($ret['code']==0);

//                $this->transcoding($bucket,'&'.$id.'&&'.$file_url,$width,$height,$choice);
                break;
            default :
                $head = ltrim(parse_url($this->downloadUrl($join_video->head_video))['path'], '/');
                $tail = ltrim(parse_url($this->downloadUrl($join_video->tail_video))['path'], '/');
                $tempHead = 'tempvideo/' . $head;
                $tempTail = 'tempvideo/' . $tail;
                $fops1 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempHead);
                $fops2 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempTail);
                list($id1, $err2) = $pfop->execute($head, $fops1);
                list($id2, $err2) = $pfop->execute($tail, $fops2);
                $message = $this->listenTranscoding($id1,$bucket);
                $message = $this->listenTranscoding($id2,$bucket);
                $head_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempHead;
                $tail_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempTail;
                $encodedUrl1 = base64_urlSafeEncode($head_url);
                $encodedUrl2 = base64_urlSafeEncode($tail_url);
                $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."/".$encodedUrl2."|saveas/".$save;
                list($id, $err) = $pfop->execute($file_url, $fops);
                set_time_limit(0);
                do{
                    list($ret, $err1) = $pfop->status($id2);
                    if($ret['code']==3 && $ret['des']=='The fop is failed'){
                        $filmfestProduction = FilmfestsProductions::where('filmfests_id',$filmfest_id)
                            ->where('tweet_productions_id',$production_id)->first();
                        $filmfestProduction -> videoStatus = 0;
                        $filmfestProduction -> save();
                        return false;
                    }
                    if($ret['code']==0){
                        $filmfestProduction = FilmfestsProductions::where('filmfests_id',$filmfest_id)
                            ->where('tweet_productions_id',$production_id)->first();
                        $filmfestProduction -> videoStatus = 2;
                        $filmfestProduction -> save();
                    }
                }while($ret['code']==0);
                break;
        }


        return true;


    }

    public function joint_tranding_ects($id,$join_id,$notice = null,$status=1,$width,$height)
    {
        $tweet = Tweet::find($id);
//        if ( !$tweet )    return response()->json(['message'=>'bad_request'],403);
//        $url = $this->downloadUrl($tweet->video);
        $file_url = '55.mp4';
        $join_video = JoinVideo::find($join_id);
        $bucket = 'hivideo-video';
        $pipeline = 'hivideo_alternative';
        $pfop = new PersistentFop($this->auth, $bucket,$pipeline,$notice);
        set_time_limit(0);
        $save = base64_urlSafeEncode($bucket .":&" . $id . '&&' . $file_url);

        switch ($status) {
            case 1:
                $head = ltrim(parse_url($this->downloadUrl($join_video->head_video))['path'], '/');
                $tail = ltrim(parse_url($this->downloadUrl($join_video->tail_video))['path'], '/');
                $tempHead = 'tempvideo/' . $head;
                $tempTail = 'tempvideo/' . $tail;
                $fops1 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempHead);
                $fops2 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempTail);
                list($id1, $err2) = $pfop->execute($head, $fops1);
                list($id2, $err2) = $pfop->execute($tail, $fops2);
                $message = $this->listenTranscoding($id1,$bucket);
                $message = $this->listenTranscoding($id2,$bucket);
                $head_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempHead;
                $tail_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempTail;
                $encodedUrl1 = base64_urlSafeEncode($head_url);
                $encodedUrl2 = base64_urlSafeEncode($tail_url);
                $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."/".$encodedUrl2."|saveas/".$save;
                list($id, $err) = $pfop->execute($file_url, $fops);
                break;
            case 2:

                $tail = ltrim(parse_url($this->downloadUrl($join_video->tail_video))['path'], '/');
                $tempTail = 'tempvideo/' . $tail;
                $fops2 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempTail);
                list($id, $err2) = $pfop->execute($tail, $fops2);
                $message = $this->listenTranscoding($id,$bucket);
                $head_url = false;
                $tail_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempTail;
                $encodedUrl2 = base64_urlSafeEncode($tail_url);
                $fops = "avconcat/2/format/mp4/index/1/".$encodedUrl2."|saveas/".$save;
                list($id, $err) = $pfop->execute($file_url, $fops);
                break;
            case 3:
                $head = ltrim(parse_url($this->downloadUrl($join_video->head_video))['path'], '/');
//                $head = 'filmfestival/bcsff/titles/bcsff_titles.mp4';
                $tempHead = 'tempvideo/' . $head;
                $fops1 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempHead);
                list($id, $err2) = $pfop->execute($head, $fops1);
                $message = $this->listenTranscoding($id,$bucket);
                $head_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempHead;
                $tail_url = false;
                $encodedUrl1 = base64_urlSafeEncode($head_url);
                $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."|saveas/".$save;
                list($id1, $err) = $pfop->execute($file_url, $fops);


                break;
            default :
                $head = ltrim(parse_url($this->downloadUrl($join_video->head_video))['path'], '/');
                $tail = ltrim(parse_url($this->downloadUrl($join_video->tail_video))['path'], '/');
                $tempHead = 'tempvideo/' . $head;
                $tempTail = 'tempvideo/' . $tail;
                $fops1 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempHead);
                $fops2 = 'avthumb/mp4/s/' . (int)($width) . 'x' . (int)($height) . '|saveas/' . base64_urlSafeEncode($bucket . ':' . $tempTail);
                list($id1, $err2) = $pfop->execute($head, $fops1);
                list($id2, $err2) = $pfop->execute($tail, $fops2);
                $message = $this->listenTranscoding($id1,$bucket);
                $message = $this->listenTranscoding($id2,$bucket);
                $head_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempHead;
                $tail_url = "http://oz77q7smo.bkt.clouddn.com/" . $tempTail;
                $encodedUrl1 = base64_urlSafeEncode($head_url);
                $encodedUrl2 = base64_urlSafeEncode($tail_url);
                $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."/".$encodedUrl2."|saveas/".$save;
                list($id, $err) = $pfop->execute($file_url, $fops);
                break;
        }


    }

    public function listenTranscoding($id,$bucket)
    {
        $pfop = new PersistentFop($this->auth,$bucket);
        list($ret, $err1) = $pfop->status($id);
        set_time_limit(0);
        if($ret['code']==0){
            $a = 1;
        }elseif ($ret['code']==3 && $ret['des']=='The fop is failed'){
            $a = 0;
        }elseif ($ret['code']==1 || $ret['code']==2){
            $this->listenTranscoding($id,$bucket);
        }
    }

    public function yellowCheck($id,$url,$notice = null)
    {
        $bucket = config('constants.video_bucket');
        $pipeline = config('constants.pipeline_transcoding');
        $pfop = new PersistentFop($this->auth, $bucket,$pipeline,$notice);
        $url = $this->downloadUrl($url);
        $file_url = ltrim(parse_url($url)['path'], '/');
        $save = base64_urlSafeEncode($bucket .":&" .$id.'&&');
        $fops = "tupu-video/nrop/f/5/s/30"."|saveas/".$save;
        list($id, $err) = $pfop->execute($file_url, $fops);
    }

    public function join_ects_test($id,$join_id,$notice = null,$status=1)
    {
        $tweet = Tweet::find($id);
        if ( !$tweet )    return response()->json(['message'=>'bad_request'],403);
        $url = $this->downloadUrl($tweet->video);
        $file_url = ltrim(parse_url($url)['path'], '/');
        switch ($status){
            case 1:
                $join_video = JoinVideo::find($join_id);
                $head_url = "http://oz77q7smo.bkt.clouddn.com/".ltrim(parse_url($this -> downloadUrl($join_video->head_video))['path'], '/');
                $tail_url = "http://oz77q7smo.bkt.clouddn.com/".ltrim(parse_url($this -> downloadUrl($join_video->tail_video))['path'], '/');
                break;
            case 2:
                $join_video = JoinVideo::find($join_id);
                $head_url = false;
                $tail_url = "http://oz77q7smo.bkt.clouddn.com/".ltrim(parse_url($this -> downloadUrl($join_video->tail_video))['path'], '/');
                break;
            case 3:
                $join_video = JoinVideo::find($join_id);
                $head_url = "http://oz77q7smo.bkt.clouddn.com/".ltrim(parse_url($this -> downloadUrl($join_video->head_video))['path'], '/');
                $tail_url = false;
                break;
            default:
                $join_video = JoinVideo::find($join_id);
                $head_url = "http://oz77q7smo.bkt.clouddn.com/".ltrim(parse_url($this -> downloadUrl($join_video->head_video))['path'], '/');
                $tail_url = "http://oz77q7smo.bkt.clouddn.com/".ltrim(parse_url($this -> downloadUrl($join_video->tail_video))['path'], '/');
                break;
        }
        $bucket = 'test-video';
        $pipeline = 'hivideo_drm';
        $pfop = new PersistentFop($this->auth, $bucket,$pipeline,$notice);
//       $head_url = "http://oz77q7smo.bkt.clouddn.com/".ltrim(parse_url($this -> downloadUrl($join_video->head_video))['path'], '/');
//       $tail_url = "http://oz77q7smo.bkt.clouddn.com/".ltrim(parse_url($this -> downloadUrl($join_video->tail_video))['path'], '/');
        $encodedUrl1 = base64_urlSafeEncode( $head_url );
        $encodedUrl2 = base64_urlSafeEncode( $tail_url );
        $save = base64_urlSafeEncode($bucket .":&" . $id . '&&' . $file_url);
        $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."/".$encodedUrl2."|saveas/".$save;
        if (!$tail_url){
            $fops = "avconcat/2/format/mp4/index/2/".$encodedUrl1."|saveas/".$save;
        }elseif(!$head_url){
            $fops = "avconcat/2/format/mp4/index/1/".$encodedUrl2."|saveas/".$save;
        }
        list($id, $err) = $pfop->execute($file_url, $fops);
    }
}
