<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserIntegral extends Model
{
    protected  $table = 'user_integral';

    protected $fillable = [
        'user_id',
        'integral_count',
        'type',
        'create_at',
        'update_at',
    ];

    public  $timestamps = false;

    /**
     * 多对一
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }


}
