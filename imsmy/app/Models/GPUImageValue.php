<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/14
 * Time: 19:25
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class GPUImageValue extends Model
{
    protected  $table = 'GPUImage_value';

    protected $fillable = ['name_zh', 'name_en', 'GPUImage_id', 'min', 'max', 'init'];

    /**
     * GPUImageValue与GPUImage 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToGPUImage()
    {
        return $this->belongsTo('App\Models\GPUImage', 'GPUImage_id', 'id');
    }
}