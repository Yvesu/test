<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/13
 * Time: 16:01
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TagMember extends Model
{
    protected $table = 'tag_member';

    protected $fillable = ['tag_id', 'member_id'];
}