<?php

namespace App\Api\Transformer\Discover;

use App\Api\Transformer\Transformer;
use CloudStorage;

class DiscoverFilmTransformer extends Transformer
{

    public function transform($data)
    {

        // 初始化数组
        $pictures = array();

        // 获取图片信息
        if($data->hasManyPicture->count()){

            foreach($data->hasManyPicture->take(3) as $value){

                $pictures[] =  CloudStorage::downloadUrl($value -> picture);
            }
        }

        return [
            'id'                => $data->id,
            'name'              => $data->name,
            'intro'             => $data->intro,
            'background_image'  => CloudStorage::downloadUrl($data->background_image),
            'pictures'          => $pictures,
        ];
    }
}