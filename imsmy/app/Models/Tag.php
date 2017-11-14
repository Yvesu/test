<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/13
 * Time: 16:00
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tag';

    protected $fillable = ['user_id', 'name'];

    /**
     * 用户好友分组与其成员 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyTagMembers()
    {
        return $this->hasMany('App\Models\TagMember', 'tag_id','id');
    }
}