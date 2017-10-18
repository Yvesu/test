<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserChannel extends Model
{
    protected $table = 'user_channel';

    protected $fillable = ['user_id','channel_id','time_add','time_update'];

    public $timestamps = false;
}