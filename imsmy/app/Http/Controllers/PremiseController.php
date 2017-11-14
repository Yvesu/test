<?php

namespace App\Http\Controllers;

use  App\Models\SitesMaintain;
use  App\Models\SitesConfig;

/**
 * 前台使用 基类Controller 主要在于网站维护时使用，及网站配置信息
 * Class PremiseController
 * @package App\Http\Controllers\Admin
 */
class PremiseController extends Controller
{
    protected $_web;

    public function __construct()
    {

        // 通过id获取网站信息
        $this->_web = SitesConfig::active()->first();
        $status = SitesMaintain::first();

        if($status -> status !== 1) abort(503);

    }



}
