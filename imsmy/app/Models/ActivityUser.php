<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityUser extends Model
{
    //
    protected $table = 'activity_user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'activity_id','user_id','time_add','time_update'
    ];

    public $timestamps = false;
}
