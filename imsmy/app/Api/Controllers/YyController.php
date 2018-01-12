<?php

namespace App\Api\Controllers;

use App\Facades\CloudStorage;
use App\Models\Tweet;
use App\Models\TweetTrasf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class YyController extends Controller
{
    public function yy()
    {

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
        $pipeline = 'hivideo_alternative';
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


}
