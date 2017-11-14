<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/11
 * Time: 11:04
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    protected  $table = 'chat_group';

    protected $fillable = ['id', 'name', 'desc', 'maxusers'];
}