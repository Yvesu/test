<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/13
 * Time: 20:21
 */

namespace App\Api\Transformer;


use CloudStorage;

class BlursTransformer extends Transformer
{
    /**
     * 样式，请登录七牛查看具体信息
     * @var string
     */
    private $format = '-thumbnail.preview';

    private function asset($id,$item)
    {
        return CloudStorage::downloadUrl('blur/' . $id . $item);
        //return 'blur/' . $id . $item;
    }

    public function urlTransformer($blur,$key,$format = null)
    {
        $value = $blur->$key;
        if(!isset($value)){
            return null;
        }
        $value .=  $format;
        return $this->asset($blur->id,$value);
    }

    public function transform($blur)
    {
        $GPUImage = $this->GPUImage($blur);

        $sequence_diagram = $this->sequenceDiagram($blur);


        return [
            'id'                =>  $blur->id,
            'classNameZh'       =>  $blur->belongsToBlurClass->name_zh,
            'classNameEn'       =>  $blur->belongsToBlurClass->name_en,
            'nameZh'            =>  $blur->name_zh,
            'nameEn'            =>  $blur->name_en,
            'GPUImage'          =>  $GPUImage,
            'sequenceDiagram'   =>  $sequence_diagram,
            'dynamicImage'      =>  $this->urlTransformer($blur,'dynamic_image'),
            'faceTracking'      =>  $this->urlTransformer($blur,'face_tracking'),
            'audio'             =>  $this->urlTransformer($blur,'audio'),
            //'preview'           =>  $blur->background == null ? null : 'blur/' . $blur->id . $blur->background,//$this->urlTransformer($blur,'background'),
            'preview'           =>  $this->urlTransformer($blur,'background'),
            'thumbnail_preview' =>  $this->urlTransformer($blur,'background',$this->format),
            'gravitySensing'    =>  $blur->gravity_sensing,
            'xAlignOffset'      =>  $blur->xAlign,
            'yAlignOffset'      =>  $blur->yAlign,
            'scalingRatio'      =>  $blur->scaling_ratio,
            'updated_at'        =>  strtotime($blur->updated_at),
            
        ];
    }

    public function GPUImage($blur)
    {
        $GPUImage = json_decode($blur->parameter,true);
        foreach($GPUImage as &$item){
            if(isset($item['imgUrl'])){
                $item['imgUrl'] = $this->asset($blur->id,$item['imgUrl']);
            }
        }
        unset($item);
        return $GPUImage;
    }

    public function sequenceDiagram($blur)
    {
        if(!isset($blur->sequence_diagram)){
            return null;
        }
        $sequence_diagram = json_decode($blur->sequence_diagram,true);
        foreach($sequence_diagram as &$item){
            if(isset($item)){
                $item = $this->asset($blur->id,$item);
            }
        }
        unset($item);
        return $sequence_diagram;
    }




}