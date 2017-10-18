<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserSex extends Model
{
    protected $table = 'zx_user_sex';

    protected $fillable = ['user_id','time_add'];

    public $timestamps = false;
}