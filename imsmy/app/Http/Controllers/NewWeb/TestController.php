<?php

namespace App\Http\Controllers\NewWeb;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    //
    public function test()
    {

        Redis::select(2);
        Redis::set('name','cool');
        $name = Redis::exists('name');
        Redis::del('admin');
        return $name;
    }
}
