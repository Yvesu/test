<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Texture extends Model
{
    protected  $table = 'texture';

    protected $fillable = [
        'user_id',
        'name',
        'content',
        'download_count',
        'active',
        'folder_id',
        'recommend',
        'time_add',
        'time_update',
    ];

    public $timestamps=false;

    /**
     * 与文件夹一对一
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToFolder()
    {
        return $this->belongsTo('App\Models\TextureFolder','folder_id','id');
    }

    /**
     * 推荐
     * @param $query
     * @return mixed
     */
    public function scopeOfrecommend($query)
    {
        return $query->where('recommend','=','1');
    }

    /**
     * active
     * @param $query
     * @return mixed
     */
    public function scopeOfactive($query)
    {
        return $query->where('active','=','1');
    }

    /**
     * 与用户一对一
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function scopeOffolder($query,$id)
    {
        return $query -> WhereHas('belongsToFolder',function($q) use ($id){
            $q->where('folder_id',$id);
        });
    }

}
