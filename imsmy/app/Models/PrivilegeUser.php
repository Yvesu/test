<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivilegeUser extends Model
{
    //
    protected $table = 'privilege_user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'checker_id',
        'checker_time',
        'checker_des',
        'type',
    ];


    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与用户表关系，反向一对多
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与管理员表关系 反向1对多
     */
    public function administoar()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','checker_id','id');
    }
}
