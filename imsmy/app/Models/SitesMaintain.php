<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 网站信息维护 开关控制
 * Class SitesMaintain
 * @package App\Models
 */
class SitesMaintain extends Model
{
    protected  $table = 'zx_sites_maintain';

    protected $fillable = [
        'status',
    ];

    public $timestamps = false;


}