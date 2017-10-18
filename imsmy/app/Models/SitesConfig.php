<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * 网站信息配置
 * Class SitesMaintain
 * @package App\Models
 */
class SitesConfig extends Model
{
    protected  $table = 'zx_sites_config';

    protected $fillable = [
        'active',
        'title',
        'icon',
        'hash_icon',
        'keywords',
        'admin_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 查询可用
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

}