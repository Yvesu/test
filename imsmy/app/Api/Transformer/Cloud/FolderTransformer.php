<?php
namespace App\Api\Transformer\Cloud;

use App\Api\Transformer\Transformer;
use CloudStorage;

class FolderTransformer extends Transformer
{

    public function transform($data)
    {
        $photo = [];

        // 获取图片或者视频截图
        if($data->hasManyFiles->count()){

            foreach($data->hasManyFiles as $key => $value){

                // 视频就获取 screenshot，取 183*183尺寸
                $photo[] = CloudStorage::downloadUrl(1 === $value -> type ? $value -> address : $value -> screenshot . '?imageView2/1/w/183/h/183');

                if(isset($photo[3])) break;
            }
        }

        return [
            'id'         => $data->id,
            'name'       => $data->name,
            'count'      => $data->count,
            'time_add'   => $data->time_add,
            'time_update'=> $data->time_update,
            'photo'      => $photo,
        ];
    }
}