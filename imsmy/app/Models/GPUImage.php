<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/31
 * Time: 20:22
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class GPUImage extends Model
{
    protected  $table = 'GPUImage';

    protected $fillable = ['name_zh', 'name_en', 'texture'];

    /**
     * GPUImage与GPUImageValue 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyGPUImageValue()
    {
        return $this->hasMany('App\Models\GPUImageValue', 'GPUImage_id', 'id');
    }
}