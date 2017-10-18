<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/5
 * Time: 12:27
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Blur extends Model
{
    protected  $table = 'blur';

    protected $fillable = [
        'name_zh',
        'name_en',
        'blur_class_id',
        'parameter',
        'sequence_diagram',
        'dynamic_image',
        'background',
        'shutter_speed',
        'face_tracking',
        'gravity_sensing',
        'active',
        'xAlign',
        'yAlign',
        'scaling_ratio',
        'audio'
    ];

    protected $touches = ['belongsToBlurClass'];

    /**
     * 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToBlurClass()
    {
        return $this->belongsTo('App\Models\BlurClass', 'blur_class_id', 'id');
    }
}