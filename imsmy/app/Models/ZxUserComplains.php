<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZxUserComplains extends Model
{
    // 用户举报表
    protected  $table = 'zx_user_complains';

    protected $fillable = [
        'cause_id',
        'user_id',
        'status',
        'type',
        'type_id',
        'content',
        'staff_id',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 多对一关系 举报详情与举报原因
     */
    public function belongsToCause()
    {
        return $this -> belongsTo('App\Models\ZxUserComplainsCause','cause_id','id');
    }

    /**
     * 多对一关系 处理举报情况的工作人员
     */
    public function belongsToStaff()
    {
        return $this -> belongsTo('App\Models\Admin\Administrator','staff_id','id');
    }

}