<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/11
 * Time: 11:07
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ChatGroupBlacklist extends Model
{
    protected  $table = 'chat_group_blacklist';

    protected $fillable = ['group_id', 'user_id'];
}