<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/11
 * Time: 11:05
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ChatGroupMember extends Model
{
    protected  $table = 'chat_group_member';

    protected $fillable = ['group_id', 'user_id', 'type'];
}