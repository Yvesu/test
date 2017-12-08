<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/22
 * Time: 20:12
 */

namespace App\Api\Transformer;

use App\Models\Blur;
use CloudStorage;
class BlurClassesTransformer extends Transformer
{
    protected $blursTransformer;

    public function __construct(BlursTransformer $blursTransformer)
    {
        $this->blursTransformer = $blursTransformer;
    }

    public function countBlurs($class_id)
    {
        //TODO权限判断 测试滤镜
        return  Blur::where('blur_class_id',$class_id)
                        ->where('active','!=',1)
                        ->get();
    }
    public function transform($blur_class,$type = 0)
    {
        $blurs = $this->countBlurs($blur_class->id);
        $transform =  [
            'id'            =>  $blur_class->id,
            'nameZh'        =>  $blur_class->name_zh,
            'nameEn'        =>  $blur_class->name_en,
            'smallIcon'     =>  CloudStorage::downloadUrl('blur_class/' . $blur_class->id . '/' . $blur_class->icon_sm),
            'largeIcon'     =>  CloudStorage::downloadUrl('blur_class/' . $blur_class->id . '/' . $blur_class->icon_lg),
            'count'         =>  $blurs->count(),
            'updated_at'    =>  strtotime($blur_class->updated_at),
        ];

        switch($type){
            case 1 :
                $this->installTransform($transform,$blurs);
                break;
            case 2 :
                $this->previewTransform($transform,$blurs);
                break;
            default :
                break;
        }
        return $transform;
    }

    public function previewTransform(&$transform,$blurs)
    {
        $data = $this->blursTransformer->transformCollection($blurs->all());
        $except = ['id','nameZh','nameEn','preview','thumbnail_preview','updated_at'];

        foreach($data as &$item){
            $item = array_intersect_key( $item, array_flip( $except ) );
        }
        unset($item);
        $transform['data'] = $data;
    }

    public function installTransform(&$transform,$blurs)
    {
        $transform['data'] = $this->blursTransformer->transformCollection($blurs->all());
    }
}