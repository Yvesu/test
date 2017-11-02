<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TweetCheckLog extends Common
{
    protected  $table = 'tweet_check_log';

    protected $fillable=['tweet_id', 'active', 'admin_id', 'time_add', 'time_update'];

    public $timestamps = false;

    /**
     * 动态操作的管理人员
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToCheckAdmin()
    {
        // 第二个参数是关联关系连接表的表名,第三个参数是本类的id，第四个参数是第一个参数那个类的id
        return $this->belongsTo('App\Models\Admin\Administrator','admin_id','id');
    }
}