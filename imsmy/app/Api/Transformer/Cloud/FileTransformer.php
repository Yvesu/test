<?php
namespace App\Api\Transformer\Cloud;

use App\Api\Transformer\Transformer;
use CloudStorage;

class FileTransformer extends Transformer
{

    public function transform($data)
    {
        $screenshot = '';

        // 判断文件类型,获取图片和视频的截图
        if(in_array($data -> type,[0,1])){

            $screenshot = CloudStorage::downloadUrl($data -> address . (1 === $data -> type ? '?imageView2/1/w/183/h/183' : '?vframe/jpg/offset/1/w/183/h/183'));
        }

        return [
            'id'            => $data->id,
            'name'          => $data->name,
            'address'       => CloudStorage::downloadUrl($data->address),
            'screenshot'    => $screenshot,
            'type'          => $data->type,
            'format'        => $data->format,
            'size'          => $data->size,
            'time_add'      => $data->time_add,
            'time_update'   => $data->time_update,
        ];
    }
}