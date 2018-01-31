<?php

namespace App\Http\Controllers\NewWeb;

use CloudStorage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Omnipay\Common\Exception\RuntimeExceptionTest;

class TestController extends Controller
{
    //
    public function test()
    {

//        Redis::del('sunwukong');
//        Redis::select(5);
//        Redis::set('name','cool');
//        $name1 = Redis::exists('naruto');
//        Redis::select(4);
//        Redis::Hdel('a','b');
//        Redis::DEL('naruto');
//        Redis::DEL('sunwukong');
//        Redis::DEL('nameisang');
//        Redis::DEL('hahahaha');
//        $name1 = Redis::get('nameisang');
//        return $name1;
//        $a = 'aaa.jpg';
//        CloudStorage::imgWaterMark('hivideo-img-ects',$a,'coolrxy');
        set_time_limit(0);
        sleep(100);
        dd(222);
    }
}
