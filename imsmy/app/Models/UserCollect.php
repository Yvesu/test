<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCollect extends Model
{
    protected $table = 'user_collects';

    protected $fillable = [
        'user_id',
        'type',
        'type_id',
        'status',
        'create_time',
    ];

    public $timestamps = false;

    /**
     * @param $query
     * @param $type
     * @return mixed
     */
    public function scopeOftype($query,$type)
    {
        return $query->where('type',$type);
    }

    /**
     * @param $query
     * @param $status
     * @return mixed
     */
    public function scopeOfstatus($query, $status)
    {
        return $query->where('status',(string)$status);
    }

    /**
     * @param $query
     * @param $user
     * @return mixed
     */
    public function scopeOfuser($query,$user)
    {
        return $query->where('user_id',$user);
    }
}
