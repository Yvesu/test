<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * 指定从 CSRF 验证中排除的URL
     *
     * @var array
     */
    protected $except = [
        // 支付宝
        'alipay/return',
        // 微信支付
        'weixin/return',
    ];
}
