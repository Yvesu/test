<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PremiseController;

class IndexController extends PremiseController
{
    /**
     * 网站前端首页
     */
    public function index()
    {

        // 网站首页
        return view('welcome');
    }
}